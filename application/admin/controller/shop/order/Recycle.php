<?php

namespace app\admin\controller\shop\order;

use app\common\controller\Backend;
use think\Db;

/**
 * 商城-订单回收
 *
 * @icon fa fa-circle-o
 */
class Recycle extends Backend
{

    /**
     * Recycle模型对象
     * @var \app\admin\model\shop\order\Recycle
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\shop\order\Recycle;
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    public function index()
    {
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $rows = $list->items();
        $userIds = [];
        foreach ($rows as $row) {
            if (!empty($row['user_id'])) {
                $userIds[] = (int)$row['user_id'];
            }
        }
        $users = $userIds ? Db::name('shop_user')->where('id', 'in', array_unique($userIds))->column('id,username,nickname', 'id') : [];
        $orderStatusList = [
            'unpaid'    => __('Status unpaid'),
            'paid'      => __('Status paid'),
            'shipped'   => __('Status shipped'),
            'completed' => __('Status completed'),
            'cancelled' => __('Status cancelled'),
            'recycled'  => __('Status recycled'),
        ];
        $payStatusList = [
            'unpaid' => __('Pay_status unpaid'),
            'paid'   => __('Pay_status paid'),
        ];
        foreach ($rows as &$row) {
            $user = isset($users[$row['user_id']]) ? $users[$row['user_id']] : [];
            $row['user'] = [
                'username' => isset($user['username']) ? $user['username'] : '',
                'nickname' => isset($user['nickname']) ? $user['nickname'] : '',
            ];
            $row['order_status_text'] = isset($orderStatusList[$row['order_status']]) ? $orderStatusList[$row['order_status']] : $row['order_status'];
            $row['pay_status_text'] = isset($payStatusList[$row['pay_status']]) ? $payStatusList[$row['pay_status']] : $row['pay_status'];
        }
        unset($row);
        $result = ['total' => $list->total(), 'rows' => $rows];
        return json($result);
    }

    public function approve($ids = null)
    {
        $this->audit($ids, 'approve');
    }

    public function reject($ids = null)
    {
        $this->audit($ids, 'reject');
    }

    protected function audit($ids, $action)
    {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $ids = $ids ?: $this->request->post('ids');
        $now = time();
        $successMessage = $action === 'reject' ? __('Order recycle audit rejected') : __('Order recycle audit approved');
        Db::startTrans();
        try {
            $recycle = Db::name('shop_order_recycle')->where('id', (int)$ids)->lock(true)->find();
            if (!$recycle) {
                throw new \Exception(__('No Results were found'));
            }
            if ($recycle['status'] !== 'pending') {
                throw new \Exception(__('Only pending recycle orders can be audited'));
            }

            if ($action === 'reject') {
                Db::name('shop_order_recycle')->where('id', (int)$recycle['id'])->update([
                    'status'         => 'rejected',
                    'audit_admin_id' => (int)$this->auth->id,
                    'audittime'      => $now,
                    'updatetime'     => $now,
                ]);
                Db::name('shop_order_recycle_log')->insert([
                    'recycle_id' => (int)$recycle['id'],
                    'order_id'   => (int)$recycle['order_id'],
                    'order_no'   => $recycle['order_no'],
                    'user_id'    => (int)$recycle['user_id'],
                    'admin_id'   => (int)$this->auth->id,
                    'action'     => 'reject',
                    'memo'       => __('Order recycle audit rejected'),
                    'createtime' => $now,
                ]);
                Db::commit();
            } else {
                $amount = round((float)$recycle['recycle_amount'], 2);
                if ($amount <= 0) {
                    throw new \Exception(__('Recycle amount must be greater than 0'));
                }
                $user = Db::name('shop_user')->where('id', (int)$recycle['user_id'])->lock(true)->find();
                if (!$user) {
                    throw new \Exception(__('User does not exist'));
                }
                $order = Db::name('shop_order')->where('id', (int)$recycle['order_id'])->lock(true)->find();
                if (!$order) {
                    throw new \Exception(__('Order does not exist'));
                }
                if ($order['status'] === 'recycled') {
                    throw new \Exception(__('Order has already been recycled'));
                }

                $before = (float)$user['money'];
                $after = $before + $amount;
                Db::name('shop_user')->where('id', (int)$user['id'])->update([
                    'money'      => number_format($after, 2, '.', ''),
                    'updatetime' => $now,
                ]);
                Db::name('shop_balance_log')->insert([
                    'user_id'     => (int)$user['id'],
                    'type'        => 'recycle',
                    'order_id'    => (int)$recycle['order_id'],
                    'recharge_id' => 0,
                    'withdraw_id' => 0,
                    'refund_id'   => 0,
                    'money'       => number_format($amount, 2, '.', ''),
                    'before'      => number_format($before, 2, '.', ''),
                    'after'       => number_format($after, 2, '.', ''),
                    'memo'        => __('Order recycle income') . ': ' . $recycle['order_no'],
                    'createtime'  => $now,
                ]);
                Db::name('shop_order')->where('id', (int)$order['id'])->update([
                    'status'     => 'recycled',
                    'updatetime' => $now,
                ]);
                Db::name('shop_order_recycle')->where('id', (int)$recycle['id'])->update([
                    'status'         => 'approved',
                    'audit_admin_id' => (int)$this->auth->id,
                    'audittime'      => $now,
                    'updatetime'     => $now,
                ]);
                Db::name('shop_order_recycle_log')->insert([
                    'recycle_id' => (int)$recycle['id'],
                    'order_id'   => (int)$recycle['order_id'],
                    'order_no'   => $recycle['order_no'],
                    'user_id'    => (int)$recycle['user_id'],
                    'admin_id'   => (int)$this->auth->id,
                    'action'     => 'approve',
                    'memo'       => __('Order recycle audit approved'),
                    'createtime' => $now,
                ]);
                Db::commit();
            }
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success($successMessage);
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}

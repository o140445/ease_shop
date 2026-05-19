<?php

namespace app\admin\controller\shop;

use app\common\controller\Backend;
use app\index\service\FinanceLogService;
use think\Db;

/**
 * 商城-售后退款
 *
 * @icon fa fa-circle-o
 */
class Refund extends Backend
{

    /**
     * Refund模型对象
     * @var \app\admin\model\shop\Refund
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\shop\Refund;
        $this->view->assign("typeList", $this->model->getTypeList());
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
        [$where, $sort, $order, $offset, $limit] = $this->buildparams(null, false);
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
        foreach ($rows as &$row) {
            $user = isset($users[$row['user_id']]) ? $users[$row['user_id']] : [];
            $row['user'] = [
                'username' => isset($user['username']) ? $user['username'] : '',
                'nickname' => isset($user['nickname']) ? $user['nickname'] : '',
            ];
        }
        unset($row);
        return json(['total' => $list->total(), 'rows' => $rows]);
    }

    public function approve($ids = null)
    {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $ids = $ids ?: $this->request->post('ids');
        $financeLog = new FinanceLogService();
        $now = time();
        Db::startTrans();
        try {
            $refund = Db::name('shop_refund')->where('id', (int)$ids)->lock(true)->find();
            if (!$refund) {
                throw new \Exception(__('No Results were found'));
            }
            if ($refund['status'] !== 'pending') {
                throw new \Exception(__('Only pending refund requests can be audited'));
            }
            $order = Db::name('shop_order')->where('id', (int)$refund['order_id'])->lock(true)->find();
            if (!$order) {
                throw new \Exception(__('Order does not exist'));
            }
            $user = Db::name('shop_user')->where('id', (int)$refund['user_id'])->lock(true)->find();
            if (!$user) {
                throw new \Exception(__('User does not exist'));
            }

            $refundMoney = round((float)$refund['apply_money'], 2);
            if ($refundMoney <= 0) {
                throw new \Exception(__('Refund amount must be greater than 0'));
            }
            if ($refundMoney > (float)$order['pay_amount']) {
                throw new \Exception(__('Refund amount cannot exceed paid amount'));
            }

            $before = (float)$user['money'];
            $after = round($before + $refundMoney, 2);
            Db::name('shop_user')->where('id', (int)$user['id'])->update([
                'money'      => number_format($after, 2, '.', ''),
                'updatetime' => $now,
            ]);
            Db::name('shop_refund')->where('id', (int)$refund['id'])->update([
                'status'          => 'refunded',
                'refund_money'    => number_format($refundMoney, 2, '.', ''),
                'audit_admin_id'  => (int)$this->auth->id,
                'audit_remark'    => __('Refund request approved'),
                'refund_admin_id' => (int)$this->auth->id,
                'refund_remark'   => __('Refunded to balance'),
                'audittime'       => $now,
                'refundtime'      => $now,
                'updatetime'      => $now,
            ]);
            Db::name('shop_order')->where('id', (int)$order['id'])->update([
                'status'     => 'returned',
                'pay_status' => 'refunded',
                'updatetime' => $now,
            ]);
            Db::name('shop_order_log')->insert([
                'order_id'    => (int)$order['id'],
                'order_no'    => $order['order_no'],
                'user_id'     => (int)$refund['user_id'],
                'admin_id'    => (int)$this->auth->id,
                'action'      => 'refund',
                'from_status' => $order['status'],
                'to_status'   => 'returned',
                'memo'        => __('Refunded to balance') . ': ' . number_format($refundMoney, 2, '.', ''),
                'createtime'  => $now,
            ]);
            $financeLog->balanceChange([
                'user_id'   => (int)$refund['user_id'],
                'type'      => 'refund',
                'order_id'  => (int)$order['id'],
                'refund_id' => (int)$refund['id'],
                'money'     => number_format($refundMoney, 2, '.', ''),
                'before'    => number_format($before, 2, '.', ''),
                'after'     => number_format($after, 2, '.', ''),
                'memo'      => __('Order refund') . ': ' . $refund['refund_no'],
            ]);
            Db::commit();
            $financeLog->operation('refund_approve_pay', 'success', [
                'admin_id'     => (int)$this->auth->id,
                'user_id'      => (int)$refund['user_id'],
                'order_id'     => (int)$order['id'],
                'refund_id'    => (int)$refund['id'],
                'refund_money' => number_format($refundMoney, 2, '.', ''),
                'before'       => number_format($before, 2, '.', ''),
                'after'        => number_format($after, 2, '.', ''),
            ]);
        } catch (\Exception $e) {
            Db::rollback();
            $financeLog->transactionFail('refund_approve_pay', $e, [
                'admin_id'  => (int)$this->auth->id,
                'refund_id' => (int)$ids,
            ]);
            $this->error($e->getMessage());
        }

        $this->success(__('Refund completed'));
    }

    public function reject($ids = null)
    {
        $this->audit($ids, 'reject');
    }

    public function refund($ids = null)
    {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $ids = $ids ?: $this->request->post('ids');
        $financeLog = new FinanceLogService();
        $now = time();
        Db::startTrans();
        try {
            $refund = Db::name('shop_refund')->where('id', (int)$ids)->lock(true)->find();
            if (!$refund) {
                throw new \Exception(__('No Results were found'));
            }
            if (!in_array($refund['status'], ['approved', 'returned'], true)) {
                throw new \Exception(__('Only approved refund requests can be refunded'));
            }

            $order = Db::name('shop_order')->where('id', (int)$refund['order_id'])->lock(true)->find();
            if (!$order) {
                throw new \Exception(__('Order does not exist'));
            }
            $user = Db::name('shop_user')->where('id', (int)$refund['user_id'])->lock(true)->find();
            if (!$user) {
                throw new \Exception(__('User does not exist'));
            }

            $refundMoney = round((float)$refund['refund_money'], 2);
            if ($refundMoney <= 0) {
                $refundMoney = round((float)$refund['apply_money'], 2);
            }
            if ($refundMoney <= 0) {
                throw new \Exception(__('Refund amount must be greater than 0'));
            }
            if ($refundMoney > (float)$order['pay_amount']) {
                throw new \Exception(__('Refund amount cannot exceed paid amount'));
            }

            $before = (float)$user['money'];
            $after = round($before + $refundMoney, 2);
            Db::name('shop_user')->where('id', (int)$user['id'])->update([
                'money'      => number_format($after, 2, '.', ''),
                'updatetime' => $now,
            ]);
            Db::name('shop_refund')->where('id', (int)$refund['id'])->update([
                'status'          => 'refunded',
                'refund_money'    => number_format($refundMoney, 2, '.', ''),
                'refund_admin_id' => (int)$this->auth->id,
                'refund_remark'   => __('Refunded to balance'),
                'refundtime'      => $now,
                'updatetime'      => $now,
            ]);
            Db::name('shop_order')->where('id', (int)$order['id'])->update([
                'status'     => 'returned',
                'pay_status' => 'refunded',
                'updatetime' => $now,
            ]);
            Db::name('shop_order_log')->insert([
                'order_id'    => (int)$order['id'],
                'order_no'    => $order['order_no'],
                'user_id'     => (int)$refund['user_id'],
                'admin_id'    => (int)$this->auth->id,
                'action'      => 'refund',
                'from_status' => $order['status'],
                'to_status'   => 'returned',
                'memo'        => __('Refunded to balance') . ': ' . number_format($refundMoney, 2, '.', ''),
                'createtime'  => $now,
            ]);
            $financeLog->balanceChange([
                'user_id'   => (int)$refund['user_id'],
                'type'      => 'refund',
                'order_id'  => (int)$order['id'],
                'refund_id' => (int)$refund['id'],
                'money'     => number_format($refundMoney, 2, '.', ''),
                'before'    => number_format($before, 2, '.', ''),
                'after'     => number_format($after, 2, '.', ''),
                'memo'      => __('Order refund') . ': ' . $refund['refund_no'],
            ]);
            Db::commit();
            $financeLog->operation('refund_pay', 'success', [
                'admin_id'     => (int)$this->auth->id,
                'user_id'      => (int)$refund['user_id'],
                'order_id'     => (int)$order['id'],
                'refund_id'    => (int)$refund['id'],
                'refund_money' => number_format($refundMoney, 2, '.', ''),
                'before'       => number_format($before, 2, '.', ''),
                'after'        => number_format($after, 2, '.', ''),
            ]);
        } catch (\Exception $e) {
            Db::rollback();
            $financeLog->transactionFail('refund_pay', $e, [
                'admin_id'  => (int)$this->auth->id,
                'refund_id' => (int)$ids,
            ]);
            $this->error($e->getMessage());
        }

        $this->success(__('Refund completed'));
    }

    protected function audit($ids, $action)
    {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $ids = $ids ?: $this->request->post('ids');
        $now = time();
        Db::startTrans();
        try {
            $refund = Db::name('shop_refund')->where('id', (int)$ids)->lock(true)->find();
            if (!$refund) {
                throw new \Exception(__('No Results were found'));
            }
            if ($refund['status'] !== 'pending') {
                throw new \Exception(__('Only pending refund requests can be audited'));
            }
            $order = Db::name('shop_order')->where('id', (int)$refund['order_id'])->lock(true)->find();
            if (!$order) {
                throw new \Exception(__('Order does not exist'));
            }

            if ($action === 'reject') {
                Db::name('shop_refund')->where('id', (int)$refund['id'])->update([
                    'status'         => 'rejected',
                    'audit_admin_id' => (int)$this->auth->id,
                    'audit_remark'   => __('Refund request rejected'),
                    'audittime'      => $now,
                    'updatetime'     => $now,
                ]);
                Db::name('shop_order')->where('id', (int)$order['id'])->update([
                    'status'     => 'completed',
                    'updatetime' => $now,
                ]);
                Db::name('shop_order_log')->insert([
                    'order_id'    => (int)$order['id'],
                    'order_no'    => $order['order_no'],
                    'user_id'     => (int)$refund['user_id'],
                    'admin_id'    => (int)$this->auth->id,
                    'action'      => 'refund_reject',
                    'from_status' => $order['status'],
                    'to_status'   => 'completed',
                    'memo'        => __('Refund request rejected'),
                    'createtime'  => $now,
                ]);
                $message = __('Refund request rejected');
            } else {
                $refundMoney = round((float)$refund['apply_money'], 2);
                Db::name('shop_refund')->where('id', (int)$refund['id'])->update([
                    'status'         => 'approved',
                    'refund_money'   => number_format($refundMoney, 2, '.', ''),
                    'audit_admin_id' => (int)$this->auth->id,
                    'audit_remark'   => __('Refund request approved'),
                    'audittime'      => $now,
                    'updatetime'     => $now,
                ]);
                Db::name('shop_order_log')->insert([
                    'order_id'    => (int)$order['id'],
                    'order_no'    => $order['order_no'],
                    'user_id'     => (int)$refund['user_id'],
                    'admin_id'    => (int)$this->auth->id,
                    'action'      => 'refund_approve',
                    'from_status' => $order['status'],
                    'to_status'   => $order['status'],
                    'memo'        => __('Refund request approved'),
                    'createtime'  => $now,
                ]);
                $message = __('Refund request approved');
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        $this->success($message);
    }


    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}

<?php

namespace app\admin\controller\shop;

use app\common\controller\Backend;
use app\index\service\FinanceLogService;
use think\Db;

/**
 * 商城-提款申请
 *
 * @icon fa fa-circle-o
 */
class Withdraw extends Backend
{

    /**
     * Withdraw模型对象
     * @var \app\admin\model\shop\Withdraw
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\shop\Withdraw;
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
            $withdraw = Db::name('shop_withdraw')->where('id', (int)$ids)->lock(true)->find();
            if (!$withdraw) {
                throw new \Exception(__('No Results were found'));
            }
            if ($withdraw['status'] !== 'pending') {
                throw new \Exception(__('Only pending withdraw requests can be approved'));
            }
            $user = Db::name('shop_user')->where('id', (int)$withdraw['user_id'])->lock(true)->find();
            if (!$user) {
                throw new \Exception(__('User does not exist'));
            }
            $money = round((float)$withdraw['money'], 2);
            if ($money <= 0) {
                throw new \Exception(__('Withdraw amount must be greater than 0'));
            }
            if ((float)$user['money'] < $money) {
                throw new \Exception(__('Insufficient account balance'));
            }
            $before = (float)$user['money'];
            $after = round($before - $money, 2);
            Db::name('shop_user')->where('id', (int)$user['id'])->update([
                'money'      => number_format($after, 2, '.', ''),
                'updatetime' => $now,
            ]);
            Db::name('shop_withdraw')->where('id', (int)$withdraw['id'])->update([
                'status'         => 'approved',
                'audit_admin_id' => (int)$this->auth->id,
                'audit_remark'   => __('Withdraw approved'),
                'audittime'      => $now,
                'updatetime'     => $now,
            ]);
            $financeLog->balanceChange([
                'user_id'     => (int)$withdraw['user_id'],
                'type'        => 'withdraw',
                'withdraw_id' => (int)$withdraw['id'],
                'money'       => '-' . number_format($money, 2, '.', ''),
                'before'      => number_format($before, 2, '.', ''),
                'after'       => number_format($after, 2, '.', ''),
                'memo'        => __('Withdraw approved') . ': ' . $withdraw['withdraw_no'],
            ]);
            Db::commit();
            $financeLog->operation('withdraw_approve', 'success', [
                'admin_id'    => (int)$this->auth->id,
                'user_id'     => (int)$withdraw['user_id'],
                'withdraw_id' => (int)$withdraw['id'],
                'money'       => '-' . number_format($money, 2, '.', ''),
                'before'      => number_format($before, 2, '.', ''),
                'after'       => number_format($after, 2, '.', ''),
            ]);
        } catch (\Exception $e) {
            Db::rollback();
            $financeLog->transactionFail('withdraw_approve', $e, [
                'admin_id'    => (int)$this->auth->id,
                'withdraw_id' => (int)$ids,
            ]);
            $this->error($e->getMessage());
        }

        $this->success(__('Withdraw approved'));
    }

    public function reject($ids = null)
    {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $ids = $ids ?: $this->request->post('ids');
        $financeLog = new FinanceLogService();
        $now = time();
        Db::startTrans();
        try {
            $withdraw = Db::name('shop_withdraw')->where('id', (int)$ids)->lock(true)->find();
            if (!$withdraw) {
                throw new \Exception(__('No Results were found'));
            }
            if ($withdraw['status'] !== 'pending') {
                throw new \Exception(__('Only pending withdraw requests can be rejected'));
            }
            Db::name('shop_withdraw')->where('id', (int)$withdraw['id'])->update([
                'status'         => 'rejected',
                'audit_admin_id' => (int)$this->auth->id,
                'audit_remark'   => __('Withdraw rejected'),
                'audittime'      => $now,
                'updatetime'     => $now,
            ]);
            Db::commit();
            $financeLog->operation('withdraw_reject', 'success', [
                'admin_id'    => (int)$this->auth->id,
                'user_id'     => (int)$withdraw['user_id'],
                'withdraw_id' => (int)$withdraw['id'],
                'money'       => number_format((float)$withdraw['money'], 2, '.', ''),
            ]);
        } catch (\Exception $e) {
            Db::rollback();
            $financeLog->transactionFail('withdraw_reject', $e, [
                'admin_id'    => (int)$this->auth->id,
                'withdraw_id' => (int)$ids,
            ]);
            $this->error($e->getMessage());
        }

        $this->success(__('Withdraw rejected'));
    }


    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}

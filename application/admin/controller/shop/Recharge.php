<?php

namespace app\admin\controller\shop;

use app\common\controller\Backend;
use app\index\service\FinanceLogService;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 商城-余额充值
 *
 * @icon fa fa-circle-o
 */
class Recharge extends Backend
{

    /**
     * Recharge模型对象
     * @var \app\admin\model\shop\Recharge
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\shop\Recharge;
        $this->view->assign("payTypeList", $this->model->getPayTypeList());
        $this->view->assign("payStatusList", $this->model->getPayStatusList());
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

    public function add()
    {
        if (false === $this->request->isPost()) {
            $this->view->assign('userId', (int)$this->request->get('user_id', 0));
            return $this->view->fetch();
        }

        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $userId = (int)($params['user_id'] ?? 0);
        $money = round((float)($params['money'] ?? 0), 2);
        if ($userId <= 0) {
            $this->error(__('Please select user'));
        }
        if ($money <= 0) {
            $this->error(__('Please enter recharge amount'));
        }

        $financeLog = new FinanceLogService();
        Db::startTrans();
        try {
            $user = Db::name('shop_user')->where('id', $userId)->lock(true)->find();
            if (!$user) {
                throw new \Exception(__('User does not exist'));
            }

            $now = time();
            $before = (float)$user['money'];
            $after = round($before + $money, 2);
            $rechargeNo = 'RC' . date('YmdHis') . mt_rand(1000, 9999);
            $rechargeId = Db::name('shop_recharge')->insertGetId([
                'recharge_no'  => $rechargeNo,
                'user_id'      => $userId,
                'money'        => number_format($money, 2, '.', ''),
                'give_money'   => '0.00',
                'pay_money'    => number_format($money, 2, '.', ''),
                'pay_type'     => 'admin',
                'pay_status'   => 'paid',
                'voucher'      => '',
                'remark'       => '',
                'admin_id'     => $this->auth->id,
                'admin_remark' => __('Admin recharge'),
                'paidtime'     => $now,
                'createtime'   => $now,
                'updatetime'   => $now,
            ]);
            Db::name('shop_user')->where('id', $userId)->update([
                'money'                 => number_format($after, 2, '.', ''),
                'total_recharge_amount' => Db::raw('total_recharge_amount+' . $money),
                'updatetime'            => $now,
            ]);
            $financeLog->balanceChange([
                'user_id'     => $userId,
                'type'        => 'recharge',
                'recharge_id' => $rechargeId,
                'money'       => number_format($money, 2, '.', ''),
                'before'      => number_format($before, 2, '.', ''),
                'after'       => number_format($after, 2, '.', ''),
                'memo'        => __('Admin recharge') . ': ' . $rechargeNo,
            ]);
            Db::commit();
            $financeLog->operation('admin_recharge', 'success', [
                'admin_id'    => $this->auth->id,
                'user_id'     => $userId,
                'recharge_id' => $rechargeId,
                'recharge_no' => $rechargeNo,
                'money'       => number_format($money, 2, '.', ''),
                'before'      => number_format($before, 2, '.', ''),
                'after'       => number_format($after, 2, '.', ''),
            ]);
        } catch (ValidateException|PDOException|\Exception $e) {
            Db::rollback();
            $financeLog->transactionFail('admin_recharge', $e, [
                'admin_id' => $this->auth->id,
                'user_id'  => $userId,
                'money'    => number_format($money, 2, '.', ''),
            ]);
            $this->error($e->getMessage());
        }

        $this->success();
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
            $recharge = Db::name('shop_recharge')->where('id', (int)$ids)->lock(true)->find();
            if (!$recharge) {
                throw new \Exception(__('No Results were found'));
            }
            if ($recharge['pay_status'] !== 'unpaid') {
                throw new \Exception(__('Only unpaid recharge requests can be approved'));
            }
            $user = Db::name('shop_user')->where('id', (int)$recharge['user_id'])->lock(true)->find();
            if (!$user) {
                throw new \Exception(__('User does not exist'));
            }
            $money = round((float)$recharge['money'] + (float)$recharge['give_money'], 2);
            if ($money <= 0) {
                throw new \Exception(__('Recharge amount must be greater than 0'));
            }
            $before = (float)$user['money'];
            $after = round($before + $money, 2);
            Db::name('shop_user')->where('id', (int)$user['id'])->update([
                'money'                 => number_format($after, 2, '.', ''),
                'total_recharge_amount' => Db::raw('total_recharge_amount+' . $money),
                'updatetime'            => $now,
            ]);
            Db::name('shop_recharge')->where('id', (int)$recharge['id'])->update([
                'pay_status'   => 'paid',
                'admin_id'     => (int)$this->auth->id,
                'admin_remark' => __('Recharge approved'),
                'paidtime'     => $now,
                'updatetime'   => $now,
            ]);
            $financeLog->balanceChange([
                'user_id'     => (int)$recharge['user_id'],
                'type'        => 'recharge',
                'recharge_id' => (int)$recharge['id'],
                'money'       => number_format($money, 2, '.', ''),
                'before'      => number_format($before, 2, '.', ''),
                'after'       => number_format($after, 2, '.', ''),
                'memo'        => __('Recharge approved') . ': ' . $recharge['recharge_no'],
            ]);
            Db::commit();
            $financeLog->operation('recharge_approve', 'success', [
                'admin_id'    => (int)$this->auth->id,
                'user_id'     => (int)$recharge['user_id'],
                'recharge_id' => (int)$recharge['id'],
                'money'       => number_format($money, 2, '.', ''),
                'before'      => number_format($before, 2, '.', ''),
                'after'       => number_format($after, 2, '.', ''),
            ]);
        } catch (\Exception $e) {
            Db::rollback();
            $financeLog->transactionFail('recharge_approve', $e, [
                'admin_id'    => (int)$this->auth->id,
                'recharge_id' => (int)$ids,
            ]);
            $this->error($e->getMessage());
        }

        $this->success(__('Recharge approved'));
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
            $recharge = Db::name('shop_recharge')->where('id', (int)$ids)->lock(true)->find();
            if (!$recharge) {
                throw new \Exception(__('No Results were found'));
            }
            if ($recharge['pay_status'] !== 'unpaid') {
                throw new \Exception(__('Only unpaid recharge requests can be rejected'));
            }
            Db::name('shop_recharge')->where('id', (int)$recharge['id'])->update([
                'pay_status'   => 'cancelled',
                'admin_id'     => (int)$this->auth->id,
                'admin_remark' => __('Recharge rejected'),
                'updatetime'   => $now,
            ]);
            Db::commit();
            $financeLog->operation('recharge_reject', 'success', [
                'admin_id'    => (int)$this->auth->id,
                'user_id'     => (int)$recharge['user_id'],
                'recharge_id' => (int)$recharge['id'],
                'money'       => number_format((float)$recharge['money'], 2, '.', ''),
            ]);
        } catch (\Exception $e) {
            Db::rollback();
            $financeLog->transactionFail('recharge_reject', $e, [
                'admin_id'    => (int)$this->auth->id,
                'recharge_id' => (int)$ids,
            ]);
            $this->error($e->getMessage());
        }

        $this->success(__('Recharge rejected'));
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}

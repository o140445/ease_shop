<?php

namespace app\admin\controller\shop;

use app\common\controller\Backend;
use app\index\service\FinanceLogService;
use fast\Random;
use think\Cookie;
use think\Db;

/**
 * 商城-会员
 *
 * @icon fa fa-circle-o
 */
class User extends Backend
{
    protected $relationSearch = true;

    /**
     * User模型对象
     * @var \app\admin\model\shop\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\shop\User;
        $this->view->assign("statusList", $this->model->getStatusList());
        $levelList = Db::name('shop_user_level')
            ->whereNull('deletetime')
            ->order('id', 'desc')
            ->column('name', 'id');
        $this->assignconfig('levelList', $levelList);
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
            ->with('level')
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $password = trim((string)($params['password'] ?? ''));
        if ($password === '') {
            $this->error(__('Password can not be empty'));
        }
        if (Db::name('shop_user')->where('username', trim((string)$params['username']))->whereNull('deletetime')->find()) {
            $this->error(__('Username already exists'));
        }

        $salt = Random::alnum();
        $params['password'] = md5(md5($password) . $salt);
        $params['salt'] = $salt;
        $params['pay_password'] = '';
        $params['pay_salt'] = '';

        Db::startTrans();
        try {
            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }

    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $password = trim((string)($params['password'] ?? ''));
        $payPassword = trim((string)($params['pay_password'] ?? ''));
        unset($params['money']);
        if ($password !== '') {
            $salt = Random::alnum();
            $params['password'] = md5(md5($password) . $salt);
            $params['salt'] = $salt;
        } else {
            unset($params['password'], $params['salt']);
        }
        if ($payPassword !== '') {
            $paySalt = Random::alnum();
            $params['pay_password'] = md5(md5($payPassword) . $paySalt);
            $params['pay_salt'] = $paySalt;
        } else {
            unset($params['pay_password'], $params['pay_salt']);
        }
        if (isset($params['username']) && Db::name('shop_user')->where('username', trim((string)$params['username']))->where('id', '<>', $row['id'])->whereNull('deletetime')->find()) {
            $this->error(__('Username already exists'));
        }

        Db::startTrans();
        try {
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    public function deduct($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }

        $money = round((float)$this->request->post('row.money', 0), 2);
        $remark = trim((string)$this->request->post('row.remark', ''));
        if ($money <= 0) {
            $this->error(__('Deduct amount must be greater than 0'));
        }

        $financeLog = new FinanceLogService();
        Db::startTrans();
        try {
            $user = Db::name('shop_user')->where('id', (int)$row['id'])->lock(true)->find();
            if (!$user) {
                throw new \Exception(__('User does not exist'));
            }
            if ((float)$user['money'] < $money) {
                throw new \Exception(__('Insufficient account balance'));
            }
            $before = (float)$user['money'];
            $after = round($before - $money, 2);
            Db::name('shop_user')->where('id', (int)$user['id'])->update([
                'money'      => number_format($after, 2, '.', ''),
                'updatetime' => time(),
            ]);
            $financeLog->balanceChange([
                'user_id' => (int)$user['id'],
                'type'    => 'adjust',
                'money'   => '-' . number_format($money, 2, '.', ''),
                'before'  => number_format($before, 2, '.', ''),
                'after'   => number_format($after, 2, '.', ''),
                'memo'    => __('Admin deduct balance') . ($remark !== '' ? ': ' . $remark : ''),
            ]);
            Db::commit();
            $financeLog->operation('admin_deduct_balance', 'success', [
                'admin_id' => (int)$this->auth->id,
                'user_id'  => (int)$user['id'],
                'money'    => '-' . number_format($money, 2, '.', ''),
                'before'   => number_format($before, 2, '.', ''),
                'after'    => number_format($after, 2, '.', ''),
            ]);
        } catch (\Exception $e) {
            Db::rollback();
            $financeLog->transactionFail('admin_deduct_balance', $e, [
                'admin_id' => (int)$this->auth->id,
                'user_id'  => (int)$row['id'],
                'money'    => '-' . number_format($money, 2, '.', ''),
            ]);
            $this->error($e->getMessage());
        }

        $this->success();
    }

    public function freezemoney($ids = null)
    {
        return $this->moveFrozenMoney($ids, 'freeze');
    }

    public function unfreezemoney($ids = null)
    {
        return $this->moveFrozenMoney($ids, 'unfreeze');
    }

    public function freeze($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $row->save(['status' => 'locked']);
        $this->success(__('Freeze successful'));
    }

    public function unfreeze($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $row->save(['status' => 'normal']);
        $this->success(__('Unfreeze successful'));
    }

    public function loginuser($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($row['status'] !== 'normal') {
            $this->error(__('Only normal users can login'));
        }
        $token = Random::uuid();
        Db::name('shop_user')->where('id', (int)$row['id'])->update([
            'prevtime'   => $row['logintime'],
            'logintime'  => time(),
            'loginip'    => $this->request->ip(),
            'token'      => $token,
            'updatetime' => time(),
        ]);
        Cookie::set('shop_uid', (int)$row['id']);
        Cookie::set('shop_token', $token);
        $this->redirect('/index/center/index/lang/ar.html');
    }

    protected function moveFrozenMoney($ids, $action)
    {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        $financeLog = new FinanceLogService();
        Db::startTrans();
        try {
            $user = Db::name('shop_user')->where('id', (int)$row['id'])->lock(true)->find();
            if (!$user) {
                throw new \Exception(__('User does not exist'));
            }
            $available = (float)$user['money'];
            $frozen = (float)($user['frozen_money'] ?? 0);
            if ($action === 'freeze') {
                $money = round($available, 2);
                if ($money <= 0) {
                    throw new \Exception(__('Insufficient account balance'));
                }
                $afterAvailable = 0.00;
                $afterFrozen = round($frozen + $money, 2);
                $memo = __('Admin freeze balance');
                $logMoney = '-' . number_format($money, 2, '.', '');
                $logType = 'freeze';
            } else {
                $money = round($frozen, 2);
                if ($money <= 0) {
                    throw new \Exception(__('Insufficient frozen balance'));
                }
                $afterAvailable = round($available + $money, 2);
                $afterFrozen = 0.00;
                $memo = __('Admin unfreeze balance');
                $logMoney = number_format($money, 2, '.', '');
                $logType = 'unfreeze';
            }
            Db::name('shop_user')->where('id', (int)$user['id'])->update([
                'money'        => number_format($afterAvailable, 2, '.', ''),
                'frozen_money' => number_format($afterFrozen, 2, '.', ''),
                'updatetime'   => time(),
            ]);
            $financeLog->balanceChange([
                'user_id' => (int)$user['id'],
                'type'    => $logType,
                'money'   => $logMoney,
                'before'  => number_format($available, 2, '.', ''),
                'after'   => number_format($afterAvailable, 2, '.', ''),
                'memo'    => $memo,
            ]);
            Db::commit();
            $financeLog->operation('admin_' . $action . '_balance', 'success', [
                'admin_id'      => (int)$this->auth->id,
                'user_id'       => (int)$user['id'],
                'money'         => number_format($money, 2, '.', ''),
                'before'        => number_format($available, 2, '.', ''),
                'after'         => number_format($afterAvailable, 2, '.', ''),
                'frozen_before' => number_format($frozen, 2, '.', ''),
                'frozen_after'  => number_format($afterFrozen, 2, '.', ''),
            ]);
        } catch (\Exception $e) {
            Db::rollback();
            $financeLog->transactionFail('admin_' . $action . '_balance', $e, [
                'admin_id' => (int)$this->auth->id,
                'user_id'  => (int)$row['id'],
                'money'    => number_format($money, 2, '.', ''),
            ]);
            $this->error($e->getMessage());
        }

        $this->success();
    }

}

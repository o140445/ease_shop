<?php

namespace app\index\service;

use think\Db;
use think\Validate;

class CenterService
{
    protected $financeLog;

    public function __construct()
    {
        $this->financeLog = new FinanceLogService();
    }

    public function saveProfile($userId, array $data)
    {
        $data = [
            'nickname' => trim($data['nickname'] ?? ''),
        ];
        $validate = new Validate([
            'nickname' => 'require|length:2,50',
        ], [
            'nickname.require' => __('Please enter nickname'),
            'nickname.length'  => __('Nickname must be 2 to 50 characters'),
        ]);
        if (!$validate->check($data)) {
            throw new \Exception($validate->getError());
        }

        Db::name('shop_user')->where('id', $userId)->update([
            'nickname'   => $data['nickname'],
            'updatetime' => time(),
        ]);
    }

    public function savePayPassword($user, $oldpassword, $newpassword, $renewpassword)
    {
        $secret = $this->getPaymentPasswordSecret($user);
        $rule = [
            'newpassword'   => 'require|length:6,30',
            'renewpassword' => 'require|confirm:newpassword',
        ];
        if ($secret) {
            $rule['oldpassword'] = 'require|length:6,30';
        }
        $validate = new Validate($rule, [
            'oldpassword.require'   => __('Please enter old payment password'),
            'newpassword.require'   => __('Please enter new payment password'),
            'newpassword.length'    => __('Payment password must be 6 to 30 characters'),
            'renewpassword.confirm' => __('The two payment passwords do not match'),
        ]);
        if (!$validate->check(compact('oldpassword', 'newpassword', 'renewpassword'))) {
            throw new \Exception($validate->getError());
        }
        if ($secret && $secret['password'] !== md5(md5($oldpassword) . $secret['salt'])) {
            throw new \Exception(__('Old payment password is incorrect'));
        }

        $salt = substr(md5(uniqid((string)mt_rand(), true)), 0, 8);
        Db::name('shop_user')->where('id', $user['id'])->update([
            'pay_password' => md5(md5($newpassword) . $salt),
            'pay_salt'     => $salt,
            'updatetime'   => time(),
        ]);
    }

    public function saveAddress($userId, array $data)
    {
        $data = [
            'consignee'   => trim($data['consignee'] ?? ''),
            'mobile'      => trim($data['mobile'] ?? ''),
            'country'     => '',
            'province'    => '',
            'city'        => '',
            'district'    => '',
            'address'     => trim($data['address'] ?? ''),
            'postal_code' => '',
            'is_default'  => (int)($data['is_default'] ?? 0),
        ];
        $validate = new Validate([
            'consignee' => 'require|length:2,50',
            'mobile'    => 'require|max:30',
            'address'   => 'require|length:3,255',
        ], [
            'consignee.require' => __('Please enter consignee'),
            'mobile.require'    => __('Please enter mobile number'),
            'address.require'   => __('Please enter detailed address'),
        ]);
        if (!$validate->check($data)) {
            throw new \Exception($validate->getError());
        }

        Db::startTrans();
        try {
            $addressCount = Db::name('shop_user_address')->where('user_id', $userId)->whereNull('deletetime')->lock(true)->count();
            if (!$addressCount) {
                $data['is_default'] = 1;
            }
            if ($data['is_default']) {
                Db::name('shop_user_address')->where('user_id', $userId)->update(['is_default' => 0]);
            }
            $data['user_id'] = $userId;
            $data['createtime'] = time();
            $data['updatetime'] = time();
            Db::name('shop_user_address')->insert($data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function setDefaultAddress($userId, $id)
    {
        Db::startTrans();
        try {
            $address = Db::name('shop_user_address')->where('id', $id)->where('user_id', $userId)->whereNull('deletetime')->lock(true)->find();
            if (!$address) {
                throw new \Exception(__('Address does not exist'));
            }
            Db::name('shop_user_address')->where('user_id', $userId)->update(['is_default' => 0]);
            Db::name('shop_user_address')->where('id', $id)->update(['is_default' => 1, 'updatetime' => time()]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function deleteAddress($userId, $id)
    {
        Db::name('shop_user_address')->where('id', $id)->where('user_id', $userId)->update(['deletetime' => time()]);
    }

    public function saveBank($userId, array $data)
    {
        $data = [
            'bank_name'  => trim($data['bank_name'] ?? ''),
            'card_no'    => trim($data['card_no'] ?? ''),
            'realname'   => trim($data['realname'] ?? ''),
            'is_default' => (int)($data['is_default'] ?? 0),
        ];
        $validate = new Validate([
            'bank_name' => 'require|length:2,120',
            'card_no'   => 'require|length:3,80',
            'realname'  => 'require|length:2,80',
        ], [
            'bank_name.require' => __('Please enter bank name'),
            'card_no.require'   => __('Please enter account number'),
            'realname.require'  => __('Please enter name'),
        ]);
        if (!$validate->check($data)) {
            throw new \Exception($validate->getError());
        }

        Db::startTrans();
        try {
            $bankCount = Db::name('shop_user_bank')->where('user_id', $userId)->whereNull('deletetime')->lock(true)->count();
            if (!$bankCount) {
                $data['is_default'] = 1;
            }
            if ($data['is_default']) {
                Db::name('shop_user_bank')->where('user_id', $userId)->update(['is_default' => 0]);
            }
            $data['user_id'] = $userId;
            $data['bank_branch'] = '';
            $data['mobile'] = '';
            $data['id_card'] = '';
            $data['status'] = 'normal';
            $data['createtime'] = time();
            $data['updatetime'] = time();
            Db::name('shop_user_bank')->insert($data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function setDefaultBank($userId, $id)
    {
        Db::startTrans();
        try {
            $bank = Db::name('shop_user_bank')->where('id', $id)->where('user_id', $userId)->whereNull('deletetime')->lock(true)->find();
            if (!$bank) {
                throw new \Exception(__('Bank card does not exist'));
            }
            Db::name('shop_user_bank')->where('user_id', $userId)->update(['is_default' => 0]);
            Db::name('shop_user_bank')->where('id', $id)->update(['is_default' => 1, 'updatetime' => time()]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function deleteBank($userId, $id)
    {
        Db::name('shop_user_bank')->where('id', $id)->where('user_id', $userId)->update(['deletetime' => time()]);
    }

    public function createRecharge($userId, array $data)
    {
        $money = round((float)($data['money'] ?? 0), 2);
        if ($money <= 0) {
            $this->financeLog->operation('recharge_apply', 'fail', [
                'user_id' => $userId,
                'money'   => number_format($money, 2, '.', ''),
                'error'   => __('Please enter recharge amount'),
            ]);
            throw new \Exception(__('Please enter recharge amount'));
        }

        Db::startTrans();
        try {
            $rechargeNo = 'RC' . date('YmdHis') . mt_rand(1000, 9999);
            $rechargeId = Db::name('shop_recharge')->insertGetId([
                'recharge_no' => $rechargeNo,
                'user_id'     => $userId,
                'money'       => number_format($money, 2, '.', ''),
                'give_money'  => '0.00',
                'pay_money'   => number_format($money, 2, '.', ''),
                'pay_type'    => 'offline',
                'pay_status'  => 'unpaid',
                'voucher'     => trim($data['voucher'] ?? ''),
                'remark'      => trim($data['remark'] ?? ''),
                'createtime'  => time(),
                'updatetime'  => time(),
            ]);
            Db::commit();
            $this->financeLog->operation('recharge_apply', 'success', [
                'user_id'     => $userId,
                'recharge_id' => $rechargeId,
                'recharge_no' => $rechargeNo,
                'money'       => number_format($money, 2, '.', ''),
            ]);
        } catch (\Exception $e) {
            Db::rollback();
            $this->financeLog->transactionFail('recharge_apply', $e, [
                'user_id' => $userId,
                'money'   => number_format($money, 2, '.', ''),
            ]);
            throw $e;
        }
    }

    public function createWithdraw($user, array $data)
    {
        $money = round((float)($data['money'] ?? 0), 2);
        $bankId = (int)($data['bank_id'] ?? 0);

        Db::startTrans();
        try {
            $freshUser = Db::name('shop_user')->where('id', $user['id'])->lock(true)->find();
            $bank = Db::name('shop_user_bank')->where('id', $bankId)->where('user_id', $user['id'])->where('status', 'normal')->whereNull('deletetime')->lock(true)->find();
            if (!$bank) {
                throw new \Exception(__('Please select withdraw bank card'));
            }
            if ($money <= 0) {
                throw new \Exception(__('Please enter withdraw amount'));
            }
            if ($money > (float)$freshUser['money']) {
                throw new \Exception(__('Insufficient account balance'));
            }
            $withdrawNo = 'WD' . date('YmdHis') . mt_rand(1000, 9999);
            $withdrawId = Db::name('shop_withdraw')->insertGetId([
                'withdraw_no'  => $withdrawNo,
                'user_id'      => $user['id'],
                'bank_id'      => $bank['id'],
                'realname'     => $bank['realname'],
                'card_no'      => $bank['card_no'],
                'bank_name'    => $bank['bank_name'],
                'bank_branch'  => $bank['bank_branch'],
                'money'        => number_format($money, 2, '.', ''),
                'fee'          => '0.00',
                'actual_money' => number_format($money, 2, '.', ''),
                'status'       => 'pending',
                'applytime'    => time(),
                'createtime'   => time(),
                'updatetime'   => time(),
            ]);
            Db::commit();
            $this->financeLog->operation('withdraw_apply', 'success', [
                'user_id'     => $user['id'],
                'withdraw_id' => $withdrawId,
                'withdraw_no' => $withdrawNo,
                'money'       => number_format($money, 2, '.', ''),
                'balance'     => number_format((float)$freshUser['money'], 2, '.', ''),
            ]);
        } catch (\Exception $e) {
            Db::rollback();
            $this->financeLog->transactionFail('withdraw_apply', $e, [
                'user_id' => $user['id'],
                'bank_id' => $bankId,
                'money'   => number_format($money, 2, '.', ''),
            ]);
            throw $e;
        }
    }

    public function getPaymentPasswordSecret($user)
    {
        if (empty($user['pay_password']) || empty($user['pay_salt'])) {
            return null;
        }
        return [
            'password' => $user['pay_password'],
            'salt'     => $user['pay_salt'],
        ];
    }
}

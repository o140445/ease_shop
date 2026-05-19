<?php

namespace app\index\controller;

use app\index\service\CenterService;
use app\index\service\ShopAuthService;
use think\Db;
use think\Validate;

class Center extends Index
{
    public function index()
    {
        $user = $this->requireShopLogin();
        $this->view->assign('title', __('Member center'));
        $this->assignCenterBase($user, 'center');
        $orders = Db::name('shop_order')->alias('o')
            ->join('__SHOP_ORDER_RECYCLE__ r', 'r.order_id=o.id', 'LEFT')
            ->where('o.user_id', $user['id'])
            ->field('o.*,r.status as recycle_status')
            ->order('o.id desc')
            ->limit(6)
            ->select();
        $recharges = Db::name('shop_recharge')->where('user_id', $user['id'])->order('id desc')->limit(5)->select();
        $withdraws = Db::name('shop_withdraw')->where('user_id', $user['id'])->order('id desc')->limit(5)->select();
        $addresses = Db::name('shop_user_address')->where('user_id', $user['id'])->whereNull('deletetime')->order('is_default desc,id desc')->select();
        $banks = Db::name('shop_user_bank')->where('user_id', $user['id'])->where('status', 'normal')->whereNull('deletetime')->order('is_default desc,id desc')->select();
        $orderStats = $this->getOrderStats($user['id']);
        $this->view->assign(compact('orders', 'recharges', 'withdraws', 'addresses', 'banks', 'orderStats'));
        return $this->view->fetch();
    }

    public function orders()
    {
        $user = $this->requireShopLogin();
        $this->view->assign('title', __('Order list'));
        $this->assignCenterBase($user, 'centerorders');
        $status = $this->request->param('status', 'all');
        $statusTabs = [
            'all'       => __('All orders'),
            'unpaid'    => __('Pending payment'),
            'paid'      => __('Pending shipment'),
            'shipped'   => __('Pending receipt'),
            'completed' => __('Completed'),
            'returned'  => __('Returned'),
            'recycled'  => __('Recycled'),
        ];
        if (!isset($statusTabs[$status])) {
            $status = 'all';
        }
        $query = Db::name('shop_order')->alias('o')
            ->join('__SHOP_ORDER_RECYCLE__ r', 'r.order_id=o.id', 'LEFT')
            ->where('o.user_id', $user['id'])
            ->field('o.*,r.status as recycle_status')
            ->order('o.id desc');
        if ($status !== 'all') {
            $query->where('o.status', $status);
        }
        $orders = $query->paginate(10, false, [
            'query' => ['status' => $status, 'lang' => $this->shopLang],
        ]);
        $this->view->assign(compact('orders', 'status', 'statusTabs'));
        return $this->view->fetch();
    }

    public function profile()
    {
        $user = $this->requireShopLogin();
        if ($this->request->isPost()) {
            try {
                (new CenterService())->saveProfile($user['id'], $this->request->post());
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success(__('Profile saved'), url('center/profile', ['lang' => $this->shopLang]));
        }
        $user = Db::name('shop_user')->where('id', $user['id'])->find();
        $this->view->assign('title', __('Profile information'));
        $this->assignCenterBase($user, 'centerprofile');
        return $this->view->fetch();
    }

    public function account()
    {
        $user = $this->requireShopLogin();
        $this->view->assign('title', __('Account settings'));
        $this->assignCenterBase($user, 'centeraccount');
        return $this->view->fetch();
    }

    public function address()
    {
        $user = $this->requireShopLogin();
        if ($this->request->isPost()) {
            $action = $this->request->post('action', 'add');
            if ($action === 'set_default') {
                try {
                    (new CenterService())->setDefaultAddress($user['id'], (int)$this->request->post('id', 0));
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success(__('Default address set'), url('center/address', ['lang' => $this->shopLang]));
            }
            if ($action === 'delete') {
                (new CenterService())->deleteAddress($user['id'], (int)$this->request->post('id', 0));
                $this->success(__('Address deleted'), url('center/address', ['lang' => $this->shopLang]));
            }

            try {
                (new CenterService())->saveAddress($user['id'], $this->request->post());
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success(__('Address added'), url('center/address', ['lang' => $this->shopLang]));
        }
        $this->view->assign('title', __('Shipping address'));
        $this->assignCenterBase($user, 'centeraddress');
        $addresses = Db::name('shop_user_address')->where('user_id', $user['id'])->whereNull('deletetime')->order('is_default desc,id desc')->select();
        $this->view->assign(compact('addresses'));
        return $this->view->fetch();
    }

    public function addressadd()
    {
        $user = $this->requireShopLogin();
        if ($this->request->isPost()) {
            try {
                (new CenterService())->saveAddress($user['id'], $this->request->post());
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success(__('Address added'), url('center/address', ['lang' => $this->shopLang]));
        }
        $this->view->assign('title', __('Add address'));
        $this->assignCenterBase($user, 'centeraddress');
        return $this->view->fetch();
    }

    public function bank()
    {
        $user = $this->requireShopLogin();
        if ($this->request->isPost()) {
            $action = $this->request->post('action', '');
            if ($action === 'set_default') {
                try {
                    (new CenterService())->setDefaultBank($user['id'], (int)$this->request->post('id', 0));
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success(__('Default bank card set'), url('center/bank', ['lang' => $this->shopLang]));
            }
            if ($action === 'delete') {
                (new CenterService())->deleteBank($user['id'], (int)$this->request->post('id', 0));
                $this->success(__('Bank card deleted'), url('center/bank', ['lang' => $this->shopLang]));
            }
            $this->error(__('Invalid action type'));
        }
        $this->view->assign('title', __('Bank cards'));
        $this->assignCenterBase($user, 'centerbank');
        $banks = Db::name('shop_user_bank')->where('user_id', $user['id'])->where('status', 'normal')->whereNull('deletetime')->order('is_default desc,id desc')->select();
        $this->view->assign(compact('banks'));
        return $this->view->fetch();
    }

    public function bankadd()
    {
        $user = $this->requireShopLogin();
        if ($this->request->isPost()) {
            try {
                (new CenterService())->saveBank($user['id'], $this->request->post());
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success(__('Bank card added'), url('center/bank', ['lang' => $this->shopLang]));
        }
        $this->view->assign('title', __('Add bank card'));
        $this->assignCenterBase($user, 'centerbank');
        return $this->view->fetch();
    }

    public function password()
    {
        $user = $this->requireShopLogin();
        if ($this->request->isPost()) {
            $oldpassword = $this->request->post('oldpassword', '', null);
            $newpassword = $this->request->post('newpassword', '', null);
            $renewpassword = $this->request->post('renewpassword', '', null);
            $validate = new Validate([
                'oldpassword'   => 'require|length:6,30',
                'newpassword'   => 'require|length:6,30',
                'renewpassword' => 'require|confirm:newpassword',
            ], [
                'oldpassword.require'   => __('Please enter old password'),
                'newpassword.require'   => __('Please enter new password'),
                'newpassword.length'    => __('New password must be 6 to 30 characters'),
                'renewpassword.confirm' => __('The two new passwords do not match'),
            ]);
            if (!$validate->check(compact('oldpassword', 'newpassword', 'renewpassword'))) {
                $this->error($validate->getError());
            }
            try {
                (new ShopAuthService())->changePassword($user['id'], $oldpassword, $newpassword);
                $this->success(__('Password changed, please login again'), url('user/login', ['lang' => $this->shopLang]));
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }
        $this->view->assign('title', __('Login password'));
        $this->assignCenterBase($user, 'centerpassword');
        return $this->view->fetch();
    }

    public function paypassword()
    {
        $user = $this->requireShopLogin();
        $centerService = new CenterService();
        $secret = $centerService->getPaymentPasswordSecret($user);
        $redirectUrl = $this->request->param('url', '', null);
        if ($redirectUrl === '' || stripos($redirectUrl, '://') !== false) {
            $redirectUrl = url('center/account', ['lang' => $this->shopLang]);
        }
        if ($this->request->isPost()) {
            $oldpassword = $this->request->post('oldpassword', '', null);
            $newpassword = $this->request->post('newpassword', '', null);
            $renewpassword = $this->request->post('renewpassword', '', null);
            try {
                $centerService->savePayPassword($user, $oldpassword, $newpassword, $renewpassword);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success(__('Payment password saved'), $redirectUrl);
        }
        $hasPayPassword = $secret ? 1 : 0;
        $this->view->assign('title', __('Payment password'));
        $this->assignCenterBase($user, 'centerpaypassword');
        $this->view->assign(compact('hasPayPassword', 'redirectUrl'));
        return $this->view->fetch();
    }

    public function wallet()
    {
        $user = $this->requireShopLogin();
        if ($this->request->isPost()) {
            $action = $this->request->post('action', '');
            if ($action === 'recharge') {
                /*
                try {
                    (new CenterService())->createRecharge($user['id'], $this->request->post());
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success(__('Recharge request submitted, waiting for confirmation'), url('center/wallet', ['wallet_tab' => 'recharge', 'lang' => $this->shopLang]));
                */
                $this->success(__('Please contact customer service'), $this->site['customer'] ?: url('center/wallet', ['wallet_tab' => 'recharge', 'lang' => $this->shopLang]));
            }
            if ($action === 'withdraw') {
                try {
                    (new CenterService())->createWithdraw($user, $this->request->post());
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success(__('Withdraw request submitted, waiting for review'), url('center/wallet', ['wallet_tab' => 'withdraw', 'lang' => $this->shopLang]));
            }
            if ($action === 'bank') {
                try {
                    (new CenterService())->saveBank($user['id'], $this->request->post());
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success(__('Bank card added'), url('center/wallet', ['lang' => $this->shopLang]));
            }
            $this->error(__('Invalid action type'));
        }
        $user = Db::name('shop_user')->where('id', $user['id'])->find();
        $recharges = Db::name('shop_recharge')->where('user_id', $user['id'])->order('id desc')->limit(10)->select();
        $withdraws = Db::name('shop_withdraw')->where('user_id', $user['id'])->order('id desc')->limit(10)->select();
        $banks = Db::name('shop_user_bank')->where('user_id', $user['id'])->where('status', 'normal')->whereNull('deletetime')->order('is_default desc,id desc')->select();
        $walletTab = $this->request->param('wallet_tab', 'overview');
        if (!in_array($walletTab, ['overview', 'recharge', 'recharge_record', 'withdraw', 'withdraw_record'])) {
            $walletTab = 'overview';
        }
        $titleMap = [
            'overview'        => __('Recharge and withdraw'),
            'recharge'        => __('Recharge'),
            'recharge_record' => __('Recharge records'),
            'withdraw'        => __('Withdraw'),
            'withdraw_record' => __('Withdraw records'),
        ];
        $this->view->assign('title', $titleMap[$walletTab]);
        $this->assignCenterBase($user, $walletTab === 'withdraw' ? 'centerwithdraw' : 'centerrecharge');
        $this->view->assign(compact('recharges', 'withdraws', 'banks', 'walletTab'));
        return $this->view->fetch();
    }
}

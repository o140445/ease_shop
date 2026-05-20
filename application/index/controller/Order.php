<?php

namespace app\index\controller;

use app\index\service\OrderService;
use app\index\service\CenterService;

class Order extends Index
{
    public function detail()
    {
        $user = $this->requireShopLogin();
        $id = (int)$this->request->param('id', 0);
        try {
            list($order, $items) = (new OrderService())->getOrderDetail($user['id'], $id);
        } catch (\Exception $e) {
            $this->error(__($e->getMessage()));
        }

        $hasPayPassword = (new CenterService())->getPaymentPasswordSecret($user) ? 1 : 0;
        $this->view->assign('title', __('Order detail') . ' ' . $order['order_no']);
        $this->view->assign(compact('user', 'order', 'items', 'hasPayPassword'));
        return $this->view->fetch();
    }

    public function pay()
    {
        $user = $this->requireShopLogin();
        if (!$this->request->isPost()) {
            $this->error(__('Please confirm payment on the order page'), url('center/orders', ['lang' => $this->shopLang]));
        }

        $payPassword = $this->request->post('pay_password', '', null);
        try {
            (new OrderService())->payWithBalance($user['id'], (int)$this->request->post('id', 0), $payPassword);
        } catch (\Exception $e) {
            if ($e->getMessage() === __('Please set payment password first')) {
                $this->error(__($e->getMessage()), url('center/paypassword', ['url' => url('order/detail', ['id' => (int)$this->request->post('id', 0), 'lang' => $this->shopLang]), 'lang' => $this->shopLang]));
            }
            $this->error(__($e->getMessage()));
        }

        $redirectUrl = url('center/orders', ['status' => 'paid', 'lang' => $this->shopLang]);
        if ($this->request->isAjax()) {
            $this->success(__('Payment successful'), $redirectUrl);
        }
        $this->redirect($redirectUrl);
    }

    public function returnorder()
    {
        $user = $this->requireShopLogin();
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        try {
            (new OrderService())->applyReturn($user['id'], (int)$this->request->post('id', 0));
        } catch (\Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success(__('Return request submitted'), url('center/orders', ['lang' => $this->shopLang]));
    }

    public function complete()
    {
        $user = $this->requireShopLogin();
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $orderId = (int)$this->request->post('id', 0);
        try {
            (new OrderService())->completeByUser($user['id'], $orderId);
        } catch (\Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success(__('Order completed successfully'), url('order/detail', ['id' => $orderId, 'lang' => $this->shopLang]));
    }

    public function recycle()
    {
        $user = $this->requireShopLogin();
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        try {
            (new OrderService())->applyRecycle($user['id'], (int)$this->request->post('id', 0));
        } catch (\Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success(__('Recycle audit submitted'), url('center/orders', ['status' => 'completed', 'lang' => $this->shopLang]));
    }
}

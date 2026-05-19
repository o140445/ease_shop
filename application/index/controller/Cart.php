<?php

namespace app\index\controller;

use app\index\service\CartService;
use app\index\service\OrderService;

class Cart extends Index
{
    public function index()
    {
        $user = $this->requireShopLogin();
        $this->view->assign('title', __('Cart'));
        list($items, $total) = (new CartService())->getCartItems($user['id']);
        $this->view->assign(compact('user', 'items', 'total'));
        return $this->view->fetch();
    }

    public function checkout()
    {
        $user = $this->requireShopLogin();
        $this->view->assign('title', __('Checkout'));
        $orderService = new OrderService();

        if (!$this->request->isPost()) {
            try {
                $data = $orderService->getFormattedCheckoutData($user);
            } catch (\Exception $e) {
                $this->error($e->getMessage(), url('product/index', ['lang' => $this->shopLang]));
            }
            $this->view->assign(array_merge(['user' => $user], $data));
            return $this->view->fetch();
        }

        try {
            $orderId = $orderService->createFromCart($user);
        } catch (\Exception $e) {
            $this->error($e->getMessage() ?: __('Order creation failed, please try again later'));
        }

        $this->redirect('order/detail', ['id' => $orderId, 'lang' => $this->shopLang]);
    }

    public function add()
    {
        $user = $this->requireShopLogin();
        if (!$this->request->isPost()) {
            $this->error(__('Please add to cart from the product page'), url('product/index', ['lang' => $this->shopLang]));
        }

        try {
            (new CartService())->add(
                $user['id'],
                (int)$this->request->post('product_id', $this->request->post('id', 0)),
                (int)$this->request->post('sku_id', 0),
                max(1, (int)$this->request->post('quantity', 1))
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->redirect('cart/index', ['lang' => $this->shopLang]);
    }

    public function update()
    {
        $user = $this->requireShopLogin();
        try {
            (new CartService())->updateQuantity(
                $user['id'],
                (int)$this->request->post('id', 0),
                max(1, (int)$this->request->post('quantity', 1))
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->redirect('cart/index', ['lang' => $this->shopLang]);
    }

    public function remove()
    {
        $user = $this->requireShopLogin();
        if (!$this->request->isPost()) {
            $this->error(__('Please remove products from the cart'), url('cart/index', ['lang' => $this->shopLang]));
        }

        try {
            (new CartService())->remove($user['id'], (int)$this->request->post('id', 0));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->redirect('cart/index', ['lang' => $this->shopLang]);
    }
}

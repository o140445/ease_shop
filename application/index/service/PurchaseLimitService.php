<?php

namespace app\index\service;

use think\Db;

class PurchaseLimitService
{
    public function assertProductCanBePurchased($userId, $productId, $cartQuantity)
    {
        $limit = $this->getSingleItemLimit();
        if ($limit <= 0) {
            return;
        }

        $cartQuantity = max(0, (int)$cartQuantity);
        if ($cartQuantity > $limit) {
            throw new \Exception(__('Already purchased, unable to purchase again'));
        }

        $purchasedQuantity = $this->getPurchasedQuantity($userId, $productId);
        if ($purchasedQuantity + $cartQuantity > $limit) {
            throw new \Exception(__('Already purchased, unable to purchase again'));
        }
    }

    public function assertCartCanBePurchased($userId, array $items)
    {
        $limit = $this->getSingleItemLimit();
        if ($limit <= 0) {
            return;
        }

        $productQuantities = [];
        foreach ($items as $item) {
            $productId = (int)$item['product_id'];
            if (!isset($productQuantities[$productId])) {
                $productQuantities[$productId] = 0;
            }
            $productQuantities[$productId] += (int)$item['quantity'];
        }

        foreach ($productQuantities as $productId => $quantity) {
            $this->assertProductCanBePurchased($userId, $productId, $quantity);
        }
    }

    public function getSingleItemLimit()
    {
        $value = trim((string)config('site.single_item_limit'));
        if ($value === '' || in_array(strtolower($value), ['0', 'false', 'off', 'no'], true)) {
            return 0;
        }
        return max(1, (int)$value);
    }

    protected function getPurchasedQuantity($userId, $productId)
    {
        return (int)Db::name('shop_order_item')->alias('item')
            ->join('__SHOP_ORDER__ orders', 'orders.id=item.order_id', 'INNER')
            ->where('item.user_id', (int)$userId)
            ->where('item.product_id', (int)$productId)
            ->where('orders.pay_status', 'paid')
            ->where('orders.status', 'not in', ['cancelled', 'returned', 'refunded'])
            ->sum('item.quantity');
    }
}

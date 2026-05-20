<?php

namespace app\index\service;

use think\Db;

class CartService
{
    protected $purchaseLimit;

    public function __construct()
    {
        $this->purchaseLimit = new PurchaseLimitService();
    }

    public function getCartItems($userId)
    {
        $items = Db::name('shop_cart')->alias('cart')
            ->join('__SHOP_PRODUCT__ product', 'product.id=cart.product_id', 'LEFT')
            ->join('__SHOP_PRODUCT_SKU__ sku', 'sku.id=cart.sku_id', 'LEFT')
            ->where('cart.user_id', $userId)
            ->field('cart.*,product.title,product.main_image,product.price as product_price,product.stock as product_stock,sku.price as sku_price,sku.stock as sku_stock,sku.spec_json')
            ->order('cart.id desc')
            ->select();

        $total = 0;
        foreach ($items as &$item) {
            $item['display_price'] = $item['sku_id'] && $item['sku_price'] !== null ? $item['sku_price'] : $item['product_price'];
            $item['line_total'] = number_format((float)$item['display_price'] * (int)$item['quantity'], 2, '.', '');
            $total += (float)$item['line_total'];
        }
        unset($item);

        return [$items, number_format($total, 2, '.', '')];
    }

    public function add($userId, $productId, $skuId, $quantity)
    {
        Db::startTrans();
        try {
            $this->assertCanPurchase($userId);
            $product = Db::name('shop_product')->where('id', $productId)->where('status', 'normal')->lock(true)->find();
            if (!$product) {
                throw new \Exception(__('Product not found'));
            }

            if ($skuId) {
                $sku = Db::name('shop_product_sku')
                    ->where('id', $skuId)
                    ->where('product_id', $productId)
                    ->where('status', 'normal')
                    ->lock(true)
                    ->find();
                if (!$sku) {
                    throw new \Exception(__('Product specification does not exist'));
                }
            }

            $currentProductCartQuantity = (int)Db::name('shop_cart')
                ->where('user_id', $userId)
                ->where('product_id', $productId)
                ->lock(true)
                ->sum('quantity');
            $this->purchaseLimit->assertProductCanBePurchased($userId, $productId, $currentProductCartQuantity + $quantity);

            $cart = Db::name('shop_cart')->where([
                'user_id'    => $userId,
                'product_id' => $productId,
                'sku_id'     => $skuId,
            ])->lock(true)->find();

            $now = time();
            if ($cart) {
                Db::name('shop_cart')->where('id', $cart['id'])->update([
                    'quantity'   => (int)$cart['quantity'] + $quantity,
                    'selected'   => 1,
                    'updatetime' => $now,
                ]);
            } else {
                Db::name('shop_cart')->insert([
                    'user_id'    => $userId,
                    'product_id' => $productId,
                    'sku_id'     => $skuId,
                    'quantity'   => $quantity,
                    'selected'   => 1,
                    'createtime' => $now,
                    'updatetime' => $now,
                ]);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    protected function assertCanPurchase($userId)
    {
        $user = Db::name('shop_user')->where('id', (int)$userId)->where('status', 'normal')->lock(true)->find();
        if (!$user) {
            throw new \Exception(__('User does not exist'));
        }
        if ((float)($user['frozen_money'] ?? 0) > 0) {
            throw new \Exception(__('Unable to purchase'));
        }
    }

    public function updateQuantity($userId, $cartId, $quantity)
    {
        Db::startTrans();
        try {
            $cart = Db::name('shop_cart')->where('id', $cartId)->where('user_id', $userId)->lock(true)->find();
            if (!$cart) {
                throw new \Exception(__('Cart product does not exist'));
            }
            $otherProductCartQuantity = (int)Db::name('shop_cart')
                ->where('user_id', $userId)
                ->where('product_id', (int)$cart['product_id'])
                ->where('id', '<>', (int)$cart['id'])
                ->lock(true)
                ->sum('quantity');
            $this->purchaseLimit->assertProductCanBePurchased($userId, (int)$cart['product_id'], $otherProductCartQuantity + $quantity);

            Db::name('shop_cart')->where('id', $cartId)->update([
                'quantity'   => $quantity,
                'updatetime' => time(),
            ]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function remove($userId, $cartId)
    {
        Db::startTrans();
        try {
            Db::name('shop_cart')->where('id', $cartId)->where('user_id', $userId)->delete();
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
}

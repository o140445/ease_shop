<?php

namespace app\index\service;

use think\Db;

class OrderService
{
    protected $financeLog;
    protected $purchaseLimit;

    public function __construct()
    {
        $this->financeLog = new FinanceLogService();
        $this->purchaseLimit = new PurchaseLimitService();
    }

    public function getCheckoutData($user)
    {
        $user = Db::name('shop_user')->where('id', (int)$user['id'])->where('status', 'normal')->find();
        if (!$user) {
            throw new \Exception(__('User does not exist'));
        }
        if ((float)($user['frozen_money'] ?? 0) > 0) {
            throw new \Exception(__('Unable to purchase'));
        }
        $items = Db::name('shop_cart')->alias('cart')
            ->join('__SHOP_PRODUCT__ product', 'product.id=cart.product_id AND product.status="normal"', 'INNER')
            ->join('__SHOP_PRODUCT_SKU__ sku', 'sku.id=cart.sku_id', 'LEFT')
            ->where('cart.user_id', $user['id'])
            ->field('cart.*,product.sn,product.title,product.main_image,product.price as product_price,product.stock as product_stock,sku.sku_code,sku.price as sku_price,sku.stock as sku_stock,sku.spec_json')
            ->order('cart.id desc')
            ->select();

        if (!$items) {
            throw new \Exception(__('Your cart is empty'));
        }
        $this->purchaseLimit->assertCartCanBePurchased($user['id'], $items);

        $productAmount = 0;
        $totalQuantity = 0;
        foreach ($items as &$item) {
            $item['display_price'] = $item['sku_id'] && $item['sku_price'] !== null ? $item['sku_price'] : $item['product_price'];
            $item['line_total'] = round((float)$item['display_price'] * (int)$item['quantity'], 2);
            $productAmount += $item['line_total'];
            $totalQuantity += (int)$item['quantity'];
        }
        unset($item);

        $level = $user['level_id'] ? Db::name('shop_user_level')->where('id', $user['level_id'])->where('status', 'normal')->find() : null;
        $discountRate = $level ? min(100, max(0, (float)$level['discount_rate'])) : 100.00;
        $levelName = $level ? $level['name'] : __('Regular member');
        $levelDiscountAmount = round($productAmount * max(0, 100 - $discountRate) / 100, 2);
        $freightAmount = 0.00;
        $payAmount = max(0, round($productAmount + $freightAmount - $levelDiscountAmount, 2));
        $address = $this->getDefaultAddress($user['id']);

        return compact('items', 'level', 'levelName', 'discountRate', 'productAmount', 'totalQuantity', 'levelDiscountAmount', 'freightAmount', 'payAmount', 'address');
    }

    public function getFormattedCheckoutData($user)
    {
        $data = $this->getCheckoutData($user);
        foreach ($data['items'] as &$item) {
            $item['line_total'] = number_format((float)$item['line_total'], 2, '.', '');
            $item['display_price'] = number_format((float)$item['display_price'], 2, '.', '');
        }
        unset($item);

        $data['productAmount'] = number_format($data['productAmount'], 2, '.', '');
        $data['freightAmount'] = number_format($data['freightAmount'], 2, '.', '');
        $data['levelDiscountAmount'] = number_format($data['levelDiscountAmount'], 2, '.', '');
        $data['payAmount'] = number_format($data['payAmount'], 2, '.', '');
        $data['discountRate'] = number_format($data['discountRate'], 2, '.', '');

        return $data;
    }

    public function createFromCart($user)
    {
        Db::startTrans();
        try {
            $data = $this->getCheckoutData($user);
            $items = $data['items'];
            $level = $data['level'];
            $address = $data['address'];
            $now = time();
            $orderNo = 'OD' . date('YmdHis') . mt_rand(1000, 9999);

            $orderId = Db::name('shop_order')->insertGetId([
                'order_no'               => $orderNo,
                'user_id'                => $user['id'],
                'status'                 => 'unpaid',
                'pay_type'               => 'balance',
                'pay_status'             => 'unpaid',
                'product_amount'         => number_format($data['productAmount'], 2, '.', ''),
                'freight_amount'         => number_format($data['freightAmount'], 2, '.', ''),
                'level_id'               => $level ? $level['id'] : 0,
                'level_name'             => $level ? $level['name'] : '',
                'level_discount_rate'    => number_format($data['discountRate'], 2, '.', ''),
                'level_discount_amount'  => number_format($data['levelDiscountAmount'], 2, '.', ''),
                'discount_amount'        => number_format($data['levelDiscountAmount'], 2, '.', ''),
                'pay_amount'             => number_format($data['payAmount'], 2, '.', ''),
                'total_quantity'         => $data['totalQuantity'],
                'receiver_name'          => $address ? $address['consignee'] : $user['nickname'],
                'receiver_mobile'        => $address ? $address['mobile'] : $user['mobile'],
                'receiver_country'       => $address ? $address['country'] : '',
                'receiver_province'      => $address ? $address['province'] : '',
                'receiver_city'          => $address ? $address['city'] : '',
                'receiver_district'      => $address ? $address['district'] : '',
                'receiver_address'       => $address ? $address['address'] : '',
                'receiver_postal_code'   => $address ? $address['postal_code'] : '',
                'createtime'             => $now,
                'updatetime'             => $now,
            ]);

            foreach ($items as $item) {
                Db::name('shop_order_item')->insert([
                    'order_id'      => $orderId,
                    'order_no'      => $orderNo,
                    'user_id'       => $user['id'],
                    'product_id'    => $item['product_id'],
                    'sku_id'        => $item['sku_id'],
                    'product_sn'    => $item['sn'],
                    'sku_code'      => $item['sku_code'] ?: '',
                    'title'         => $item['title'],
                    'sku_spec'      => $item['spec_json'] ?: '',
                    'image'         => $item['main_image'],
                    'price'         => number_format((float)$item['display_price'], 2, '.', ''),
                    'quantity'      => (int)$item['quantity'],
                    'total_price'   => number_format((float)$item['line_total'], 2, '.', ''),
                    'refund_status' => 'none',
                    'createtime'    => $now,
                ]);
            }

            Db::name('shop_cart')->where('user_id', $user['id'])->delete();
            Db::commit();
            $this->financeLog->operation('create_order', 'success', [
                'user_id'    => $user['id'],
                'order_id'   => $orderId,
                'order_no'   => $orderNo,
                'pay_amount' => number_format($data['payAmount'], 2, '.', ''),
            ]);
            return $orderId;
        } catch (\Exception $e) {
            Db::rollback();
            $this->financeLog->transactionFail('create_order', $e, [
                'user_id' => $user['id'],
            ]);
            throw $e;
        }
    }

    public function getOrderDetail($userId, $orderId)
    {
        $order = Db::name('shop_order')->where('id', $orderId)->where('user_id', $userId)->find();
        if (!$order) {
            throw new \Exception(__('Order does not exist'));
        }
        $recycle = Db::name('shop_order_recycle')->where('order_id', $order['id'])->find();
        $order['recycle_status'] = $recycle ? $recycle['status'] : '';

        $items = Db::name('shop_order_item')->where('order_id', $order['id'])->order('id asc')->select();
        return [$order, $items];
    }

    public function applyReturn($userId, $orderId)
    {
        Db::startTrans();
        try {
            $order = Db::name('shop_order')->where('id', $orderId)->where('user_id', $userId)->lock(true)->find();
            if (!$order) {
                throw new \Exception(__('Order does not exist'));
            }
            if ($order['status'] !== 'completed') {
                throw new \Exception(__('Only completed orders can be returned'));
            }
            if ($order['pay_status'] !== 'paid') {
                throw new \Exception(__('Only paid orders can be operated'));
            }
            $recycle = Db::name('shop_order_recycle')->where('order_id', (int)$order['id'])->find();
            if ($recycle && in_array($recycle['status'], ['pending', 'approved', 'recycled'], true)) {
                throw new \Exception(__('Order recycle audit is pending'));
            }

            $exists = Db::name('shop_refund')
                ->where('order_id', (int)$order['id'])
                ->where('user_id', (int)$userId)
                ->where('type', 'return_refund')
                ->where('status', 'in', ['pending', 'approved', 'returned', 'refunded'])
                ->find();
            if ($exists) {
                throw new \Exception(__('Order return request is pending'));
            }

            $now = time();
            $refundNo = 'RF' . date('YmdHis') . mt_rand(1000, 9999);
            Db::name('shop_refund')->insertGetId([
                'refund_no'       => $refundNo,
                'order_id'        => (int)$order['id'],
                'order_item_id'   => 0,
                'order_no'        => $order['order_no'],
                'user_id'         => (int)$userId,
                'product_id'      => 0,
                'sku_id'          => 0,
                'type'            => 'return_refund',
                'reason'          => __('User requested return'),
                'description'     => '',
                'images'          => '',
                'apply_money'     => number_format((float)$order['pay_amount'], 2, '.', ''),
                'refund_money'    => '0.00',
                'quantity'        => (int)$order['total_quantity'],
                'status'          => 'pending',
                'audit_admin_id'  => 0,
                'audit_remark'    => '',
                'refund_admin_id' => 0,
                'refund_remark'   => '',
                'applytime'       => $now,
                'createtime'      => $now,
                'updatetime'      => $now,
            ]);
            Db::name('shop_order')->where('id', (int)$order['id'])->update([
                'status'     => 'refunding',
                'updatetime' => $now,
            ]);
            Db::name('shop_order_log')->insert([
                'order_id'    => (int)$order['id'],
                'order_no'    => $order['order_no'],
                'user_id'     => (int)$userId,
                'admin_id'    => 0,
                'action'      => 'return_apply',
                'from_status' => $order['status'],
                'to_status'   => 'refunding',
                'memo'        => __('User requested return'),
                'createtime'  => $now,
            ]);
            Db::commit();
            $this->financeLog->operation('order_return_apply', 'success', [
                'user_id'  => $userId,
                'order_id' => (int)$order['id'],
                'order_no' => $order['order_no'],
                'money'    => number_format((float)$order['pay_amount'], 2, '.', ''),
            ]);
        } catch (\Exception $e) {
            Db::rollback();
            $this->financeLog->transactionFail('order_return_apply', $e, [
                'user_id'  => $userId,
                'order_id' => $orderId,
            ]);
            throw $e;
        }
    }

    public function completeByUser($userId, $orderId)
    {
        Db::startTrans();
        try {
            $order = Db::name('shop_order')->where('id', $orderId)->where('user_id', $userId)->lock(true)->find();
            if (!$order) {
                throw new \Exception(__('Order does not exist'));
            }
            if ($order['status'] !== 'shipped') {
                throw new \Exception(__('Only shipped orders can be completed'));
            }
            if ($order['pay_status'] !== 'paid') {
                throw new \Exception(__('Only paid orders can be operated'));
            }

            $now = time();
            Db::name('shop_order')->where('id', (int)$order['id'])->update([
                'status'       => 'completed',
                'completetime' => $now,
                'updatetime'   => $now,
            ]);
            Db::name('shop_order_log')->insert([
                'order_id'    => (int)$order['id'],
                'order_no'    => $order['order_no'],
                'user_id'     => (int)$userId,
                'admin_id'    => 0,
                'action'      => 'user_complete',
                'from_status' => $order['status'],
                'to_status'   => 'completed',
                'memo'        => __('User confirmed receipt'),
                'createtime'  => $now,
            ]);
            Db::commit();
            $this->financeLog->operation('order_complete', 'success', [
                'user_id'  => $userId,
                'order_id' => (int)$order['id'],
                'order_no' => $order['order_no'],
            ]);
        } catch (\Exception $e) {
            Db::rollback();
            $this->financeLog->transactionFail('order_complete', $e, [
                'user_id'  => $userId,
                'order_id' => $orderId,
            ]);
            throw $e;
        }
    }

    public function applyRecycle($userId, $orderId)
    {
        Db::startTrans();
        try {
            $order = Db::name('shop_order')->where('id', $orderId)->where('user_id', $userId)->lock(true)->find();
            if (!$order) {
                throw new \Exception(__('Order does not exist'));
            }
            if ($order['status'] !== 'completed') {
                throw new \Exception(__('Only completed orders can be recycled'));
            }
            if ($order['pay_status'] !== 'paid') {
                throw new \Exception(__('Only paid orders can be operated'));
            }

            $now = time();
            $recycleData = [
                'order_id'         => (int)$order['id'],
                'order_no'         => $order['order_no'],
                'user_id'          => (int)$userId,
                'order_status'     => $order['status'],
                'pay_status'       => $order['pay_status'],
                'pay_amount'       => $order['pay_amount'],
                'recycle_amount'   => $order['pay_amount'],
                'total_quantity'   => (int)$order['total_quantity'],
                'receiver_name'    => $order['receiver_name'],
                'receiver_mobile'  => $order['receiver_mobile'],
                'receiver_address' => trim($order['receiver_country'] . ' ' . $order['receiver_province'] . ' ' . $order['receiver_city'] . ' ' . $order['receiver_district'] . ' ' . $order['receiver_address']),
                'status'           => 'pending',
                'recycle_admin_id' => 0,
                'audit_admin_id'   => 0,
                'restore_admin_id' => 0,
                'delete_admin_id'  => 0,
                'memo'             => __('User submitted order recycle audit'),
                'recycletime'      => $now,
                'audittime'        => null,
                'restoretime'      => null,
                'deletetime'       => null,
                'createtime'       => $now,
                'updatetime'       => $now,
            ];

            $recycle = Db::name('shop_order_recycle')->where('order_id', (int)$order['id'])->lock(true)->find();
            if ($recycle) {
                if ($recycle['status'] === 'pending') {
                    throw new \Exception(__('Order recycle audit is pending'));
                }
                if (in_array($recycle['status'], ['approved', 'recycled'], true)) {
                    throw new \Exception(__('Order has already been recycled'));
                }
                Db::name('shop_order_recycle')->where('id', (int)$recycle['id'])->update($recycleData);
                $recycleId = (int)$recycle['id'];
            } else {
                $recycleId = Db::name('shop_order_recycle')->insertGetId($recycleData);
            }
            Db::name('shop_order_recycle_log')->insert([
                'recycle_id' => $recycleId,
                'order_id'   => (int)$order['id'],
                'order_no'   => $order['order_no'],
                'user_id'    => (int)$userId,
                'admin_id'   => 0,
                'action'     => 'recycle',
                'memo'       => __('User submitted order recycle audit'),
                'createtime' => $now,
            ]);
            Db::commit();
            $this->financeLog->operation('order_recycle_apply', 'success', [
                'user_id'        => $userId,
                'order_id'       => (int)$order['id'],
                'order_no'       => $order['order_no'],
                'recycle_id'     => $recycleId,
                'recycle_amount' => number_format((float)$order['pay_amount'], 2, '.', ''),
            ]);
        } catch (\Exception $e) {
            Db::rollback();
            $this->financeLog->transactionFail('order_recycle_apply', $e, [
                'user_id'  => $userId,
                'order_id' => $orderId,
            ]);
            throw $e;
        }
    }

    public function payWithBalance($userId, $orderId, $payPassword = '')
    {
        Db::startTrans();
        try {
            $order = Db::name('shop_order')->where('id', $orderId)->where('user_id', $userId)->lock(true)->find();
            if (!$order) {
                throw new \Exception(__('Order does not exist'));
            }
            if ($order['pay_status'] === 'paid') {
                Db::commit();
                $this->financeLog->operation('balance_pay', 'skip_paid', [
                    'user_id'  => $userId,
                    'order_id' => $order['id'],
                    'order_no' => $order['order_no'],
                ]);
                return $order;
            }

            $freshUser = Db::name('shop_user')->where('id', $userId)->lock(true)->find();
            if (!$freshUser) {
                throw new \Exception(__('User does not exist'));
            }
            $this->verifyPaymentPassword($freshUser, $payPassword);
            if ((float)$freshUser['money'] < (float)$order['pay_amount']) {
                throw new \Exception(__('Insufficient balance to complete payment'));
            }

            $now = time();
            $before = (float)$freshUser['money'];
            $after = round($before - (float)$order['pay_amount'], 2);
            $totalOrderAmount = round((float)$freshUser['total_order_amount'] + (float)$order['product_amount'], 2);
            $totalPayAmount = round((float)$freshUser['total_pay_amount'] + (float)$order['pay_amount'], 2);
            $totalRechargeAmount = (float)$freshUser['total_recharge_amount'];
            $userUpdate = [
                'money'            => number_format($after, 2, '.', ''),
                'total_order_amount' => number_format($totalOrderAmount, 2, '.', ''),
                'total_pay_amount' => number_format($totalPayAmount, 2, '.', ''),
                'updatetime'       => $now,
            ];
            $upgradeLevel = $this->getEligibleUpgradeLevel($freshUser, $totalOrderAmount, $totalPayAmount, $totalRechargeAmount);
            if ($upgradeLevel) {
                $userUpdate['level_id'] = (int)$upgradeLevel['id'];
            }
            Db::name('shop_user')->where('id', $userId)->update($userUpdate);
            Db::name('shop_order')->where('id', $order['id'])->update([
                'status'     => 'paid',
                'pay_status' => 'paid',
                'paidtime'   => $now,
                'updatetime' => $now,
            ]);
            $this->financeLog->balanceChange([
                'user_id'    => $userId,
                'type'       => 'pay',
                'order_id'   => $order['id'],
                'money'      => '-' . number_format((float)$order['pay_amount'], 2, '.', ''),
                'before'     => number_format($before, 2, '.', ''),
                'after'      => number_format($after, 2, '.', ''),
                'memo'       => __('Order balance payment') . ': ' . $order['order_no'],
            ]);
            if ($upgradeLevel) {
                $this->financeLog->operation('member_level_upgrade', 'success', [
                    'user_id'       => $userId,
                    'from_level_id' => (int)$freshUser['level_id'],
                    'to_level_id'   => (int)$upgradeLevel['id'],
                    'to_level_name' => $upgradeLevel['name'],
                    'order_id'      => (int)$order['id'],
                    'order_no'      => $order['order_no'],
                ]);
            }

            Db::commit();
            $this->financeLog->operation('balance_pay', 'success', [
                'user_id'    => $userId,
                'order_id'   => $order['id'],
                'order_no'   => $order['order_no'],
                'money'      => '-' . number_format((float)$order['pay_amount'], 2, '.', ''),
                'before'     => number_format($before, 2, '.', ''),
                'after'      => number_format($after, 2, '.', ''),
            ]);
            return $order;
        } catch (\Exception $e) {
            Db::rollback();
            $this->financeLog->transactionFail('balance_pay', $e, [
                'user_id'  => $userId,
                'order_id' => $orderId,
            ]);
            throw $e;
        }
    }

    protected function verifyPaymentPassword($user, $payPassword)
    {
        $secret = (new CenterService())->getPaymentPasswordSecret($user);
        if (!$secret) {
            throw new \Exception(__('Please set payment password first'));
        }

        $payPassword = trim((string)$payPassword);
        if ($payPassword === '') {
            throw new \Exception(__('Please enter payment password'));
        }

        if ($secret['password'] !== md5(md5($payPassword) . $secret['salt'])) {
            throw new \Exception(__('Payment password is incorrect'));
        }
    }

    protected function getEligibleUpgradeLevel($user, $totalOrderAmount, $totalPayAmount, $totalRechargeAmount)
    {
        $currentLevel = $user['level_id'] ? Db::name('shop_user_level')->where('id', (int)$user['level_id'])->find() : null;
        $currentLevelValue = $currentLevel ? (int)$currentLevel['level'] : 0;
        $levels = Db::name('shop_user_level')
            ->where('status', 'normal')
            ->order('level desc,id desc')
            ->select();

        foreach ($levels as $level) {
            if ((int)$level['level'] <= $currentLevelValue) {
                continue;
            }
            $conditions = [];
            if ((float)$level['min_order_amount'] > 0) {
                $conditions[] = $totalOrderAmount >= (float)$level['min_order_amount'];
            }
            if ((float)$level['min_pay_amount'] > 0) {
                $conditions[] = $totalPayAmount >= (float)$level['min_pay_amount'];
            }
            if ((float)$level['min_recharge_amount'] > 0) {
                $conditions[] = $totalRechargeAmount >= (float)$level['min_recharge_amount'];
            }
            if (!$conditions || in_array(true, $conditions, true)) {
                return $level;
            }
        }

        return null;
    }

    protected function getDefaultAddress($userId)
    {
        return Db::name('shop_user_address')
            ->where('user_id', $userId)
            ->whereNull('deletetime')
            ->order('is_default desc,id desc')
            ->find();
    }
}

<?php

namespace app\admin\controller\shop;

use app\common\controller\Backend;
use app\index\service\FinanceLogService;
use think\Db;

/**
 * 商城-订单
 *
 * @icon fa fa-circle-o
 */
class Order extends Backend
{
    /**
     * Order模型对象
     * @var \app\admin\model\shop\Order
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\shop\Order;
        $this->view->assign("statusList", $this->model->getStatusList());
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
        $orderIds = [];
        foreach ($rows as $row) {
            $orderIds[] = (int)$row['id'];
        }
        $recycleStatuses = $orderIds ? Db::name('shop_order_recycle')->where('order_id', 'in', array_unique($orderIds))->column('status', 'order_id') : [];
        foreach ($rows as &$row) {
            $user = isset($users[$row['user_id']]) ? $users[$row['user_id']] : [];
            $row['user'] = [
                'username' => isset($user['username']) ? $user['username'] : '',
                'nickname' => isset($user['nickname']) ? $user['nickname'] : '',
            ];
            $row['recycle_status'] = isset($recycleStatuses[$row['id']]) ? $recycleStatuses[$row['id']] : '';
        }
        unset($row);
        $result = ['total' => $list->total(), 'rows' => $rows];
        return json($result);
    }


    public function detail($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        $items = Db::name('shop_order_item')
            ->where('order_id', (int)$row['id'])
            ->order('id', 'asc')
            ->select();

        $this->view->assign('row', $row);
        $this->view->assign('items', $items);
        return $this->view->fetch();
    }


    public function ship($ids = null)
    {
        $this->changeStatus($ids, 'paid', 'shipped', __('Order shipped successfully'));
    }


    public function complete($ids = null)
    {
        $this->changeStatus($ids, 'shipped', 'completed', __('Order completed successfully'));
    }


    public function recycle($ids = null)
    {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $ids = $ids ?: $this->request->post('ids');
        $order = $this->model->get($ids);
        if (!$order) {
            $this->error(__('No Results were found'));
        }
        if ($order['status'] !== 'completed') {
            $this->error(__('Only completed orders can be recycled'));
        }
        if ($order['status'] === 'recycled') {
            $this->error(__('Order has already been recycled'));
        }

        $now = time();
        Db::startTrans();
        try {
            $recycleData = [
                'order_id'         => (int)$order['id'],
                'order_no'         => $order['order_no'],
                'user_id'          => (int)$order['user_id'],
                'order_status'     => $order['status'],
                'pay_status'       => $order['pay_status'],
                'pay_amount'       => $order['pay_amount'],
                'recycle_amount'   => $order['pay_amount'],
                'total_quantity'   => (int)$order['total_quantity'],
                'receiver_name'    => $order['receiver_name'],
                'receiver_mobile'  => $order['receiver_mobile'],
                'receiver_address' => trim($order['receiver_country'] . ' ' . $order['receiver_province'] . ' ' . $order['receiver_city'] . ' ' . $order['receiver_district'] . ' ' . $order['receiver_address']),
                'status'           => 'pending',
                'recycle_admin_id' => (int)$this->auth->id,
                'audit_admin_id'   => 0,
                'restore_admin_id' => 0,
                'delete_admin_id'  => 0,
                'memo'             => __('Admin submitted order recycle audit'),
                'recycletime'      => $now,
                'audittime'        => null,
                'restoretime'      => null,
                'deletetime'       => null,
                'createtime'       => $now,
                'updatetime'       => $now,
            ];

            $recycle = Db::name('shop_order_recycle')->where('order_id', (int)$order['id'])->find();
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
                'user_id'    => (int)$order['user_id'],
                'admin_id'   => (int)$this->auth->id,
                'action'     => 'recycle',
                'memo'       => __('Admin submitted order recycle audit'),
                'createtime' => $now,
            ]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success(__('Order recycle audit submitted'));
    }

    public function returnorder($ids = null)
    {
        $ids = $ids ?: $this->request->param('ids');
        $order = $this->model->get($ids);
        if (!$order) {
            $this->error(__('No Results were found'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $order);
            return $this->view->fetch('return_order');
        }
        if ($order['status'] !== 'completed') {
            $this->error(__('Only completed orders can be returned'));
        }
        if ($order['pay_status'] !== 'paid') {
            $this->error(__('Only paid orders can be operated'));
        }
        $recycle = Db::name('shop_order_recycle')->where('order_id', (int)$order['id'])->find();
        if ($recycle && in_array($recycle['status'], ['pending', 'approved', 'recycled'], true)) {
            $this->error(__('Order recycle audit is pending'));
        }

        $returnMoney = round((float)$this->request->post('return_money', 0), 2);
        if ($returnMoney <= 0) {
            $this->error(__('Please enter return amount'));
        }
        if ($returnMoney > (float)$order['pay_amount']) {
            $this->error(__('Return amount cannot exceed paid amount'));
        }

        $financeLog = new FinanceLogService();
        $now = time();
        Db::startTrans();
        try {
            $freshOrder = Db::name('shop_order')->where('id', (int)$order['id'])->lock(true)->find();
            if (!$freshOrder || $freshOrder['status'] !== 'completed') {
                throw new \Exception(__('Invalid order status'));
            }
            $freshUser = Db::name('shop_user')->where('id', (int)$freshOrder['user_id'])->lock(true)->find();
            if (!$freshUser) {
                throw new \Exception(__('User does not exist'));
            }

            $before = (float)$freshUser['money'];
            $after = round($before + $returnMoney, 2);
            $refundNo = 'RF' . date('YmdHis') . mt_rand(1000, 9999);
            $refundId = Db::name('shop_refund')->insertGetId([
                'refund_no'       => $refundNo,
                'order_id'        => (int)$freshOrder['id'],
                'order_item_id'   => 0,
                'order_no'        => $freshOrder['order_no'],
                'user_id'         => (int)$freshOrder['user_id'],
                'product_id'      => 0,
                'sku_id'          => 0,
                'type'            => 'return_refund',
                'reason'          => __('Admin order return'),
                'description'     => '',
                'images'          => '',
                'apply_money'     => number_format($returnMoney, 2, '.', ''),
                'refund_money'    => number_format($returnMoney, 2, '.', ''),
                'quantity'        => (int)$freshOrder['total_quantity'],
                'status'          => 'refunded',
                'audit_admin_id'  => (int)$this->auth->id,
                'audit_remark'    => __('Admin order return approved'),
                'refund_admin_id' => (int)$this->auth->id,
                'refund_remark'   => __('Admin returned amount to balance'),
                'applytime'       => $now,
                'audittime'       => $now,
                'returntime'      => $now,
                'refundtime'      => $now,
                'createtime'      => $now,
                'updatetime'      => $now,
            ]);
            Db::name('shop_user')->where('id', (int)$freshOrder['user_id'])->update([
                'money'      => number_format($after, 2, '.', ''),
                'updatetime' => $now,
            ]);
            Db::name('shop_order')->where('id', (int)$freshOrder['id'])->update([
                'status'       => 'returned',
                'pay_status'   => 'refunded',
                'admin_remark' => trim((string)$freshOrder['admin_remark'] . ' ' . __('Returned amount') . ': ' . number_format($returnMoney, 2, '.', '')),
                'updatetime'   => $now,
            ]);
            $financeLog->balanceChange([
                'user_id'   => (int)$freshOrder['user_id'],
                'type'      => 'refund',
                'order_id'  => (int)$freshOrder['id'],
                'refund_id' => $refundId,
                'money'     => number_format($returnMoney, 2, '.', ''),
                'before'    => number_format($before, 2, '.', ''),
                'after'     => number_format($after, 2, '.', ''),
                'memo'      => __('Order return refund') . ': ' . $freshOrder['order_no'],
            ]);
            Db::commit();
            $financeLog->operation('order_return', 'success', [
                'admin_id'     => (int)$this->auth->id,
                'user_id'      => (int)$freshOrder['user_id'],
                'order_id'     => (int)$freshOrder['id'],
                'order_no'     => $freshOrder['order_no'],
                'refund_id'    => $refundId,
                'return_money' => number_format($returnMoney, 2, '.', ''),
                'before'       => number_format($before, 2, '.', ''),
                'after'        => number_format($after, 2, '.', ''),
            ]);
        } catch (\Exception $e) {
            Db::rollback();
            $financeLog->transactionFail('order_return', $e, [
                'admin_id'     => (int)$this->auth->id,
                'order_id'     => (int)$order['id'],
                'return_money' => number_format($returnMoney, 2, '.', ''),
            ]);
            $this->error($e->getMessage());
        }

        $this->success(__('Order returned successfully'));
    }

    protected function changeStatus($ids, $fromStatus, $toStatus, $successMessage)
    {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $ids = $ids ?: $this->request->post('ids');
        $order = $this->model->get($ids);
        if (!$order) {
            $this->error(__('No Results were found'));
        }
        if ($order['status'] !== $fromStatus) {
            $this->error(__('Invalid order status'));
        }
        if ($order['pay_status'] !== 'paid') {
            $this->error(__('Only paid orders can be operated'));
        }

        Db::startTrans();
        try {
            $order->save([
                'status'     => $toStatus,
                'updatetime' => time(),
            ]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success($successMessage);
    }

}

<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use fast\Date;
use think\Db;

/**
 * 控制台
 *
 * @icon   fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        $column = [];
        $starttime = Date::unixtime('day', -6);
        $endtime = Date::unixtime('day', 0, 'end');
        for ($time = $starttime; $time <= $endtime;) {
            $column[] = date("Y-m-d", $time);
            $time += 86400;
        }

        $orderSeries = array_fill_keys($column, 0);
        $revenueSeries = array_fill_keys($column, 0);
        $orderRows = Db::name('shop_order')
            ->where('createtime', 'between', [$starttime, $endtime])
            ->field('COUNT(*) AS order_count, SUM(CASE WHEN pay_status = "paid" THEN pay_amount ELSE 0 END) AS revenue, DATE_FORMAT(FROM_UNIXTIME(createtime), "%Y-%m-%d") AS stat_date')
            ->group('stat_date')
            ->select();
        foreach ($orderRows as $row) {
            $orderSeries[$row['stat_date']] = (int)$row['order_count'];
            $revenueSeries[$row['stat_date']] = round((float)$row['revenue'], 2);
        }

        $todayStart = Date::unixtime('day', 0);
        $todayEnd = Date::unixtime('day', 0, 'end');
        $paidWhere = ['pay_status' => 'paid'];
        $todayPaidWhere = [
            'pay_status' => 'paid',
            'paidtime'   => ['between', [$todayStart, $todayEnd]],
        ];
        $recentOrders = Db::name('shop_order')->alias('o')
            ->join('__SHOP_USER__ u', 'u.id = o.user_id', 'LEFT')
            ->field('o.id,o.order_no,o.status,o.pay_status,o.pay_amount,o.total_quantity,o.createtime,u.username,u.nickname')
            ->order('o.id desc')
            ->limit(8)
            ->select();
        foreach ($recentOrders as &$order) {
            $order['status_text'] = $this->orderStatusText($order['status']);
            $order['pay_status_text'] = $this->payStatusText($order['pay_status']);
            $order['user_name'] = $order['nickname'] ?: ($order['username'] ?: '-');
            $order['pay_amount_text'] = number_format((float)$order['pay_amount'], 2, '.', '');
        }
        unset($order);
        $totalRevenue = (float)Db::name('shop_order')->where($paidWhere)->sum('pay_amount');
        $todayRevenue = (float)Db::name('shop_order')->where($todayPaidWhere)->sum('pay_amount');

        $this->view->assign([
            'totalUser'       => Db::name('shop_user')->count(),
            'todayUser'       => Db::name('shop_user')->where('createtime', 'between', [$todayStart, $todayEnd])->count(),
            'totalProduct'    => Db::name('shop_product')->count(),
            'normalProduct'   => Db::name('shop_product')->where('status', 'normal')->count(),
            'totalOrder'      => Db::name('shop_order')->count(),
            'todayOrder'      => Db::name('shop_order')->where('createtime', 'between', [$todayStart, $todayEnd])->count(),
            'totalRevenue'    => number_format($totalRevenue, 2, '.', ''),
            'todayRevenue'    => number_format($todayRevenue, 2, '.', ''),
            'pendingPay'      => Db::name('shop_order')->where('status', 'unpaid')->count(),
            'pendingShip'     => Db::name('shop_order')->where('status', 'paid')->count(),
            'pendingReceive'  => Db::name('shop_order')->where('status', 'shipped')->count(),
            'pendingRefund'   => Db::name('shop_refund')->where('status', 'pending')->count(),
            'pendingRecycle'  => Db::name('shop_order_recycle')->where('status', 'pending')->count(),
            'pendingRecharge' => Db::name('shop_recharge')->where('pay_status', 'unpaid')->count(),
            'pendingWithdraw' => Db::name('shop_withdraw')->where('status', 'pending')->count(),
            'recentOrders'    => $recentOrders,
        ]);

        $this->assignconfig('column', array_keys($orderSeries));
        $this->assignconfig('orderdata', array_values($orderSeries));
        $this->assignconfig('revenuedata', array_values($revenueSeries));

        return $this->view->fetch();
    }

    protected function orderStatusText($status)
    {
        $list = [
            'unpaid'    => '待支付',
            'paid'      => '待发货',
            'shipped'   => '待收货',
            'completed' => '已完成',
            'returned'  => '已退货',
            'cancelled' => '已取消',
            'refunding' => '退款中',
            'refunded'  => '已退款',
            'recycled'  => '已回收',
        ];
        return isset($list[$status]) ? $list[$status] : $status;
    }

    protected function payStatusText($status)
    {
        $list = [
            'unpaid'   => '未支付',
            'paid'     => '已支付',
            'refunded' => '已退款',
        ];
        return isset($list[$status]) ? $list[$status] : $status;
    }

}

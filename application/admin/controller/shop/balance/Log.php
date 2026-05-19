<?php

namespace app\admin\controller\shop\balance;

use app\common\controller\Backend;
use think\Db;

/**
 * 商城-余额流水
 *
 * @icon fa fa-circle-o
 */
class Log extends Backend
{

    /**
     * Log模型对象
     * @var \app\admin\model\shop\balance\Log
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\shop\balance\Log;
        $this->view->assign("typeList", $this->model->getTypeList());
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
        $orderIds = [];
        $rechargeIds = [];
        $withdrawIds = [];
        $refundIds = [];
        foreach ($rows as $row) {
            if (!empty($row['user_id'])) {
                $userIds[] = (int)$row['user_id'];
            }
            if (!empty($row['order_id'])) {
                $orderIds[] = (int)$row['order_id'];
            }
            if (!empty($row['recharge_id'])) {
                $rechargeIds[] = (int)$row['recharge_id'];
            }
            if (!empty($row['withdraw_id'])) {
                $withdrawIds[] = (int)$row['withdraw_id'];
            }
            if (!empty($row['refund_id'])) {
                $refundIds[] = (int)$row['refund_id'];
            }
        }

        $users = $userIds ? Db::name('shop_user')->where('id', 'in', array_unique($userIds))->column('id,username,nickname', 'id') : [];
        $orders = $orderIds ? Db::name('shop_order')->where('id', 'in', array_unique($orderIds))->column('id,order_no', 'id') : [];
        $recharges = $rechargeIds ? Db::name('shop_recharge')->where('id', 'in', array_unique($rechargeIds))->column('id,recharge_no', 'id') : [];
        $withdraws = $withdrawIds ? Db::name('shop_withdraw')->where('id', 'in', array_unique($withdrawIds))->column('id,withdraw_no', 'id') : [];
        $refunds = $refundIds ? Db::name('shop_refund')->where('id', 'in', array_unique($refundIds))->column('id,refund_no', 'id') : [];

        foreach ($rows as &$row) {
            $user = isset($users[$row['user_id']]) ? $users[$row['user_id']] : [];
            $row['user'] = [
                'username' => isset($user['username']) ? $user['username'] : '',
                'nickname' => isset($user['nickname']) ? $user['nickname'] : '',
            ];
            $row['order'] = ['order_no' => isset($orders[$row['order_id']]) ? $orders[$row['order_id']] : ''];
            $row['recharge'] = ['recharge_no' => isset($recharges[$row['recharge_id']]) ? $recharges[$row['recharge_id']] : ''];
            $row['withdraw'] = ['withdraw_no' => isset($withdraws[$row['withdraw_id']]) ? $withdraws[$row['withdraw_id']] : ''];
            $row['refund'] = ['refund_no' => isset($refunds[$row['refund_id']]) ? $refunds[$row['refund_id']] : ''];
        }
        unset($row);

        return json(['total' => $list->total(), 'rows' => $rows]);
    }


    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}

<?php

namespace app\admin\model\shop;

use think\Model;
use traits\model\SoftDelete;

class Order extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'shop_order';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'status_text',
        'pay_type_text',
        'pay_status_text',
        'paidtime_text',
        'shiptime_text',
        'completetime_text',
        'canceltime_text'
    ];
    

    
    public function getStatusList()
    {
        return ['unpaid' => __('Status unpaid'), 'paid' => __('Status paid'), 'shipped' => __('Status shipped'), 'completed' => __('Status completed'), 'returned' => __('Status returned'), 'cancelled' => __('Status cancelled'), 'refunding' => __('Status refunding'), 'refunded' => __('Status refunded'), 'recycled' => __('Status recycled')];
    }

    public function getPayTypeList()
    {
        return ['balance' => __('Pay_type balance')];
    }

    public function getPayStatusList()
    {
        return ['unpaid' => __('Pay_status unpaid'), 'paid' => __('Pay_status paid'), 'refunded' => __('Pay_status refunded')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }


    public function getPayTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['pay_type'] ?? '');
        $list = $this->getPayTypeList();
        return $list[$value] ?? '';
    }


    public function getPayStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['pay_status'] ?? '');
        $list = $this->getPayStatusList();
        return $list[$value] ?? '';
    }


    public function getPaidtimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['paidtime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getShiptimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['shiptime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCompletetimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['completetime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCanceltimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['canceltime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setPaidtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setShiptimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setCompletetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setCanceltimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function user()
    {
        return $this->belongsTo('app\admin\model\shop\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}

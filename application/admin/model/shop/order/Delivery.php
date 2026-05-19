<?php

namespace app\admin\model\shop\order;

use think\Model;


class Delivery extends Model
{

    

    

    // 表名
    protected $name = 'shop_order_delivery';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'delivery_type_text',
        'status_text',
        'shiptime_text',
        'receivetime_text'
    ];
    

    
    public function getDeliveryTypeList()
    {
        return ['express' => __('Delivery_type express'), 'manual' => __('Delivery_type manual'), 'virtual' => __('Delivery_type virtual')];
    }

    public function getStatusList()
    {
        return ['shipped' => __('Status shipped'), 'received' => __('Status received'), 'cancelled' => __('Status cancelled')];
    }


    public function getDeliveryTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['delivery_type'] ?? '');
        $list = $this->getDeliveryTypeList();
        return $list[$value] ?? '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }


    public function getShiptimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['shiptime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getReceivetimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['receivetime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setShiptimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setReceivetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}

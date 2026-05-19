<?php

namespace app\admin\model\shop;

use think\Model;
use traits\model\SoftDelete;

class Recharge extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'shop_recharge';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'pay_type_text',
        'pay_status_text',
        'paidtime_text'
    ];
    

    
    public function getPayTypeList()
    {
        return ['offline' => __('Pay_type offline'), 'admin' => __('Pay_type admin')];
    }

    public function getPayStatusList()
    {
        return ['unpaid' => __('Pay_status unpaid'), 'paid' => __('Pay_status paid'), 'cancelled' => __('Pay_status cancelled')];
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

    protected function setPaidtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}

<?php

namespace app\admin\model\shop;

use think\Model;
use traits\model\SoftDelete;

class Withdraw extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'shop_withdraw';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'status_text',
        'applytime_text',
        'audittime_text',
        'paidtime_text'
    ];
    

    
    public function getStatusList()
    {
        return ['pending' => __('Status pending'), 'approved' => __('Status approved'), 'rejected' => __('Status rejected'), 'paid' => __('Status paid'), 'cancelled' => __('Status cancelled')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }


    public function getApplytimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['applytime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getAudittimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['audittime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getPaidtimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['paidtime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setApplytimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setAudittimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setPaidtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}

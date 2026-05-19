<?php

namespace app\admin\model\shop;

use think\Model;
use traits\model\SoftDelete;

class Refund extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'shop_refund';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'type_text',
        'status_text',
        'applytime_text',
        'audittime_text',
        'returntime_text',
        'refundtime_text'
    ];
    

    
    public function getTypeList()
    {
        return ['refund' => __('Type refund'), 'return_refund' => __('Type return_refund')];
    }

    public function getStatusList()
    {
        return ['pending' => __('Status pending'), 'approved' => __('Status approved'), 'rejected' => __('Status rejected'), 'returned' => __('Status returned'), 'refunded' => __('Status refunded'), 'cancelled' => __('Status cancelled')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
        return $list[$value] ?? '';
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


    public function getReturntimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['returntime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getRefundtimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['refundtime'] ?? '');
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

    protected function setReturntimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setRefundtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}

<?php

namespace app\admin\model\shop\order;

use think\Model;
use traits\model\SoftDelete;

class Recycle extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'shop_order_recycle';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'status_text',
        'recycletime_text',
        'audittime_text',
        'restoretime_text'
    ];
    

    
    public function getStatusList()
    {
        return ['pending' => __('Status pending'), 'approved' => __('Status approved'), 'rejected' => __('Status rejected'), 'recycled' => __('Status recycled'), 'restored' => __('Status restored'), 'deleted' => __('Status deleted')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }


    public function getRecycletimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['recycletime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getRestoretimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['restoretime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getAudittimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['audittime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setRecycletimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setRestoretimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setAudittimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}

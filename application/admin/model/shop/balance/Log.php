<?php

namespace app\admin\model\shop\balance;

use think\Model;


class Log extends Model
{

    

    

    // 表名
    protected $name = 'shop_balance_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text'
    ];
    

    
    public function getTypeList()
    {
        return ['recharge' => __('Type recharge'), 'pay' => __('Type pay'), 'refund' => __('Type refund'), 'recycle' => __('Type recycle'), 'withdraw' => __('Type withdraw'), 'withdraw_reject' => __('Type withdraw_reject'), 'adjust' => __('Type adjust')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
        return $list[$value] ?? '';
    }




}

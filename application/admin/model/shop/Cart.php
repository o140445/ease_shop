<?php

namespace app\admin\model\shop;

use think\Model;


class Cart extends Model
{

    

    

    // 表名
    protected $name = 'shop_cart';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'selected_text'
    ];
    

    
    public function getSelectedList()
    {
        return ['0' => __('Selected 0'), '1' => __('Selected 1')];
    }


    public function getSelectedTextAttr($value, $data)
    {
        $value = $value ?: ($data['selected'] ?? '');
        $list = $this->getSelectedList();
        return $list[$value] ?? '';
    }




}

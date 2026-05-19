<?php

namespace app\admin\model\shop;

use think\Model;
use traits\model\SoftDelete;

class User extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'shop_user';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'status_text'
    ];
    

    public function getStatusList()
    {
        return ['normal' => __('Status normal'), 'hidden' => __('Status hidden'), 'locked' => __('Status locked')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }

    public function level()
    {
        return $this->belongsTo('app\admin\model\shop\Level', 'level_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}

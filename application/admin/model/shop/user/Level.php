<?php

namespace app\admin\model\shop\user;

use think\Model;
use traits\model\SoftDelete;

class Level extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'shop_user_level';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'is_default_text',
        'status_text'
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            if (!$row['weigh']) {
                $pk = $row->getPk();
                $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
            }
        });
    }

    
    public function getIsDefaultList()
    {
        return ['0' => __('Is_default 0'), '1' => __('Is_default 1')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Status normal'), 'hidden' => __('Status hidden')];
    }


    public function getIsDefaultTextAttr($value, $data)
    {
        $value = $value ?: ($data['is_default'] ?? '');
        $list = $this->getIsDefaultList();
        return $list[$value] ?? '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }




}

<?php

namespace app\admin\model\shop\home;

use think\Model;
use traits\model\SoftDelete;

class Module extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'shop_home_module';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'type_text',
        'data_type_text',
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

    
    public function getTypeList()
    {
        return ['product' => __('Type product'), 'category' => __('Type category'), 'notice' => __('Type notice'), 'custom' => __('Type custom')];
    }

    public function getDataTypeList()
    {
        return ['auto' => __('Data_type auto'), 'manual' => __('Data_type manual')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Status normal'), 'hidden' => __('Status hidden')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
        return $list[$value] ?? '';
    }


    public function getDataTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['data_type'] ?? '');
        $list = $this->getDataTypeList();
        return $list[$value] ?? '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }




}

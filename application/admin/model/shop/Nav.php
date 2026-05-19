<?php

namespace app\admin\model\shop;

use think\Model;
use traits\model\SoftDelete;

class Nav extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'shop_nav';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'link_type_text',
        'target_text',
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

    
    public function getLinkTypeList()
    {
        return ['url' => __('Link_type url'), 'product' => __('Link_type product'), 'category' => __('Link_type category'), 'notice' => __('Link_type notice'), 'page' => __('Link_type page')];
    }

    public function getTargetList()
    {
        return ['self' => __('Target self'), 'blank' => __('Target blank')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Status normal'), 'hidden' => __('Status hidden')];
    }


    public function getLinkTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['link_type'] ?? '');
        $list = $this->getLinkTypeList();
        return $list[$value] ?? '';
    }


    public function getTargetTextAttr($value, $data)
    {
        $value = $value ?: ($data['target'] ?? '');
        $list = $this->getTargetList();
        return $list[$value] ?? '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }




}

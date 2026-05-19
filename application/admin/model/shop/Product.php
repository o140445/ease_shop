<?php

namespace app\admin\model\shop;

use think\Model;
use traits\model\SoftDelete;

class Product extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'shop_product';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'type_text',
        'is_sku_text',
        'is_recommend_text',
        'is_new_text',
        'is_hot_text',
        'status_text'
    ];
    

    protected static function init()
    {
        self::beforeInsert(function ($row) {
            $data = $row->getData();
            if (empty($data['type'])) {
                $row['type'] = 'normal';
            }
            if (empty($data['sn'])) {
                $row['sn'] = 'P' . date('YmdHis') . mt_rand(1000, 9999);
            }
            $data = $row->getData();
            if (empty($data['title'])) {
                $row['title'] = 'Product ' . $row['sn'];
            }
            foreach (['subtitle', 'keywords', 'description'] as $field) {
                if (empty($data[$field])) {
                    $row[$field] = '';
                }
            }
        });

        self::afterInsert(function ($row) {
            if (!$row['weigh']) {
                $pk = $row->getPk();
                $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
            }
        });
    }

    
    public function getTypeList()
    {
        return ['normal' => __('Type normal'), 'virtual' => __('Type virtual')];
    }

    public function getIsSkuList()
    {
        return ['0' => __('Is_sku 0'), '1' => __('Is_sku 1')];
    }

    public function getIsRecommendList()
    {
        return ['0' => __('Is_recommend 0'), '1' => __('Is_recommend 1')];
    }

    public function getIsNewList()
    {
        return ['0' => __('Is_new 0'), '1' => __('Is_new 1')];
    }

    public function getIsHotList()
    {
        return ['0' => __('Is_hot 0'), '1' => __('Is_hot 1')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Status normal'), 'hidden' => __('Status hidden'), 'soldout' => __('Status soldout')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
        return $list[$value] ?? '';
    }


    public function getIsSkuTextAttr($value, $data)
    {
        $value = $value ?: ($data['is_sku'] ?? '');
        $list = $this->getIsSkuList();
        return $list[$value] ?? '';
    }


    public function getIsRecommendTextAttr($value, $data)
    {
        $value = $value ?: ($data['is_recommend'] ?? '');
        $list = $this->getIsRecommendList();
        return $list[$value] ?? '';
    }


    public function getIsNewTextAttr($value, $data)
    {
        $value = $value ?: ($data['is_new'] ?? '');
        $list = $this->getIsNewList();
        return $list[$value] ?? '';
    }


    public function getIsHotTextAttr($value, $data)
    {
        $value = $value ?: ($data['is_hot'] ?? '');
        $list = $this->getIsHotList();
        return $list[$value] ?? '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }

    public function category()
    {
        return $this->belongsTo('app\admin\model\shop\Category', 'category_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}

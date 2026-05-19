<?php

namespace app\admin\model\shop;

use think\Model;
use traits\model\SoftDelete;

class Banner extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'shop_banner';
    
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
        'status_text',
        'starttime_text',
        'endtime_text'
    ];
    

    protected static function init()
    {
        self::beforeWrite(function ($row) {
            $data = $row->getData();
            $linkType = $data['link_type'] ?? 'none';

            if (!isset($data['position']) || $data['position'] === '') {
                $row['position'] = 'home';
            }
            if (!isset($data['subtitle'])) {
                $row['subtitle'] = '';
            }

            if ($linkType === 'url') {
                $row['link_id'] = 0;
                if (empty($data['link_url'])) {
                    throw new \Exception(__('Please enter link URL'));
                }
            } elseif (in_array($linkType, ['product', 'category', 'notice'], true)) {
                $row['link_url'] = '';
                if (empty($data['link_id'])) {
                    throw new \Exception(__('Please select related object'));
                }
            } else {
                $row['link_url'] = '';
                $row['link_id'] = 0;
            }
        });

        self::afterInsert(function ($row) {
            if (!$row['weigh']) {
                $pk = $row->getPk();
                $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
            }
        });
    }

    
    public function getPositionList()
    {
        return ['home' => __('Position home')];
    }

    public function getLinkTypeList()
    {
        return ['none' => __('Link_type none'), 'url' => __('Link_type url'), 'product' => __('Link_type product'), 'category' => __('Link_type category'), 'notice' => __('Link_type notice')];
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


    public function getStarttimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['starttime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getEndtimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['endtime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setStarttimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setEndtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}

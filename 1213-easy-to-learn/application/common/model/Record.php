<?php

namespace app\common\model;

use think\Model;


class Record extends Model
{

    

    

    // 表名
    protected $name = 'record';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'gender_text'
    ];
    

    
    public function getGenderList()
    {
        return ['0' => __('Gender 0'), '1' => __('Gender 1')];
    }


    public function getGenderTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['gender']) ? $data['gender'] : '');
        $list = $this->getGenderList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function area()
    {
        return $this->belongsTo('Area', 'area_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}

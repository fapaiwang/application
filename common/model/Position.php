<?php
namespace app\common\model;

class Position extends \think\Model
{
    protected $type = [
        'data' => 'json',
    ];
    public function PositionCate(){
        return $this->hasOne('position_cate','id','cate_id')->joinType('left');
    }
}
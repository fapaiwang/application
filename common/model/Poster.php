<?php
namespace app\common\model;

class Poster extends \think\Model
{
    protected $type = [
        'setting' => 'json'
    ];
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    public function PosterSpace(){
        return $this->hasOne('poster_space','id','spaceid')->joinType('left');
    }
    public function setStartdateAttr($value){
        return strtotime($value);
    }
    public function setEnddateAttr($value){
        return strtotime($value);
    }
    public function getTypeAttr($value){
        $type  = $this->typeArr();
        return $type[$value];
    }
    public function typeArr(){
        return $type  = [
            'images'  => '图片',
            'flash' => '动画',
            'text' => '文字广告',
            'code' => '代码广告'
        ];
    }
}
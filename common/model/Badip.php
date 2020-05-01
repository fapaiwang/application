<?php
namespace app\common\model;

class Badip extends \think\Model
{
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    public function setExpiresAttr($value){
        $val = $value == 0 ? $value : (time()+$value*86400);
        return $val;
    }
}
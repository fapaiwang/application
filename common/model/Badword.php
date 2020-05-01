<?php
namespace app\common\model;

class Badword extends \think\Model
{
    public function getLevelAttr($value){
        $level = $this->levelArr();
        return $level[$value];
    }
    public function levelArr(){
        $level = [
          1 => '一般',
          2 => '危险'
        ];
        return $level;
    }
}
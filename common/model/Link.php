<?php
namespace app\common\model;

class Link extends \think\Model
{
    public function linkCate(){
      return $this->hasOne('link_cate','id','cate_id')->joinType('left');
    }
}
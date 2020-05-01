<?php
namespace app\common\model;

class Nav extends \think\Model
{
    protected $auto = ['alias'];
    protected $insert = ['is_sys'];
    protected function setAliasAttr(){
        if(isset($this->cate_id) && !empty($this->cate_id)){
            $cate = getCate('pagesCate');
            if(isset($cate[$this->cate_id]))
            {
                return $cate[$this->cate_id]['alias'];
            }else{
                return '';
            }
        }else{
            return '';
        }

    }
    protected function setIsSysAttr(){
        return 0;
    }
}
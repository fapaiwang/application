<?php


namespace app\common\model;
class AgentCompany extends \think\Model
{
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    protected function setPasswordAttr($value){
        if(!empty($value)){
            return passwordEncode($value);
        }
        return '';
    }
}
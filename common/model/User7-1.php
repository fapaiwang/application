<?php
namespace app\common\model;

class User extends \think\Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'reg_time'; //指定时间字段
    protected $updateTime = false;
   /* protected $type = [
        'file'    =>  'serialize'
    ];*/
    protected $insert = ['reg_ip'];
    public function userInfo(){
        return $this->hasOne('user_info','user_id','id')->joinType('left');
    }
    protected function setTimeoutAttr($value)
    {
        if($value)
        {
            $value = strtotime($value);
        }
        return $value;
    }
    protected function setRegIpAttr()
    {
        return request()->ip();
    }
    protected function setPasswordAttr($value){
        if(!empty($value)){
            return passwordEncode($value);
        }
        return '';
    }
}
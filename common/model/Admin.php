<?php
namespace app\common\model;

class Admin extends \think\Model
{
    protected $insert = [
        'status'=>1,
        'reg_ip',
        'reg_time'
    ];
    public function role(){
        return $this->hasOne('role','id','role_id')->joinType('left');
    }
    protected function setRegIpAttr()
    {
        return request()->ip();
    }
    protected function setRegTimeAttr()
    {
        return time();
    }
    protected function setPasswordAttr($value){
        if(!empty($value)){
            return passwordEncode($value);
        }
        return '';
    }
    public function name_exists($name, $id=0) {
        $pk = $this->getPk();
        $where = "username='" . $name . "'  AND ". $pk ."<>'" . $id . "'";
        $result = $this->where($where)->count($pk);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
}
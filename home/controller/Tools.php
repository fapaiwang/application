<?php


namespace app\home\controller;
use app\common\controller\HomeBase;
class Tools extends HomeBase
{
    public function index()
    {
        return $this->fetch();
    }
    //房贷计算
    public function loan(){
        return $this->fetch();
    }
    //税费计算
    public function secondhandtax(){
        return $this->fetch();
    }
    //首付成数计算
    public function downpayment(){
        return $this->fetch();
    }
    //资质查询
    public function qualification(){
        return $this->fetch();
    }

}
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
        $seo['title'] = "房贷计算_房拍网贷款工具";
        $seo['keys']  = "房贷计算";
        $seo['desc']  = " 提供房贷计算工具，让您清楚房贷需要的金额。";
        $this->assign('seo',$seo);
        return $this->fetch();
    }
    public function loan2(){
        return $this->fetch();
    }
    //税费计算
    public function secondhandtax(){
        $seo['title'] = " 税费计算_房拍网贷款工具";
        $seo['keys']  = "税费计算";
        $seo['desc']  = "提供税费计算，让您明确需要交多少税费。";
        $this->assign('seo',$seo);
        return $this->fetch();
    }
    //首付成数计算
    public function downpayment(){
        $seo['title'] = " 首付成数测试_房拍网贷款工具";
        $seo['keys']  = "首付成数测试";
        $seo['desc']  = "提供房贷首付成数测试服务。";
        $this->assign('seo',$seo);
        return $this->fetch();
    }
    //资质查询
    public function qualification(){
        $seo['title'] = " 资质查询_房拍网贷款工具";
        $seo['keys']  = "资质查询";
        $seo['desc']  = "提供房贷资质查询服务。";
        $this->assign('seo',$seo);
        return $this->fetch();
    }

}
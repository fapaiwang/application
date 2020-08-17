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
        $seo['title'] = "房贷计算器,贷款计算器在线计算,房贷计算工具,税费计算器-金铂顺昌房拍网";
        $seo['keys']  = "房贷计算器,贷款计算器在线计算,房贷计算工具,税费计算器,金铂顺昌房拍网";
        $seo['desc']  = "金铂顺昌房拍网为您提供房贷计算器,房贷计算工具,税费计算器,贷款计算器在线计算。金铂顺昌从事法拍房15年，积累千套成交案例。要买便宜房就到金铂顺昌房拍网。";
        $this->assign('seo',$seo);
        return $this->fetch();
    }
    public function loan2(){
        return $this->fetch();
    }
    //税费计算
    public function secondhandtax(){
        $seo['title'] = "法拍房,二手房税费计算器,税费计算器-金铂顺昌房拍网";
        $seo['keys']  = "法拍房,二手房税费计算器,税费计算器,金铂顺昌房拍网";
        $seo['desc']  = "金铂顺昌房拍网为您提供法拍房,二手房税费计算器,税费计算器在线计算。金铂顺昌从事法拍房15年，积累千套成交案例。要买便宜房就到金铂顺昌房拍网。";
        $this->assign('seo',$seo);
        return $this->fetch();
    }
    //首付成数计算
    public function downpayment(){
        $seo['title'] = "首付成数测试-金铂顺昌房拍网";
        $seo['keys']  = "首付成数测试,金铂顺昌房拍网";
        $seo['desc']  = "金铂顺昌房拍网为您提供法拍房,二手房首付成数测试。金铂顺昌从事法拍房15年，积累千套成交案例。要买便宜房就到金铂顺昌房拍网。";
        $this->assign('seo',$seo);
        return $this->fetch();
    }
    //资质查询
    public function qualification(){
        $seo['title'] = "购房资质查询工具-金铂顺昌房拍网";
        $seo['keys']  = "购房资质查询工具,金铂顺昌房拍网";
        $seo['desc']  = "金铂顺昌房拍网为您提供法拍房,二手房购房资质查询。金铂顺昌从事法拍房15年，积累千套成交案例。要买便宜房就到金铂顺昌房拍网。";
        $this->assign('seo',$seo);
        return $this->fetch();
    }

}
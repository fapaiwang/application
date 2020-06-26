<?php
/**
 * Created by PhpStorm.
 * @author: al
 * Date: 2020/6/19
 * Time: 17:52
 */

namespace app\home\controller;

use app\api\controller\SecondHouse;
use app\common\controller\HomeNewBase;
use app\home\service\IndexServer;
use app\home\service\SecondServer;
use app\server;

class Promote extends HomeNewBase
{

    public function advert(){
        $IndexServer = new IndexServer();

        $statistics_num =$IndexServer->get_statistics_num();
        $recommend_house = $IndexServer->get_recommend_house(10);
//        dd($recommend_house);
        $seo['title'] = "金铂顺昌房拍网官网-专业从事不良资产处置15年,已有万+用户节省上亿资金";
        $seo['keys']  = "金铂顺昌房拍网官网,不良资产处置";
        $seo['desc']  = "金铂顺昌房拍网官网专业从事不良资产处置15年,已有万+用户节省上亿资金，汇聚千余套低于市场价25%的优质法拍房源，可以便宜放心拍房。咨询电话400 677 0028";
        $this->assign('seo',$seo);
        $this->assign('page_t',1);
        $this->assign('recommend_house', $recommend_house);
        $this->assign('pages',$recommend_house->render());
        $this->assign('statistics_num', $statistics_num);
        return $this->fetch();
    }



}
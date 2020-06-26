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
        $this->assign('page_t',1);
        $this->assign('recommend_house', $recommend_house);
        $this->assign('pages',$recommend_house->render());
        $this->assign('statistics_num', $statistics_num);
        return $this->fetch();
    }



}
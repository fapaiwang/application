<?php

namespace app\api\controller;

use app\home\service\IndexServer;
use app\home\service\SecondServer;
use app\tools\ApiResult;
use think\Controller;

class SecondHouse extends Controller
{
    use ApiResult;
    protected $Second_Server;
    protected $Index_Server;
    public function __construct(SecondServer $Second_Server,IndexServer $Index_Server)
    {
        $this->Second_Server = $Second_Server;
        $this->Index_Server = $Index_Server;
    }
    //获取今日新增房源
    public function get_today_add(){
        $res = $this->Second_Server->get_today_add();
        if (!$res->isEmpty()){
            return $this->success_o($res);
        }else{
            return $this->error_o("未查询到今日房源");
        }

    }
    //推荐房源
    public function recommend_house(){
        $limit =input('get.limit') ?? 6;
        $res =  $this->Index_Server->get_recommend_house($limit);
        if (!$res->isEmpty()){
            return $this->success_o($res);
        }else{
            return $this->error_o("未查询到推荐房源");
        }
    }
    //自由购房源
    public function restrict_house(){
        $limit =input('get.limit') ?? 6;
        $res =  $this->Index_Server->get_restrict_house($limit);
        if (!$res->isEmpty()){
            return $this->success_o($res);
        }else{
            return $this->error_o("未查询到自由购房源");
        }
    }
    //获取优质小区
    public function quality_estate(){
        $limit =input('get.limit') ?? 10;
       $res =  $this->Index_Server->get_quality_estate($limit);
        if (!$res->isEmpty()){
            return $this->success_o($res);
        }else{
            return $this->error_o("未查询到优质小区");
        }
    }
    


}
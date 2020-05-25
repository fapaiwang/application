<?php

namespace app\api\controller;


use app\common\controller\ApiBase;
use app\home\service\IndexServer;
use app\tools\ApiResult;
use app\tools\Constant;
use think\Controller;

class Banner extends Controller
{
    use ApiResult;
    protected $index_service;
    public function __construct(IndexServer $index_service)
    {
        $this->index_service = $index_service;
    }
    /**
     * 所有广告图
     * @param mixed
     * @return \think\response\Json
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index(){
        $space_id = input('get.space_id');
        if (empty($space_id)){
            return $this->error_o("space_id 不能为空");
        }
        $res =  $this->index_service->get_home_banner($space_id);
        if ($res){
            return $this->success_o($res);
        }else{
            $error= $space_id."-错误,广告图查询错误";
            return $this->error_o($error);
        }
    }

    /**
     * 首页 今日成交/正在进行/即将拍卖
     * @param mixed
     * @return \think\response\Json
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function home_second_search(){
        $arr=[];
        $banner_1 =  $this->index_service->get_home_banner(26);
        $banner_2 =  $this->index_service->get_home_banner(27);
        $arr[0]["name"] ="即将拍卖";
        $arr[0]["describe"] ="即将拍卖即将拍卖即将拍卖";
        $arr[1]["name"] ="正在进行";
        $arr[1]["describe"] ="正在进行正在进行正在进行";
        $arr[2]["name"] ="今日成交";
        $arr[2]["describe"] ="今日成交今日成交今日成交";
        if (!$banner_1->isEmpty() &&  !$banner_2->isEmpty()){
            $arr[0]["img"] =$banner_1[0]['setting'];
            $arr[1]["img"] =$banner_2[0]['setting'];
            $arr[2]["img"] =$banner_2[0]['setting'];
        }
        if ($arr){
            return $this->success_o($arr);
        }else{
            $error= "广告图查询错误";
            return $this->error_o($error);
        }
    }



}
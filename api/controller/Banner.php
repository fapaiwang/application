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



}
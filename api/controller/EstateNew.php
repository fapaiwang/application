<?php

namespace app\api\controller;
use app\common\controller\ApiBase;
use app\home\service\EstateServer;
use app\home\service\IndexServer;
use app\home\service\SecondServer;
use app\tools\ApiResult;
use app\tools\Constant;
use think\Controller;

class EstateNew extends Controller
{
    use ApiResult;
    protected $index_service;
    protected $second_service;
    protected $estate;
    protected $estate_service;
    public function __construct(IndexServer $index_service,SecondServer $second_service,\app\home\service\Estate $estate,EstateServer $estate_service)
    {
        $this->index_service = $index_service;
        $this->second_service = $second_service;
        $this->estate = $estate;
        $this->estate_service = $estate_service;
    }

    /**
     * 小区详情
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function estate_detail(){
        $id = input('id');
        if (empty($id)){
            return $this->error_o("小区id不能为空");
        }
        $info = $this->estate->detail($id);
        if (empty($info)){
            return $this->error_o("未查询到小区详情");
        }
        return $this->success_o($info);
    }
    //获取房源的成交记录
    //todo
    /**
     * 小区房源交易记录
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function transaction_record(){
        $estate_id = input('estate_id');
        if (empty($estate_id)){
            return $this->error_o("小区id不能为空");
        }
        $show = $this->estate_service->transaction_record($estate_id);
        return $this->success_o($show);
    }




}
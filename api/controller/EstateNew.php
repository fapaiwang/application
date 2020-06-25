<?php

namespace app\api\controller;


use app\common\controller\ApiBase;
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
    public function __construct(IndexServer $index_service,SecondServer $second_service)
    {
        $this->index_service = $index_service;
        $this->second_service = $second_service;
    }





}
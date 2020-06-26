<?php

namespace app\api\controller;


use app\home\controller\Api;
use app\home\service\ToolsServer;
use app\tools\ApiResult;
use app\tools\Constant;
use think\Controller;
use think\facade\Request;
use think\Log;

class Tools extends Controller
{
    use ApiResult;
    protected $ToolsService;
    protected $Api;
    public function __construct(ToolsServer $ToolsService,Api $Api)
    {
        $this->ToolsService = $ToolsService;
        $this->Api = $Api;
    }
    public function house_loan_type(){
        //dai_qipai
        $res["type"] ="商业贷";
        $res["dai_bili"] ="100,65";
        $res["dai_lilv"] =[
            ["val"=>4.41,"describe"=>"基准利率9折(5.145%)"],
            ["val"=>4.655,"describe"=>"基准利率95折(5.145%)"],
            ["val"=>4.0,"describe"=>"基准利率(5.145%)"],
            ["val"=>5.145,"describe"=>"基准利率上浮5%(5.145%)"]
        ];

    }

}
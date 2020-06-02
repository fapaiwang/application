<?php

namespace app\api\controller;

use app\home\service\SecondServer;
use app\tools\ApiResult;
use think\Controller;

header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
header("Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");

class Page extends Controller
{
    use ApiResult;

    /**
     * 首页导航
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function home_menu(){
        $res =\Config::get("setting.menu");
        if ($res){
            return $this->success_o($res);
        }else{
            return $this->error_o("未查询到首页导航");
        }
    }
    public function characteristic_second(){

    }

    


}
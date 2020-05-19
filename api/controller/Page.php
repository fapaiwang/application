<?php

namespace app\api\controller;

use app\home\service\SecondServer;
use app\tools\ApiResult;
use think\Controller;

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
            return $this->error_o("未查询到今日房源");
        }
    }
    public function characteristic_second(){

    }

    


}
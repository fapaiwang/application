<?php

namespace app\api\controller;

use app\home\service\IndexServer;
use app\home\service\SecondServer;
use app\home\service\UserService;
use app\server;
use app\tools\ApiResult;
use think\Controller;
use think\Db;

//……
class User extends Controller
{
    use ApiResult;
    protected $User_Server;

    public function __construct(UserService $User_Server)
    {
        $this->User_Server = $User_Server;
    }

    /**
     * 获取关注的房源
     * @param mixed
     * @return \think\Paginator|\think\response\Json
     * @author: al
     */
    public function followHouse(){
        if (empty(login_user())){
            return $this->error_o("请登录后再操作");
        }
        return $this->User_Server->followHouse();
    }

}
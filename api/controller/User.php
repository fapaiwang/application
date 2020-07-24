<?php

namespace app\api\controller;

use app\api\service\CsService;
use app\home\service\IndexServer;
use app\home\service\SecondServer;
use app\home\service\UserService;
use app\server;
use app\tools\ApiResult;
use app\tools\RequestResult;
use think\Controller;
use think\Db;

//……
class User extends Controller
{
    use ApiResult;
    use RequestResult;
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
        $user_id = input("param.user_id");
        if (empty($user_id)){
            return $this->error_o("用户id不能为空");
        }
        if (empty(loginUser($user_id))){
            return $this->error_o("请登录后再操作");
        }
        $info = $this->User_Server->followHouse($user_id);
        return $this->success_o($info);
    }
    /**
     * 获取预约房源
     * @param mixed
     * @return \think\Paginator|\think\response\Json
     * @author: al
     */
    public function subscribeHouse(){
        $user_id = input("param.user_id");
        if (empty($user_id)){
            return $this->error_o("用户id不能为空");
        }
        if (empty(loginUser($user_id))){
            return $this->error_o("请登录后再操作");
        }
        $info = $this->User_Server->subscribeHouse($user_id);
        return $this->success_o($info);
    }
    public function followEstate(){
        $user_id = input("param.user_id");
        if (empty($user_id)){
            return $this->error_o("用户id不能为空");
        }
        if (empty(loginUser($user_id))){
            return $this->error_o("请登录后再操作");
        }
        $info = $this->User_Server->followEstate($user_id);
        return $this->success_o($info);
    }
    public function getMac(){
        return  $mip=file_get_contents("http://city.ip138.com/city0.asp");
        return $this->get_onlineip();
        $res = new CsService();
        $res = $res->GetMac(PHP_OS);
        return $this->success_o($res);
    }
    function get_onlineip(){
        $mip= file_get_contents("http://city.ip138.com/city0.asp");
        if ($mip){
            preg_match("/\[.*\]/",$mip,$sip);
            $p= array("/\[/","/\]/");
            return preg_replace($p,"",$sip[0]);
        }
    }
}
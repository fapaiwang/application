<?php

namespace app\api\controller;

use app\api\service\CsService;
use app\home\service\SecondServer;
use app\home\service\UserService;
use app\tools\ApiResult;
use app\tools\RequestResult;
use think\Controller;

class Users extends Controller
{
    use ApiResult;
    protected $User_Server;
    protected $Second;

    public function __construct(UserService $User_Server,\app\home\controller\Second $Second)
    {
        $this->User_Server = $User_Server;
        $this->Second = $Second;
    }

    /**
     * 获取关注的房源
     * @param mixed
     * @return \think\Paginator|\think\response\Json
     * @author: al
     */
    public function followHouse(){
        $user_id = input('param.user_id');
        if (empty($user_id)){
            return $this->error_o("用户id不能为空");
        }
        if (empty(loginUser($user_id))){
            return $this->error_o("请登录后再操作");
        }
        $user_ids = $this->User_Server->getFollowHouseId($user_id);
        $info = $this->Second->getLists("","",$user_ids);
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
        if (empty(login_user($user_id))){
            return $this->error_o("请登录后再操作");
        }
        $user_ids = $this->User_Server->subscribeHouse($user_id);
        $info = $this->Second->getLists("","",$user_ids);
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
}
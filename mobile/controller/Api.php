<?php


namespace app\mobile\controller;
class Api
{
    /**
     * @return \think\response\Json
     * 新房价格走势
     */
    public function getHousePrice()
    {
        return action('home/Api/getHousePrice');
    }
    /**
     * @return \think\response\Json
     * 获取指定城市id下级区域
     */
    public function getCityChild()
    {
        $pid = input('get.pid/d',0);
        $return['code'] = 0;
        if($pid)
        {
            $info = model('city')->where('pid',$pid)->where('status',1)->order('ordid asc,id desc')->select();
            if($info)
            {
                $return['code'] = 1;
                $return['data'] = $info;
            }
            $return['points'] = $this->getCityPoint($pid);
        }
        return json($return);
    }

    /**
     * @return mixed
     * 关注
     */
    public function follow()
    {
        return action('home/Api/follow');
    }

    /**
     * @return mixed
     * 取消关注
     */
    public function userCancelFollow()
    {
        return action('home/Api/userCancelFollow');
    }
    /**
     * @return mixed
     * 发送短信
     */
    public function sendSms()
    {
        return action('home/Sms/sendSms');
    }

    /**
     * @return mixed
     * 预约
     */
    public function subscribe()
    {
        return action('home/Api/subscribe');
    }

    /**
     * 提交 问题
     */
    public function subQuestion()
    {
        return action('home/Api/subQuestion');
    }

    /**
     * @return mixed
     * 回答问题
     */
    public function answer()
    {
        return action('home/Api/answer');
    }
    /**
     * @return mixed
     * 点评提交
     */
    public function subHouseComment()
    {
        return action('home/Api/subHouseComment');
    }
    /**
     * @return \think\response\Json
     * 点评回复
     */
    public function subHouseCommentReply()
    {
        return action('home/Api/subHouseCommentReply');
    }
    public function getBanInfoById()
    {
        return action('home/Api/getBanInfoById');
    }
    public function setTop()
    {
        return action('home/Api/setTop');
    }
    /**
     * @return \think\response\Json
     * 获取城市坐标
     */
    private function getCityPoint($city)
    {
        $data = [];
        if($city)
        {
            $city_arr = getCity('cate');
            if(isset($city_arr[$city]))
            {
                $lng  = $city_arr[$city]['lng'];
                $lat  = $city_arr[$city]['lat'];
                $name = $city_arr[$city]['name'];
                $return['code'] = 1;
                $data           = ['lng'=>$lng,'lat'=>$lat,'name'=>$name];
            }
        }
        return $data;
    }
}
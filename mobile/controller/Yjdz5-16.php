<?php


namespace app\mobile\controller;

use app\common\controller\MobileBase;

class Yjdz extends MobileBase

{

    private $pageSize = 10;

    private $mod      = 'yjdz';



    /**

     * @return mixed

     * 楼盘列表

     */

    public function index()

    {

        return $this->fetch();

    }
    public function tijiao()

    {
        // $data['house_id']  = input('post.house_id/d',0);

        $data['zonge'] = input('post.zonge');
        $data['province'] = input('post.province');
        $data['city'] = input('post.city');
        $data['area'] = input('post.area');
        $data['storage'] = input('post.Storage');

        $data['create_time']    = time();

        // $sms_code          = input('post.sms_code');//短信验证码

        // $token             = input('post.__token__');

        $userInfo          = $this->getUserInfo();

        $userInfo && $data['user_id'] = $userInfo['id'];

        $setting        = getSettingCache('user');
        // print_r($data);
        // exit();

        $return['code'] = 0;
        if(db('yjdz')->insert($data))

        {
        // $return['msg']  = '提交成功';
        return $this->fetch('public/tj');
        }else{
            // $return['msg']  = '保存失败';
            return $this->fetch('public/sb');
        }

        

        return json($return);
        // return $this->fetch();

    }


    /**

     * @return mixed|string

     * 获取用户信息

     */

    private function getUserInfo()

    {

        $info = cookie('userInfo');

        $info = \org\Crypt::decrypt($info);

        return $info;

    }


}
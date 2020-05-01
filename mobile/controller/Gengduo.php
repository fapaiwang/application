<?php




namespace app\mobile\controller;

use app\common\controller\MobileBase;

class Gengduo extends MobileBase

{

    private $pageSize = 10;

    private $mod      = 'gengduo';



    /**

     * @return mixed

     * 楼盘列表

     */

    public function index()

    {
        $userInfo          = $this->getUserInfo();
        // print_r(11111111);
        if(empty($userInfo)){
            $userInfo['id']=0;
        }
        // print_r($userInfo['id']);
        $this->assign('userInfo',$userInfo);
        return $this->fetch();

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
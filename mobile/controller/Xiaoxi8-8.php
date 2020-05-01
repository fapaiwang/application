<?php




namespace app\mobile\controller;

use app\common\controller\MobileBase;

class Xiaoxi extends MobileBase

{

    private $pageSize = 10;

    private $mod      = 'xiaoxi';



    /**

     * @return mixed

     * 楼盘列表

     */
    public function index()

    {
        $info = cookie('userInfo');
        $info = \org\Crypt::decrypt($info);
        $user_id[]=['user_id','eq',$info['id']];
        $yjdzs = db('yjdz')->where($user_id)->order('zonge','desc')->find();

        if(!empty($yjdzs)){
            $zonges=$yjdzs['zonge'];


            $jiage[]=['qipai','elt',$zonges];
            $jiage[]=['fcstatus','elt',170];

            $lists = model('second_house')->where($jiage)->order('qipai','desc')->count();
        }
        // print_r($yjdzs['shuliang']);
        // print_r($lists);
        if($yjdzs['shuliang']<$lists){
            $xx=1;
        }else{
            $xx=0;
        }
        $this->assign('xx',$xx);


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
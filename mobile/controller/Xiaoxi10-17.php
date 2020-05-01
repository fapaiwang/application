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
        // print_r(22);
        $dlcg[]=['user_id','eq',$info['id']];
        $dlcg[]=['status','eq','登录成功'];
        $dls = model('user_log')->where($dlcg)->count();
        // print_r($dls);
        $yjdzs = db('yjdz')->where($user_id)->order('zonge','desc')->find();

        if(!empty($yjdzs)){
            $zonges=$yjdzs['zonge'];


            $jiage[]=['qipai','elt',$zonges];
            $jiage[]=['fcstatus','elt',170];

            $lists = model('second_house')->where($jiage)->order('qipai','desc')->count();

            // print_r($yjdzs['shuliang']);
            // print_r($lists);
            if($yjdzs['shuliang']<$lists){
            $xx=1;
            }else{
                $xx=0;
            }




        }else{
            $xx=0;
        }

        $user_ids[]=['user_id','eq',$info['id']];
        $user_ids[]=['model','eq','estate'];
        $gzxq = db('follow')->where($user_ids)->order('id','asc')->find();
        if(!empty($gzxq)){


            $field="distinct(house_id),id,shuliang";

            $gzxqs = model('follow')->field($field)->where($user_ids)->order('id','asc')->count();

            // print_r($gzxq['shuliang']);
            // print_r($gzxqs);
            if($gzxq['shuliang']<$gzxqs){
            $xq=1;
            }else{
                $xq=0;
            }




        }else{
            $xq=0;
        }
        $user_idss[]=['user_id','eq',$info['id']];
        $user_idss[]=['model','eq','second_house'];
        $gzfy = db('follow')->where($user_idss)->order('id','asc')->find();
        if(!empty($gzfy)){


            $fields="distinct(house_id),id,shuliang";

            $gzfys = model('follow')->field($fields)->where($user_idss)->order('id','asc')->count();

            // print_r($gzfy['shuliang']);
            // print_r($gzfys);
            if($gzfy['shuliang']<$gzfys){
            $fy=1;
            // print_r($fy);
            }else{
                $fy=0;
                // print_r(111);
            }




        }else{
            $fy=0;
        }
        





        $this->assign('dls',$dls);
        $this->assign('xx',$xx);
        $this->assign('xq',$xq);
        $this->assign('fy',$fy);


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
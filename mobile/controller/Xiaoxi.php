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
        // $yjdzs = db('yjdz')->where($user_id)->order('zonge','desc')->find();
        //通知公告
        // if(!empty($yjdzs)){
            // $zonges=$yjdzs['zonge'];


            // $jiage[]=['qipai','elt',$zonges];
            // $jiage[]=['fcstatus','eq',170];
            $jiage[]=['fcstatus','egt',172];//大于
            $jiage[]=['fcstatus','elt',174];

            $lists = model('second_house')->where($jiage)->order('qipai','desc')->count();

            // print_r($yjdzs['shuliang']);
            // print_r($lists);
            $tzggsuser_id[]=['id','eq',$info['id']];

            $tzggs = model('user')->where($tzggsuser_id)->find();

            if($tzggs['tzgg']<$lists){
                $cha=$lists-$tzggs['tzgg'];
                $this->assign('cha',$cha);
            $xx=1;
            }else{
                $xx=0;
            }




        // }else{
        //     $xx=0;
        // }
        //关注小区
         $fields="distinct(house_id),id,user_id,model,create_time,shuliang";
        $gzuser_id[]=['user_id','eq',$info['id']];
        $gzuser_id[]=['model','eq','estate'];
        $gzxqs = db('follow')->field($fields)->where($gzuser_id)->order('id','asc')->select();
       // print_r($gzxqs);
       
        foreach ($gzxqs as $key => $value) {
            // print_r($value['house_id']);
            // $user_ids[]=['estate_id','eq',$value['house_id']];
            // print_r($user_ids[]);
            $gzxq[]= db('second_house')->where(['estate_id'=>$value['house_id']])->select();
            $gzxqss[]= db('second_house')->where(['estate_id'=>$value['house_id']])->count();
            // print_r($gzxqss);
            // echo "a";
            // $gzxqss+=$gzxqss;
            
            
            
        }
// print_r($gzxq);
// print_r($gzxqss);
$aaa=0;
if(!empty($gzxqss)){
foreach ($gzxqss as $keys => $values) {
    $aaa+=$values;
}
}
// print_r($aaa);
if($aaa>0){


        $gzsuser_id[]=['id','eq',$info['id']];

            $gzs = model('user')->where($gzsuser_id)->find();

            // print_r($gzfy['shuliang']);
            // print_r($gzfys);
            if($aaa>$gzs['gzxq']){
                $chagzxqs=$aaa-$gzs['gzxq'];
               // print_r($chagzxqs);
$this->assign('chagzxqs',$chagzxqs);
            $xq=1;
            // print_r($fy);

            }else{
                $xq=0;
                // print_r(111);
            }




        }else{
            $xq=0;
        }

        // $user_ids[]=['user_id','eq',$info['id']];
        // $user_ids[]=['model','eq','estate'];
        // $gzxq = db('follow')->where($user_ids)->order('id','asc')->find();
        // if(!empty($gzxq)){


        //     $field="distinct(house_id),id,shuliang";

        //     $gzxqs = model('follow')->field($field)->where($user_ids)->order('id','asc')->count();

        //     // print_r($gzxq['shuliang']);
        //     // print_r($gzxqs);
        //     if($gzxq['shuliang']<$gzxqs){
        //     $xq=1;
        //     }else{
        //         $xq=0;
        //     }




        // }else{
            // $xq=0;
        // }
        //关注房源
        $user_idss[]=['user_id','eq',$info['id']];
        $user_idss[]=['model','eq','second_house'];
        $gzfy = db('follow')->where($user_idss)->order('id','asc')->find();
        // print_r($gzfy['shuliang']);
        if(!empty($gzfy)){


            $fields="distinct(house_id),id,shuliang";

            $gzfys = model('follow')->field($fields)->where($user_idss)->order('id','asc')->count();
            // print_r($gzfys);

            // print_r($gzfy['shuliang']);
            // print_r($gzfys);
            if($gzfy['shuliang']<$gzfys){
                $chagzfy=$gzfys-$gzfy['shuliang'];
                $this->assign('chagzfy',$chagzfy);
            $fy=1;
            // print_r($fy);
            }else{
                $fy=0;
                // print_r(111);
            }




        }else{
            $fy=0;
        }

        //人工推送
        $tuisongs[]=['tuisong','eq',1];
        $tuisongs[]=['fcstatus','eq',170];
        $ts = db('second_house')->where($tuisongs)->count();
        // print_r($ts);
        if(!empty($ts)){


        $tsuser_id[]=['id','eq',$info['id']];

            $tss = model('user')->where($tsuser_id)->find();

            // print_r($gzfy['shuliang']);
            // print_r($gzfys);
            if($ts>$tss['rgts']){
                $chatss=$ts-$tss['rgts'];
               
$this->assign('chatss',$chatss);
            $rts=1;
            // print_r($fy);

            }else{
                $rts=0;
                // print_r(111);
            }




        }else{
            $rts=0;
        }
        //推荐房源
        $tj[]=['rec_position','eq',1];
        $tjs = db('second_house')->where($tj)->count();
        // print_r($ts);
        if(!empty($tjs)){


        $tjuser_id[]=['id','eq',$info['id']];

            $tjss = model('user')->where($tjuser_id)->find();

            // print_r($gzfy['shuliang']);
            // print_r($gzfys);
            if($tjs>$tjss['tjfy']){
                $chatjs=$tjs-$tjss['tjfy'];
               // print_r($chatjs);
$this->assign('chatjs',$chatjs);
            $tjfys=1;
            // print_r($fy);

            }else{
                $tjfys=0;
                // print_r(111);
            }




        }else{
            $tjfys=0;
        }
        //捡漏房源
        


$this->assign('tjfys',$tjfys);
$this->assign('rts',$rts);
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
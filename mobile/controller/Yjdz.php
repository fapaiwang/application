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
        $aaa=input('post.zonge');
        $bbb=input('post.province');
        $ccc=input('post.city');
        $ddd=input('post.area');






        $userInfo          = $this->getUserInfo();

        $userInfo && $data['user_id'] = $userInfo['id'];
        $userInfo && $data['mobile'] = $userInfo['mobile'];

        $setting        = getSettingCache('user');
        if(empty($aaa)){
            // Echo "<font color ='red'>您输入的账号或密码错误！</font>";
echo "<script>alert('请输入您的购房预算');</script>";
return $this->fetch('yjdz/index');
        }else if($bbb=='---请选择---'){
echo "<script>alert('请选择您购房区域');</script>";
return $this->fetch('yjdz/index');

        }else if($ccc=='---请选择---'){
echo "<script>alert('请选择您购房区域');</script>";
return $this->fetch('yjdz/index');

        }else if($ddd=='---请选择---'){
echo "<script>alert('请选择您购房区域');</script>";
return $this->fetch('yjdz/index');

        }else if(db('yjdz')->insert($data)){
        



        $user_id[]=['user_id','eq',$userInfo['id']];
        $yjdzs = db('yjdz')->where($user_id)->order('zonge','desc')->find();
    
        if(!empty($yjdzs)){
            $zonges=$yjdzs['zonge'];


$jiage[]=['qipai','elt',$zonges];
$jiage[]=['fcstatus','elt',170];


        $lists = model('second_house')->where($jiage)->order('qipai','desc')->count();

        // model('yjdz')->where(['id'=>$yjdzs['id']])->update(['shuliang'=>$lists]);
        return $this->fetch('public/tj');
    }








        }else{
            // $return['msg']  = '保存失败';
            return $this->fetch('public/sb');
        }
//         print_r($aaa);
// if(empty($aaa)){
//             $return['msg'] = '原密码不正确';
//         }
//         exit();
        // $sms_code          = input('post.sms_code');//短信验证码

        // $token             = input('post.__token__');

        
        // print_r($data);
        // exit();
        
// print_r($data);
        // $return['code'] = 0;
        // if(db('yjdz')->insert($data))

        // {
        // // $return['msg']  = '提交成功';
        // return $this->fetch('public/tj');
        // }else{
        //     // $return['msg']  = '保存失败';
        //     return $this->fetch('public/sb');
        // }

        

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
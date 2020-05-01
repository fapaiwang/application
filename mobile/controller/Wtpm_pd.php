<?php




namespace app\mobile\controller;

use app\common\controller\MobileBase;

class Wtpm extends MobileBase

{

    private $pageSize = 10;

    private $mod      = 'wtpt';



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

        $data['user_name'] = input('post.user_name');
        $data['xq'] = input('post.xq');
        $data['mj'] = input('post.mj');
        $data['price'] = input('post.price');
        $data['address'] = input('post.address');
        $data['mobile']    = input('post.mobile');
        $aaa=input('post.user_name');
        $bbb=input('post.xq');
        $ccc=input('post.mj');
        $ddd=input('post.price');
        $eee=input('post.address');
        $fff=input('post.mobile');

        $data['create_time']    = time();

        $sms_code          = input('post.sms_code');//短信验证码

        $token             = input('post.__token__');

        $userInfo          = $this->getUserInfo();

        $userInfo && $data['user_id'] = $userInfo['id'];

        $setting        = getSettingCache('user');
        // print_r($data);
        // exit();

        // $return['code'] = 0;
        if(empty($aaa)){
            echo "<script>alert('请输入您的昵称');</script>";
            return $this->fetch('wtpm/index');
        }else if(empty($bbb)){
            echo "<script>alert('请输入委托房源所在小区');</script>";
            return $this->fetch('wtpm/index');
        }else if(empty($ccc)){
            echo "<script>alert('请输入面积');</script>";
            return $this->fetch('wtpm/index');
        }else if(empty($ddd)){
            echo "<script>alert('请输入售价');</script>";
            return $this->fetch('wtpm/index');
        }else if(empty($eee)){
            echo "<script>alert('请输入您的详细地址');</script>";
            return $this->fetch('wtpm/index');
        }else if(empty($fff)){
            echo "<script>alert('请输入您的联系方式');</script>";
            return $this->fetch('wtpm/index');
        }else if(db('wtpm')->insert($data))

        {
        // $return['msg']  = '提交成功';
        return $this->fetch('public/tj');
        }else{
            // $return['msg']  = '保存失败';
            return $this->fetch('public/sb');
        }

        // if(request()->isAjax())

        // {

        //     if($data['check_sms'] == 'yes' && $sms_code != cache($data['mobile']))

        //     {

        //         $return['msg'] = '短信验证码不正确！';

        //     }elseif($token && session('__token__')!== $token){

        //         $return['msg'] = '操作失败';

        //     }else{

        //         if(model('wtpm')->allowField(true)->save($data))

        //         {

                    

        //             session('__token__',null);

        //             action('home/Sms/sendNoticeSms',['data'=>$data]);

        //             $return['code'] = 1;

        //             $return['msg']  = '提交成功';

        //         }else{

        //             $return['msg']  = '保存失败';

        //         }

        //     }

        // }else{

        //     $return['msg']  = '提交成功';

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
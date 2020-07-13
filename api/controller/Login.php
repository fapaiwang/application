<?php

namespace app\api\controller;


use app\common\controller\ApiBase;
use app\tools\ApiResult;
use think\Log;

class Login extends ApiBase
{
    use ApiResult;
    /**
     * @return \think\response\Json
     * 用户登录
     */
    public function index()
    {
        $account  = input('post.user_name');
        $password = input('post.password');
        $return['code'] = 0;
        if(!$account)
        {
            $return['msg'] = '请填写用户名';
        }else if(!$password){
            $return['msg'] = '请填写密码';
        }else{
            $where['user_name|mobile'] = $account;
            //$where['password']         = passwordEncode($password);
            $obj   = model('user');
            $field = 'id,model,password,status,user_name,mobile,nick_name';
            $info  = $obj->where($where)->field($field)->find();
            if(!$info)
            {
                $return['msg'] = '用户不存在';
            }else if($info['password'] != passwordEncode($password)){
                $return['msg'] = '密码不正确';
            }else if($info['status'] == 0){
                $return['msg'] = '账号已禁用';
            }else{
                $edit['login_time'] = time();
                $edit['login_num']  = \think\Db::raw('login_num+1');
                $obj->save($edit,['id'=>$info['id']]);
                $token = sha1($info['user_name'].$info['mobile'].$info['id'].codestr(10));
                $data = [
                    'id'        => $info['id'],
                    'model'     => $info->getData('model'),
                    'user_name' => $info['user_name'],
                    'mobile'    => $info['mobile'],
                    'nick_name' => $info['nick_name'],
                    'token'     => $token,
                    'avatar'    => $this->getImgUrl(getAvatar($info['id'],90))
                ];
                cache($token,$data);
                $return['code'] = 200;
                $return['data'] = $data;
            }
        }
        return json($return);
    }

    /**
     * 用户注册
     */
    public function register()
    {
        $mobile      = input('post.mobile');
        $sms_code    = input('post.code');
        $password    = input('post.password');
        $user        = getSettingCache('user');
        $return['code'] = 0;
        if($user['open_reg'] == 0)
        {
            $return['msg'] = '注册功能已关闭！';
        }elseif(!is_mobile($mobile))
        {
            $return['msg'] = '手机号格式错误！';
        }elseif($user['reg_sms'] == 1 && (empty($sms_code) || cache($mobile)!=$sms_code)){
            $return['msg'] = '短信验证码错误！';
        }elseif(strlen($password)<6){
            $return['msg'] = '密码至少6位！';
        }elseif(checkMobileIsExists($mobile)){
            $return['msg'] = '手机号已注册！';
        }else{
            $data['user_name'] = $data['nick_name'] = $mobile;
            $data['password']  = $password;
            $data['mobile']    = $mobile;
            $data['login_time'] = time();
            $data['model']      = 1;
            \app\common\model\User::event('after_insert',function($obj){
                $info_data = [];
                $obj->userInfo()->save($info_data);
            });
            if(model('user')->allowField(true)->save($data))
            {
                cache($mobile,null);
                $return['code']   = 200;
                $return['msg']    = '注册成功！';
            }else{
                $return['msg']    = '注册失败';
            }
        }
        return json($return);
    }

    /**
     * @return \think\response\Json
     * 获取用户配置
     */
    public function getSetting()
    {
        $send_sms = getSettingCache('user','reg_sms');
        $send_sms = is_numeric($send_sms) ? $send_sms : 0;
        $return['code'] = 200;
        $return['data'] = $send_sms;
        return json($return);
    }
    /**
     * 退出登录
     */
    public function logout()
    {
        $token = request()->header('token');
        cache($token,null);
        $return['code'] = 200;
        return json($return);
    }

    /**
     * 获取用户信息
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function login_user(){
        return $this->success_o(login_user());
    }

    /**
     * 验证码注册
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function registerDo(){
        $mobile      = input('mobile');
        $sms_code    = input('sms_code');
//        if (empty($sms_code)){
//            return $this->error_o("短信验证码不能为空！");
//        }
        $token       = input('token');
        $user        = getSettingCache('user');
        $return['code'] = 0;
        if($user['open_reg'] == 0){
            return  $this->error_o("网站关闭注册功能！");
        }elseif(!is_mobile($mobile)) {
            return $this->error_o("手机号码格式不正确！");
        }elseif($user['reg_sms'] == 1 && cache($mobile)!=$sms_code){
            return $this->error_o("短信验证码不正确！");
        }
//        elseif(session('__token__')!== $token){
////todo  token
//            $return['msg'] = '操作失败';
//        }
        elseif(checkMobileIsExists($mobile)){
            return $this->error_o("该手机号码已被注册！");
        }else{
            $data['user_name'] = $data['nick_name'] = $mobile;
            $data['mobile']    = $mobile;
            $data['login_time'] = time();
            $data['model']      = 1;
            \app\common\model\User::event('after_insert',function($obj){
                $info_data = [];
                $obj->userInfo()->save($info_data);
            });
            if(model('user')->allowField(true)->save($data)) {

                cache($mobile,null);

                // unset($data['password']);

                $data['id']  = model('user')->id;

                $info = \org\Crypt::encrypt(json_encode($data));

                cookie('userInfo',$info);
//todo 清除token
//                session('__token__',null);//清除token
               return $this->success_o("恭喜您！注册成功！");
            }else{
                return $this->error_o("注册失败");
            }
        }

    }

}
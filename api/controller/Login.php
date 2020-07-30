<?php

namespace app\api\controller;


use app\common\controller\ApiBase;
use app\tools\ApiResult;
use think\Db;
use think\facade\Request;
use think\Log;

class Login extends ApiBase
{
    use ApiResult;
    /**
     * @return \think\response\Json
     * 用户登录
     */
    public function index(){
        $account  = input('post.user_name');
        $password = input('post.password');
        $return['code'] = 0;
        if(!$account){
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
    public function register(){
        try{
            Db::startTrans();
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
                if(model('user')->allowField(true)->save($data)) {
                    cache($mobile,null);
                    $return['code']   = 200;
                    $return['msg']    = '注册成功！';
                }else{
                    $return['msg']    = '注册失败';
                }
            }
            Db::commit();
            return json($return);
        }catch(\Exception $e){
            Db::rollback();
            Log::write('用户注册'.$e->getMessage(),'error');
            return json($return['msg']    = '注册失败');
        }


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
    public function logoutUser(){
        $id =input("param.user_id");
        cookie('userInfo',null);
        cache("user_info_".$id,null);
        return $this->success_o("退出成功");
    }
    /**
     * 获取用户信息
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function loginUser(){
        $id =input("param.user_id");
        if (empty($id)){
            return $this->success_o("用户id不能为空");
        }
        $info = loginUser($id);
        if (empty(loginUser($id))){
            return $this->error_o("当前用户未登录");
        }
        return $this->success_o($info);
    }

    /**
     * 验证码/密码 注册
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function registerDo(){
        $mobile      = input('mobile');
        $sms_code    = input('sms_code');
        $password    = input('password');
        if (empty($sms_code)){
            return $this->error_o("短信验证码不能为空！");
        }
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
            $data['password'] = $password;
            $data['model']      = 1;
//            return $this->success_o($data);
            \app\common\model\User::event('after_insert',function($obj){
                $info_data = [];
                $obj->userInfo()->save($info_data);
            });
            if(model('user')->allowField(true)->save($data)) {

                cache($mobile,null);

                // unset($data['password']);

                $data['id']  = model('user')->id;
                $data['img']      = getAvatar($data['id'],90,90);
                $user_info = $data;
                $info = \org\Crypt::encrypt(json_encode($data));
                cache("user_info_".$data['id'],$info,2592000);
                cookie('userInfo',$info);
//todo 清除token
//                session('__token__',null);//清除token
               return $this->success_o($user_info,"恭喜您！注册成功！");
            }else{
                return $this->error_o("注册失败");
            }
        }

    }

    /**
     * 手机端登录(验证码)
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function loginDo(){
        $user_name   = input('mobile');
        $sms_code    = input('sms_code');
        $user        = getSettingCache('user');
        $return['code'] = 0;
        if(!$user_name){
            return $this->error_o("请填写登录名！");
        } elseif($user['reg_sms'] == 1 && cache($user_name)!=$sms_code){
            return $this->error_o("短信验证码不正确！");
        } else{
            $where['mobile'] = $user_name;
            $where['status']           = 1;
            $info = model("user")->where($where)->field('id,model,user_name,mobile,nick_name')->find();
            if($info) {
                $user_id  = $info['id'];
                $edit['login_time'] = time();
                $edit['login_ip']   = request()->ip();
                $edit['login_num']  = \think\Db::raw('login_num+1');

                model("user")->save($edit,['id'=>$user_id]);
                $model = $info->getData('model');
                $info  = $info->toArray();
                $info['model'] = $model;
                $info['img'] = getAvatar($user_id,90,90);
                $data  = $info;
                $info = \org\Crypt::encrypt(json_encode($info));
                cookie('userInfo',$info);
                cache("user_info_".$user_id,$info,2592000);
                // session('__token__',null);//清除token
                return $this->success_o($data);
            }else{
                return   $this->error_o("用户不存在,请先注册！");
            }
        }
    }

    /**
     * 密码登录
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function loginPwd(){
        $user_name   = input('mobile');
        $password    = input('password');
        if(!$user_name) {
            return $this->error_o("请填写登录名！");
        }else{
            $where['mobile'] = $user_name;
            $where['password']         = passwordEncode($password);
            $where['status']           = 1;
            $uinfo = model('user')->field('password')->where('user_name',$user_name)->find();
            if(empty($uinfo)){
                return $this->error_o("账号为快捷登录！");
            }
            $info = model('user')->where($where)->field('id,model,user_name,mobile,nick_name')->find();
            if($info) {
                $user_id  = $info['id'];
                $edit['login_time'] = time();
                $edit['login_ip']   = request()->ip();
                $edit['login_num']  = \think\Db::raw('login_num+1');
                model('user')->save($edit,['id'=>$user_id]);
                $model = $info->getData('model');
                $info  = $info->toArray();
                $info['model'] = $model;
                $info['img'] = getAvatar($user_id,90,90);
                $data  = $info;
                $info = \org\Crypt::encrypt(json_encode($info));
                cookie('userInfo',$info);
                cache("user_info_".$user_id,$info,2592000);
//                session('__token__',null);//清除token
                return $this->success_o($data);
            }else{
                return $this->error_o("用户不存在或账号密码不正确！");
            }
        }
    }

    /**
     * 重置密码
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function resetPassword(){
        $mobile   = input('mobile');
        $sms_code = input('sms_code');
        $password = input('password');
        $password2 = input('password2');
//        $token     = input('post.token');
        if(!is_mobile($mobile)) {
            return $this->error_o('手机号码格式不正确！');
        }elseif(!checkMobileIsExists($mobile)){
            return $this->error_o('用户不存在！请确认手机号码输入是否正确！');
        }elseif(cache($mobile)!=$sms_code){
            return $this->error_o('短信验证码不正确！');
        }elseif(strlen($password)<6){
            return $this->error_o('密码至少由6位数字或字母组成！');
        }elseif($password!=$password2){
            return $this->error_o('新两次密码输入不一致！！');
        }else{
            $where['mobile']  = $mobile;
            $data['password'] = $password;
            if(model('user')->save($data,$where)) {
                session('__token__',null);
                cache($mobile,null);
                $info = model("user")->where($where)->field('id,model,user_name,mobile,nick_name')->find()->toArray();
                $info['img'] = getAvatar($info["id"],90,90);
                $user_id = $info['id'];
//                $info = \org\Crypt::encrypt(json_encode($info));
                cache("user_info_".$user_id,null);
                return $this->success_o("密码重置成功，请您重新登录");
            }else{
                return $this->error_o('新密码与原密码相同，重置失败！');
            }
        }
    }

    /**
     * 用户协议
     * @param mixed
     * @return mixed|\think\response\Json
     * @author: al
     */
    public function agreement(){
        $agreement   = model('pages')->where('id',3)->cache('pages_3',84000)->find();

        return $agreement;
    }
    public function userInfoSave(){
        $nick_name   = input('param.nick_name');
        $email       = input('param.email');
        $id = input('param.id');
        $where['id'] = $id;
        if (empty($id)){
            return $this->error_o('用户id不能为空');
        }
        $data['nick_name'] = $nick_name;
        $data['email']     = $email;
        if(model('user')->save($data,$where)){
            return $this->success_o($data);
        }else{
            return $this->error_o('请修改后再提交');
        }
    }

}
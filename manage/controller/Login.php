<?php

namespace app\manage\controller;
use think\Controller;
use think\captcha\Captcha;
class Login extends Controller
{
    public function index()
    {
        $w = ceil(date('j') / 7);
        $this->assign('w',$w);
        return $this->fetch();
    }
    public function loginDo(){
        $username = input('post.username');
        $password = input('post.password');
//        $verify_code = input('post.verify_code');
//        if(!captcha_check($verify_code)){
//            $this->error('验证码不正确');
//        }
        $obj = model('admin');
        $admin = $obj->field('id,username,password,role_id')->where(['username'=>$username, 'status'=>1])->find();
        if (!$admin) {
            $this->error('管理员不存在');
        }
        if ($admin['password'] != passwordEncode($password)) {
            $this->error('密码错误');
        }
        $obj->save(['last_time'=>time(), 'last_ip'=>request()->ip(),'login_num'=>\think\Db::raw('login_num+1')],['id'=>$admin['id']]);
        session('adminInfo',\org\Crypt::encrypt(['username'=>$admin['username'],'role'=>$admin['role_id'],'id'=>$admin['id']]));
        \org\Role::getmanagemenu(['id'=>$admin['id'],'roleid'=>$admin['role_id']]);
        $this->redirect('Index/index');
    }
    public function verfiy()
    {
        $captcha = new Captcha(config('captcha.'));
        return $captcha->entry();
    }
    public function logout(){
        session('adminInfo',null);
        $this->redirect('Login/index');
    }
}
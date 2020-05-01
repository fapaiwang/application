<?php

namespace app\agent\controller;
use think\Controller;
use think\captcha\Captcha;
class Login extends Controller
{
    public function index()
    {
        return $this->fetch();
    }
    public function loginDo(){
        $username = input('post.username');
        $password = input('post.password');
        $verify_code = input('post.verify_code');

        if(!captcha_check($verify_code)){
            $this->error('验证码不正确');
        }
        $obj = model('agent');
        $admin = $obj->field('id,user_name,password,surper_manager,company_id')->where(['user_name|mobile'=>$username, 'status'=>1])->find();
        if (!$admin) {
            $this->error('用户不存在或账号被禁用');
        }
        if ($admin['password'] != passwordEncode($password)) {
            $this->error('密码错误');
        }
        $obj->save(['login_time'=>time(), 'login_ip'=>request()->ip(),'login_num'=>\think\Db::raw('login_num+1')],['id'=>$admin['id']]);
        unset($admin['password']);
        $role_id = model('agent_company')->where('id',$admin['company_id'])->value('cate_id');//代理商级别 不同的级别所有的权限不一样
        $admin['role_id'] = $role_id;
        session('agentInfo',\org\Crypt::encrypt($admin->toArray()));
        \org\AgentRole::getmanagemenu();
        $this->redirect('Index/index');
    }
    public function verfiy()
    {
        $captcha = new Captcha(config('captcha.'));
        return $captcha->entry();
    }
    public function logout(){
        session('agentInfo',null);
        $this->redirect('Login/index');
    }
}
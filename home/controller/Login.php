<?php


namespace app\home\controller;
use app\common\controller\HomeBase;
use app\home\service\IndexServer;
use think\Db;
use think\facade\Log;

class Login extends HomeBase
{
    private $mod;
    public function initialize(){
        parent::initialize();
        $this->mod = model('user');
    }

    /**
     * @return mixed
     * 登录页
     */
    public function index()
    {
        $forward = request()->server('HTTP_REFERER');
        if(!$forward || strpos($forward,'login')!==FALSE)
        {
            $forward = url('user.index/index');
        }
        $seo['title'] = '用户登录';
        $this->assign('forward',base64_encode($forward));
        return $this->fetch();
    }

    /**
     * @return mixed
     * 注册页
     */
    public function register()
    {
        $user        = getSettingCache('user');
        $agreement   = model('pages')->where('id',3)->find();
        $seo['title'] = '用户注册';
        $this->setSeo($seo);
        $this->assign('user_cate',getUserCate());//用户分类
        $this->assign('user_setting',$user);
        $this->assign('agreement',$agreement);
        return $this->fetch();
    }

    /**
     * @return \think\response\Json
     * 注册数据处理
     */
    public function registerDo()
    {
        $mobile      = input('post.mobile');
        $sms_code    = input('post.sms_code');
        $password    = input('post.password');
        $password2   = input('post.password2');
        $verify_code = input('post.verfiy_code');
        $model       = input('post.model/d',1);
        $token       = input('post.token');
        $user        = getSettingCache('user');
        $return['code'] = 0;
        if($user['open_reg'] == 0)
        {
            $return['msg'] = '网站关闭注册功能！';
        }elseif(!is_mobile($mobile))
        {
            $return['msg'] = '手机号码格式不正确！';
        }elseif($user['reg_sms'] == 1 && cache($mobile)!=$sms_code){
            $return['msg'] = '短信验证码不正确！';
        }elseif(strlen($password)<6){
            $return['msg'] = '密码由6位数字或字符组成！';
        }elseif($password!=$password2){
            $return['msg'] = '两次密码输入不一致！';
        }elseif(!captcha_check($verify_code)){
            $return['msg'] = '验证码不正确';
        }elseif(checkMobileIsExists($mobile)){
            $return['msg'] = '该手机号码已被注册！';
        }else{
            $data['user_name'] = $data['nick_name'] = $mobile;
            $data['password']  = $password;
            $data['mobile']    = $mobile;
            $data['login_time'] = time();
            $data['model']      = $model;
            \app\common\model\User::event('after_insert',function($obj){
                $info_data = [];
                $obj->userInfo()->save($info_data);
            });
            if($this->mod->allowField(true)->save($data))
            {
                cache($mobile,null);
                unset($data['password']);
                $data['id']  = $this->mod->id;
                $info = \org\Crypt::encrypt(json_encode($data));
                cookie('userInfo',$info);
                session('__token__',null);//清除token
                $return['code']   = 1;
                $return['msg']    = '恭喜您！注册成功！';
                $return['uri']    = url('user.index/index');
            }else{
                $return['msg']    = '注册失败';
            }
        }
        return json($return);
    }
    /**
     * @return \think\response\Json
     * 登录操作
     */
    public function loginDo()
    {
        $user_name   = input('post.user_name');
        $password    = input('post.password');
        $remember    = input('post.remember/d',0);
        $forward     = input('post.forward');
        $token       = input('post.__token__');
        Log::write($user_name);
        Log::write($password);
        Log::write($remember);
        Log::write($forward);
        Log::write($token);
        $exipre      = 0;
        $return['code'] = 0;
        if(!$user_name)
        {
            $return['msg'] = '请填写登录名！';
        }else{
            $where['user_name|mobile'] = $user_name;
            $where['password']         = passwordEncode($password);
            $where['status']           = 1;
            $info = $this->mod->where($where)->field('id,model,user_name,mobile,nick_name')->find();
            if($info)
            {
                if($remember)
                {
                    $exipre = 7*86400;//默认记住一周
                }
                $edit['login_time'] = time();
                $edit['login_ip']   = request()->ip();
                $edit['login_num']  = \think\Db::raw('login_num+1');
                $this->mod->save($edit,['id'=>$info['id']]);
                $model = $info->getData('model');
                $info  = $info->toArray();
                $log['user_id'] = $info['id'];
                $info['model'] = $model;
                $info = \org\Crypt::encrypt(json_encode($info));
                cookie('userInfo',$info,$exipre);
                session('__token__',null);//清除token

                $log['status']  = '登录成功';
                $return['code'] = 1;
                $return['msg']  = '登录成功！';
                $return['uri']  = $model == 2 ? url('user.index/index') : base64_decode($forward);
            }else{
                $log['status']  = '登录失败';
                $return['msg']  = '用户不存在或账号密码不正确！';
            }
            $log['user_name'] = $user_name;
            \app\common\service\Account::log($log);
        }
        return json($return);
    }

    /**
     * @return mixed
     * 找回密码
     */
   public function forgetPassword()
   {
       $seo['title'] = '找回密码';
       $this->setSeo($seo);
       return $this->fetch();
   }

    /**
     * @return \think\response\Json
     * 密码重置操作
     */
    public function resetPassword()
    {
        $mobile   = input('post.mobile');
        $sms_code = input('post.sms_code');
        $password = input('post.password');
        $password2 = input('post.password2');
        $token     = input('post.token');
        $return['code'] = 0;
        if(!is_mobile($mobile))
        {
            $return['msg'] = '手机号码格式不正确！';
        }elseif(!checkMobileIsExists($mobile)){
            $return['msg'] = '用户不存在！请确认手机号码输入是否正确！';
        }elseif(cache($mobile)!=$sms_code){
            $return['msg'] = '短信验证码不正确！';
        }elseif(strlen($password)<6){
            $return['msg'] = '密码至少由6位数字或字母组成！';
        }elseif($password!=$password2){
            $return['msg'] = '两次密码输入不一致！';
        }elseif(session('__token__')!=$token){
            $return['msg'] = '表单数据验证失败';
        }else{
            $where['mobile']  = $mobile;
            $data['password'] = $password;
            if(model('user')->save($data,$where))
            {
                session('__token__',null);
                cache($mobile,null);
                $return['code'] = 1;
                $return['msg']  = '密码重置成功，即将为您跳转到登录页';
                $return['uri']  = url('Login/index');
            }else{
                $return['msg']  = '新密码与原密码相同，重置失败！';
            }
        }
        return json($return);
    }
    /**
     * 退出登录
     */
    public function logout()
    {
        cookie('userInfo',null);
        $http_referer = request()->server('HTTP_REFERER');
        if(strpos($http_referer,'user')!==FALSE || strpos($http_referer,'brokers')!==FALSE)
        {
            $this->redirect('Index/index');
        }
        $this->redirect($http_referer);
    }

    /**
     *  用户协议
     * @param mixed
     * @return mixed
     * @author: al
     */
    public function agreement(){
        $agreement   = model('pages')->where('id',3)->cache('pages_3',84000)->find();
        $this->assign('agreement',$agreement);
        return $this->fetch();
    }
    /**
     * 每周公司分享
     * @param mixed
     * @return mixed|string
     * @author: al
     */
    public function company_week_recommended(){
        $IndexServer = new IndexServer();
        //时间
        $time["year"] = date("Y");
        $time['s_time']=date('m/d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600));
        $time['e_time']=date('m/d', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));

        //查询数据
        $recommended =model("a_zhoutuijian")->select();
        $res = Db::query('select max(pid) as num from fang_a_zhoutuijian');
        if (empty($res[0]["num"])){
            return "统计房源城市pid错误";
        }
        //颜色
        $color["background"] ="#b89686";
        $color["city"] ="#EDCBBB";
        $color["one_line"] ="#F2E2DA";
        $color["double_row"] ="#FFFFFF";
        //背景图
        $back_img = $IndexServer->get_home_banner_x(28,1);
//dd($back_img->setting["fileurl"]);
        $arr = array_range(1, $res[0]["num"], 1);
        foreach ($arr as $key=>$val){
            $num =0;
            $last_num = 0;
            foreach ($recommended as $k=>$v){
                if (!empty($v->city)){
                    $last_num = $k;
                }
                if ($val == $v->pid){
                    $num++;
                    $recommended[$k]["num"] =$num;
                    $recommended[$last_num]["num"] =$num;
                }
            }
        }
        $this->assign('time',$time);
        $this->assign('color',$color);
        $this->assign('arr',$arr);
        $this->assign('site',getSettingCache('site'));
        $this->assign('back_img',$back_img);
        $this->assign('recommended',$recommended);
        return $this->fetch();
    }

}
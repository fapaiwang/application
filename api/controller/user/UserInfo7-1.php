<?php
namespace app\api\controller\user;
use \think\Request;
class UserInfo extends UserBase
{
    /**
     * @return \think\response\Json
     * 用户信息
     */
    public function index()
    {
        $where['id'] = $this->userInfo['id'];
        $return['code'] = 0;
        $field = "id,user_name,mobile,email,nick_name,model,mobile";
        $info = model('user')->where($where)->field($field)->find();
        if($info)
        {
            $data['model']     = $info->getData('model');
            $data['user_name'] = $info['user_name'];
            $data['nick_name'] = $info['nick_name'];
            $data['avatar'] = $this->getImgUrl(getAvatar($info['id'],90)).'?t='.time();
            $data['mobile'] = hideMobile($info['mobile']);
            $data['email']  = $info['email'];
            $return['code'] = 200;
            $return['data'] = $data;
        }
        return json($return);
    }

    /**
     * @return \think\response\Json
     * 修改密码
     */
    public function updatePassword()
    {
        $password            = input('post.password');
        $new_password        = input('post.new_password');
        $confirm_password    = input('post.confirm_password');
        $where['id']    = $this->userInfo['id'];
        $return['code'] = 0;
        $obj = model('user');
        $info = $obj::get($where);
        if($info['password']!=passwordEncode($password))
        {
            $return['msg'] = '原密码不正确';
        }else if(!$new_password){
            $return['msg'] = '请输入新密码';
        }else if($new_password!=$confirm_password){
            $return['msg'] = '两次密码输入不一致';
        }else{
            $info->password = $new_password;
            if($info->save())
            {
                $return['code'] = 200;
                $return['msg']  = '密码修改成功';
            }else{
                $return['msg']  = '修改失败';
            }
        }
        return json($return);
    }
    /**
     * @param Request $request
     * @return \think\response\Json
     * 修改资料
     */
    public function save(Request $request)
    {
        $data['nick_name'] = $request->nick_name;
        $data['email']     = $request->email;
        $return['code']    = 0;
        if(model('user')->save($data,['id'=>$this->userInfo['id']]))
        {
            $return['code'] = 200;
            $return['msg']  = '修改成功';
        }else{
            $return['msg']  = '修改失败';
        }
        return json($return);
    }
}
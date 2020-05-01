<?php


namespace app\home\controller\user;
use app\common\controller\UserBase;
class Account extends UserBase
{
    /**
     * @return mixed
     * 编辑资料
     */
    public function index()
    {
        $field = 'id,nick_name,mobile,email,model';
        $info = $this->getUserInfo($this->userInfo['id'],$field);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * @return \think\response\Json
     * 编辑
     */
    public function edit()
    {
        $where['id'] = $this->userInfo['id'];
        $nick_name   = input('post.nick_name');
        $email       = input('post.email');
        $info_data   = input('post.data');
        $data['nick_name'] = $nick_name;
        $data['email']     = $email;
        $return['code']    = 0;
        if(model('user')->save($data,$where))
        {
            model('user_info')->save($info_data,['user_id'=>$this->userInfo['id']]);
            $return['code'] = 1;
            $return['msg']  = '修改成功';
        }else{
            $return['msg']  = '请修改后再提交';
        }
        return json($return);
    }

    /**
     * @return mixed
     * 用户头像
     */
    public function avatar()
    {
        $avatar = getAvatar($this->userInfo['id']);
        $url = base64_encode(url('user.account/uploadAvatar'));
        if($avatar && is_array($avatar)){
            ksort($avatar);
        }
        $this->assign('avatar',$avatar);
        $this->assign('upload_url',$url);
        return $this->fetch();
    }

    /**
     * @return mixed
     * 修改密码
     */
    public function password()
    {
        return $this->fetch();
    }

    /**
     *修改密码
     */
    public function editPassword()
    {
        $old_password = input('post.old_password');
        $password     = input('post.password');
        $password2    = input('post.password2');
        $where['id']  = $this->userInfo['id'];
        $return['code'] = 0;
        $obj = model('user');
        $info = $obj::get($where);
        if($info['password']!=passwordEncode($old_password))
        {
            $return['msg'] = '原密码不正确';
        }else if(!$password){
            $return['msg'] = '请输入新密码';
        }else if($password!=$password2){
            $return['msg'] = '两次密码输入不一致';
        }else{
            $info->password = $password;
            if( $info->save())
            {
                $return['code'] = 1;
                $return['msg']  = '密码修改成功';
            }else{
                $return['msg']  = '修改失败，新密码与原密码相同';
            }
        }
        return json($return);
    }
    /**
     * 处理flash上传的图片保存至本地
     */
    public function uploadAvatar(){
        $avatar = new \org\Avatar();
        $avatar->data['uid'] = $this->userInfo['id'];
        $avatar->upload();
    }
}
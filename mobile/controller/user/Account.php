<?php


namespace app\mobile\controller\user;
class Account extends UserBase
{
    /**
     * @return mixed
     * 编辑资料
     */
    public function profile()
    {
        $field = 'id,nick_name,mobile,email';
        $info = $this->getUserInfo($this->userInfo['id'],$field);
        $this->assign('info',$info);
        $this->assign('title','编辑资料');
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
        $data['nick_name'] = $nick_name;
        $data['email']     = $email;
        $return['code']    = 0;
        if(model('user')->save($data,$where))
        {
            $return['code'] = 1;
            $return['msg']  = '修改成功';
        }else{
            $return['msg']  = '请修改后再提交';
        }
        return json($return);
    }
    /**
     * @return mixed
     * 修改密码
     */
    public function password()
    {
        $this->assign('title','修改密码');
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
            if($info->save())
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
     * @return \think\response\Json
     * 异步上传图片
     */
    public function ajaxUploadImg()
    {
        $img = $this->uploadsImg();
        if ($img) {
            return json(['code'=>1, 'msg'=>'上传成功', 'data'=>$img]);
        } else {
            return json(['code'=>0, 'msg'=>'请选择图片']);
        }
    }
    /**
     * @return string
     * 图片上传
     */
    private function uploadsImg()
    {
        $img = '';
        if (isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
            $file = request()->file('file');
            $dir  = config('uploads_path').'avatar/';
            // 移动到框架应用根目录/public/uploads/ 目录下
            $path = $dir.$this->userInfo['id'].'/';

            $info = $file->validate(config('upload_img_rule'))->move(env('root_path') . $path,'avatar');
            if ($info) {
                $img = './uploads/' . 'avatar/'.$this->userInfo['id']. '/' . str_replace('\\', '/', $info->getSaveName());
                $path = env('root_path').$path;
                $smallpath = $path.'30x30.jpg';
                $smallpath_45 = $path.'45x45.jpg';
                $smallpath_90 = $path.'90x90.jpg';
                $smallpath_180 = $path.'180x180.jpg';
                \think\Image::open($img)->thumb(30,30,\think\Image::THUMB_CENTER)->save($smallpath);
                \think\Image::open($img)->thumb(45,45,\think\Image::THUMB_CENTER)->save($smallpath_45);
                \think\Image::open($img)->thumb(90,90,\think\Image::THUMB_CENTER)->save($smallpath_90);
                \think\Image::open($img)->thumb(180,180,\think\Image::THUMB_CENTER)->save($smallpath_180);
            } else {
                // 上传失败获取错误信息
                $this->error($file->getError());
            }
        }
        return trim($img,'.');
    }
}
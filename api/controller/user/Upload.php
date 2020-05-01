<?php

namespace app\api\controller\user;


class Upload extends UserBase
{
    /**
     * @return \think\response\Json
     * 图片上传
     */
    public function index()
    {
        $return['code'] = 0;
        if($this->uploadImg())
        {
            $return['code'] = 200;
            $return['avatar'] = $this->getImgUrl(getAvatar($this->userInfo['id'],90));
        }
        return json($return);
    }
    protected function uploadImg(){
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
                return true;
            } else {
                // 上传失败获取错误信息
                return false;
            }
        }
        return false;
    }
}
<?php

namespace app\api\controller;


use app\api\controller\user\UserBase;

class Upload extends UserBase
{
    /**
     * @return \think\response\Json
     * 图片上传
     */
    public function index()
    {
        $return['code'] = 0;
        $dir = 'user/'.$this->userInfo['id'];
        $img = $this->uploadImg($dir);
        if(is_string($img))
        {
            $return['code'] = 200;
            $return['data'] = ['url'=>$img,'title'=>''];
        }else{
            $return['msg'] = $img['msg'];
        }
        return json($return);
    }
    protected function uploadImg($dir = 'secondhouse'){
        $img = '';
        if (isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
            try{
                $file = request()->file('file');
                $upload = new \org\Storage();
                $upload->thumbUploadFile($file,$dir);
                $img = $upload->getFullName();
            }catch(\Exception $e){
                return ['code'=>0,'msg'=>$e->getMessage()];
            }
        }
        return $img;
    }
}
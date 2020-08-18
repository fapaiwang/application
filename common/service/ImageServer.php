<?php
namespace app\common\service;
// 引入鉴权类
use Qiniu\Auth;
// 引入上传类
use Qiniu\Storage\UploadManager;

class ImageServer
{
    public function ImageWater($pathname,$source,$locate=10){
        $image = \think\Image::open($pathname);
        if($locate==10){
            $image->tilewater($source,100)->save($pathname);
        }else{
            $image->water($source,$locate,100)->save($pathname);
        }
        return true;
    }
    function QiniuImage($key){
        include_once'../vendor/qiniu/php-sdk/autoload.php';
        // 用于签名的公钥和私钥
        $accessKey = config('third_party.accessKey');
        $secretKey = config('third_party.secretKey');
        $bucket = "fangpaiwang";
        // 初始化签权对象
        $auth = new Auth($accessKey, $secretKey);
        // 生成上传 Token
        $token = $auth->uploadToken($bucket);
        // 要上传文件的本地路径
        $filePath = '../public'.$key;
        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        if($key[0]=='/'){
            $key = substr($key, 1);
        }
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        if ($err !== null) {
            return $err;
        } else {
            if(file_exists($key))
            {
                unlink($key);
            }
            return $ret;
        }
    }
}
<?php
namespace app\tools;


trait ApiResult
{
    private function respond($data)
    {
        return json($data);
    }
    public function error_o($msg,$code=Constant::ERROR){
        return $this->respond(['code'=>$code,'msg'=>$msg]);
    }
    public function success_o($data,$msg="操作成功",$code=Constant::OK){
        return $this->respond(['code'=>$code,'msg'=>$msg,'data'=>$data]);
    }
    public function aa(){
        return "hello";
    }
    public function requestUserId(){
        $user_id = input("param.user_id/d");
        if (empty($user_id)){
            return $this->error_o("请登录后再操作");
        }
        if (empty(loginUser($user_id))){
            return $this->error_o("请登录后再操作");
        }
    }
}
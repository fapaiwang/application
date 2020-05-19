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

}
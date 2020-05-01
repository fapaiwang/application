<?php

namespace app\mobile\controller\user;
class UserBase extends \think\Controller
{
    protected $userInfo;
    public function initialize()
    {
        parent::initialize();
        if(!$this->checkUserLogin())
        {
            $this->redirect(url('Login/index'));
        }
        $controller = request()->controller();
        $action     = request()->action();
        $this->assign('site',getSettingCache('site'));
        $this->assign('controller',strtolower($controller));
        $this->assign('action',$action);
    }
    private function checkUserLogin(){
        $info = cookie('userInfo');
        $info = \org\Crypt::decrypt($info);
        $this->userInfo = $info;
        $this->assign('userInfo',$info);
        return $info;
    }

    /**
     * @param $id
     * @param string $field
     * @return array|null|\PDOStatement|string|\think\Model
     * 获取用户信息
     */
    protected function getUserInfo($id,$field='id,nick_name,mobile,model')
    {
        $where['id'] = $id;
        $where['status'] = 1;
        $info = model('user')->where($where)->field($field)->find();
        return $info;
    }
    /**
     * @return \think\response\Json
     * 异步上传图片
     */
    public function ajaxUploadImg()
    {
        $img = $this->uploadImg();
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
    protected function uploadImg()
    {
        $img = '';
        if (isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
            try{
                $dir  = "user/".$this->userInfo['id'];
                $file = request()->file('file');
                $upload = new \org\Storage();
                $upload->thumbUploadFile($file,$dir);
                $img = $upload->getFullName();
            }catch(\Exception $e){
                $this->error($e->getMessage());
            }
        }
        return $img;
    }
}
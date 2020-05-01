<?php

namespace app\api\controller\user;


class UserBase extends \think\Controller
{
    protected $userInfo;
    protected $defaultNoDomainImg = '/static/images/nopic.jpg';
    public function initialize()
    {
        $this->checkUser();
        parent::initialize();
    }
    protected function getHost(){
        return "http://api.".config('url_domain_root');
    }
    protected function getImgUrl($img)
    {
        if(strpos($img,'http://') !== FALSE)
        {
            return $img;
        }else if(!$img || !file_exists('.'.$img))
        {
            return $this->getHost().$this->defaultNoDomainImg;
        }else{
            return $this->getHost().$img;
        }
    }
    private function checkUser()
    {
        $token = request()->header('token');
        $return['code'] = 400;
        if($token)
        {
            $info = cache($token);
            if($info)
            {
                $this->userInfo = $info;
                return true;
            }else{
                $return['msg'] = '登录超时，请重新登录';
            }
        }else{
            $return['msg'] = '请登录后操作';
        }
        exit(json_encode($return));
    }
    /**
     * @param $data
     * @return mixed
     * 转换图片链接
     */
    protected function turnFileUrl($data)
    {
        if(is_array($data))
        {
            foreach($data as &$v)
            {
                $v['url'] = $this->getImgUrl($v['url']);
            }
        }else{
            $data = [];
        }
        return $data;
    }
}
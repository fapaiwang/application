<?php

namespace app\home\service;
class UserService
{
    /**
     * 获取用户信息
     * @param mixed
     * @return mixed|string
     * @author: al
     */
    public function getUserInfo()
    {
        $info = cookie('userInfo');
        $info = \org\Crypt::decrypt($info);
        return $info;
    }


}
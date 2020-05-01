<?php

namespace app\api\controller;


class Url extends \think\Controller
{
    public function index()
    {
        $url = input('get.url');
        $this->assign('url',base64_decode($url));
        return $this->fetch();
    }
}
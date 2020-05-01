<?php

namespace app\mobile\controller;
use think\captcha\Captcha;
class Verfiy
{
    public function index()
    {
        $config  = config('captcha.');
        $config['imageH'] = 40;
        $config['imageW'] = 80;
        $config['length'] = 3;
        $config['fontSize'] = 16;
        $captcha = new Captcha($config);
        return $captcha->entry();
    }
}
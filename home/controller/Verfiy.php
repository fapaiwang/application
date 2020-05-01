<?php


namespace app\home\controller;
use think\captcha\Captcha;
class Verfiy
{
    public function index()
    {
        $captcha = new Captcha(config('captcha.'));
        return $captcha->entry();
    }
}
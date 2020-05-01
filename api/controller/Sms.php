<?php

namespace app\api\controller;


class Sms extends \think\Controller
{
    public function index()
    {
        return action('home/Sms/sendSms');
    }
}
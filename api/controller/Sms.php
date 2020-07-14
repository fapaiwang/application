<?php

namespace app\api\controller;


class Sms extends \think\Controller
{
    public function index(){
        return action('home/Sms/sendSms');
    }
    public function sendSms(){
        return action('home/Sms/sendSmsGet');
    }

}
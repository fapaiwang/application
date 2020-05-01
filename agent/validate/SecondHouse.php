<?php
namespace app\agent\validate;


class SecondHouse extends \think\Validate
{
    protected $rule = [
        'title'      => 'require',
        'price'      => 'number'
    ];
    protected $message = [
        'title' => '房源名称必需填写',
        'price' => '价格只能为数字',
    ];
}
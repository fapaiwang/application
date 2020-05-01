<?php
namespace app\agent\validate;


class House extends \think\Validate
{
    protected $rule = [
        'title'      => 'require',
        'price'      => 'number'
    ];
    protected $message = [
        'title' => '楼盘名称必需填写',
        'price' => '价格只能为数字',
    ];
}
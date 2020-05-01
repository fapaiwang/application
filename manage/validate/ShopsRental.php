<?php


namespace app\manage\validate;


class ShopsRental extends \think\Validate
{
    protected $rule = [
        'title'      => 'require',
        'price'      => "/^\d+\.?\d{0,2}$/"
    ];
    protected $message = [
        'title' => '房源名称必需填写',
        'price' => '价格只能为数字,最多保留两位小数',
    ];
}
<?php
namespace app\agent\controller;
/**
 * Class Error
 * @package app\home\controller
 * 空控制器
 */
class Error extends \think\Controller
{
    public function index(){
        return $this->fetch('public/404');
    }
    public function _empty(){
        return $this->fetch('public/404');
    }
}
<?php


namespace app\home\controller;


class Module extends \think\Controller
{
    public function index($tpl = '')
    {
        if($tpl)
        {
            return $this->fetch($tpl);
        }
        return '';
    }
}
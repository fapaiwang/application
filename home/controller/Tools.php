<?php


namespace app\home\controller;
use app\common\controller\HomeBase;
class Tools extends HomeBase
{
    public function index()
    {
        return $this->fetch();
    }
}
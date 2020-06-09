<?php
/**
 * Created by PhpStorm.
 * @author: al
 * Date: 2020/6/9
 * Time: 10:11
 */
namespace app\home\controller;
use app\common\controller\HomeBase;
use think\Controller;

class Parts extends HomeBase
{
    public function company_profile(){
        return $this->fetch();
    }

}
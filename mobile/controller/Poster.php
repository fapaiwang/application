<?php


namespace app\mobile\controller;
use app\common\controller\MobileBase;

class Poster extends MobileBase
{
    public function index()
    {
        $id = input('param.id/d',0);
        if(!$id)
        {
            return '';
        }
        return action('common/Poster/index',['id'=>$id,'city'=>$this->cityInfo['id']],'service');
    }
}
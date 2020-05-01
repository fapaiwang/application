<?php


namespace app\home\controller;
class Poster
{
    public function index()
    {
        $id = input('param.id/d',0);
        if(!$id)
        {
            return '';
        }
        return action('common/Poster/index',['id'=>$id],'service');
    }
}
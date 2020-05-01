<?php
namespace app\agent\controller;
use \app\common\controller\AgentBase;

class HouseSandPic extends AgentBase
{
    public function delete()
    {
        \app\common\model\HouseSandPic::event('after_delete',function($obj){
            model('attachment')->deleteAttachment('',$obj->img);
        });
        parent::delete();
    }
}
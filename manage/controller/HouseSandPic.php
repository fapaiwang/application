<?php


namespace app\manage\controller;
use \app\common\controller\ManageBase;

class HouseSandPic extends ManageBase
{
    public function delete()
    {
        \app\common\model\HouseSandPic::event('after_delete',function($obj){
            model('attachment')->deleteAttachment('',$obj->img);
        });
        parent::delete();
    }
}
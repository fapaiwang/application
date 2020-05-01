<?php

namespace app\manage\controller;
use app\common\controller\ManageBase;

class HouseType extends ManageBase
{
    private $house_id;
    public function initialize()
    {
        $this->house_id = input('param.house_id/d',0);
        $this->param_extra = ['house_id'=>$this->house_id];
        parent::initialize();
        $info = model('house')->getHouseInfo(['id'=>$this->house_id]);
        $this->assign('houseInfo',$info);
    }
    public function search()
    {
        $where['house_id'] = $this->house_id;
        $this->queryData   = $where;
        return $where;
    }
    public function addDo()
    {
        \app\common\model\HouseType::event('after_insert',function($obj){
            $obj->getHouseTypeMinMaxValue($obj->house_id);
        });
        parent::addDo();
    }
    public function editDo()
    {
        \app\common\model\HouseType::event('after_update',function($obj){
            $obj->getHouseTypeMinMaxValue($obj->house_id);
        });
        parent::editDo();
    }
    public function delete()
    {
        \app\common\model\HouseType::event('after_delete',function($obj){
            model('attachment')->deleteAttachment('',$obj->img);
        });
        parent::delete();
    }

}
<?php

namespace app\agent\controller;
use \app\common\controller\AgentBase;

class HouseSand extends AgentBase
{
    private $house_id;
    //前置操作定义
    protected $beforeActionList = [
        'beforeIndex' =>  ['only'=>'index'],
        'beforeAdd'   =>  ['only'=>['add','edit']]
    ];
    public function initialize(){
        $this->house_id = input('param.house_id/d',0);
        $this->param_extra = ['house_id'=>$this->house_id];
        parent::initialize();
        $info = model('house')->getHouseInfo(['id'=>$this->house_id]);
        $this->mod = model('house_sand_pic');
        $this->assign('houseInfo',$info);
    }
    public function beforeIndex(){
        $big_menu = [
            'title' => '添加楼栋',
            'iframe' => url('HouseSand/add',['house_id'=>$this->house_id]),
            'id' => 'add',
            'width' => '500',
            'height' => '500'
        ];
        $this->_ajaxedit = true;
        $this->getAllPoint();
        $this->assign('big_menu', $big_menu);
    }
    //搜索
    public function _search(){
        $map['house_id'] = $this->house_id;
        return $map;
    }
    public function beforeAdd(){
        $where['house_id'] = $this->house_id;
        $where['status']   = 1;
        $type_list       = model('house_type')->where($where)->field('id,title')->select();
        $this->assign('type_list',$type_list);
    }
    //添加操作
    public function addDo(){
        \app\common\model\HouseSand::event('before_insert',function($obj){
            if(isset($obj->house_type_id)){
                $obj->house_type_id = implode(',',$obj->house_type_id);
            }
            return true;
        });
        parent::addDo();
    }

    /**
     * 编辑户型
     */
    public function editDo()
    {
        \app\common\model\HouseSand::event('before_update', function ($obj) {
            if(isset($obj->house_type_id)){
                $obj->house_type_id = implode(',',$obj->house_type_id);
            }else{
                $obj->house_type_id = '';
            }
            return true;
        });
        parent::editDo();
    }
    private function getAllPoint(){
        $where['house_id'] = $this->house_id;
        $detail = $this->mod->where($where)->find();
        $id_arr = [];
        $uri    = url('HouseSandPic/addDo');
        if($detail)
        {
            if($detail['data'])
            {
                foreach($detail['data'] as $v)
                {
                    $id_arr[$v['id']] = $v['point'];
                }
                $detail['data'] = json_encode($detail['data']);
            }
            $uri = url('HouseSandPic/editDo');
        }
        $this->assign('uri',$uri);
        $this->assign('select_points',array_keys($id_arr));
        $this->assign('init_points',$id_arr);
        $this->assign('points',$detail);
    }
}
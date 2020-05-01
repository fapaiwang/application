<?php


namespace app\manage\controller;


use app\common\controller\ManageBase;

class MetroStation extends ManageBase
{
    protected $beforeActionList = [
        'beforeIndex'  => ['only' => 'index'],
        'beforeEdit' => ['only' => 'edit'],
        'beforeAdd' => ['only' => 'add']
    ];
    protected $metroId;
    public function initialize()
    {
        parent::initialize();
        $this->metroId = input('param.metro_id/d');
    }
    public function search()
    {
        $where['metro_id'] = $this->metroId;
        $this->queryData = $where;
        return $where;
    }
    public function beforeIndex()
    {
        $big_menu = [
            'title' => '添加地铁站',
            'iframe' => url('MetroStation/add',['metro_id'=>$this->metroId]),
            'id' => 'add',
            'width' => '500',
            'height' => '500'
        ];
        $this->_ajaxedit = 1;
        $this->assign('big_menu', $big_menu);
    }
    protected function beforeAdd()
    {
        $metro = model('Metro')->where('id',$this->metroId)->field('id,city,name')->find();
        $prev_location = model('metro_station')->field('lat,lng')->order(['id'=>'desc'])->find();
        $this->assign('prev_location',$prev_location);
        $this->assign('metro',$metro);
    }
    protected function beforeEdit()
    {
        $id = input('param.id/d',0);
        $metro_id = model('metro_station')->where('id',$id)->value('metro_id');
        $metro = model('Metro')->where('id',$metro_id)->field('id,city,name')->find();
        $this->assign('metro',$metro);
    }
    public function addDo()
    {
        \app\common\model\MetroStation::event('before_insert',function($obj){
           $map = input('post.map');
            !empty($map) && $location = explode(',',$map);
            $obj->lng     = isset($location[0]) ? $location[0] : 0;
            $obj->lat     = isset($location[1]) ? $location[1] : 0;
        });
        parent::addDo();
    }
    public function editDo()
    {
        \app\common\model\MetroStation::event('before_update',function($obj){
            $map = input('post.map');
            !empty($map) && $location = explode(',',$map);
            $obj->lng     = isset($location[0]) ? $location[0] : 0;
            $obj->lat     = isset($location[1]) ? $location[1] : 0;
        });
        parent::editDo();
    }

    /**
     * 删除
     */
    public function delete()
    {
        \app\common\model\MetroStation::event('after_delete',function($obj){
            \org\Relation::deleteByStation($obj->id);//删除所有关联数据
        });
        parent::delete();
    }
}
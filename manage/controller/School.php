<?php


namespace app\manage\controller;
use app\common\controller\ManageBase;
class School extends ManageBase
{
    protected $beforeActionList = [
        'beforeIndex'  => ['only' => 'index'],
    ];
    public function initialize()
    {
        parent::initialize();
        $this->sort = 'ordid asc,id desc';
    }
    public function beforeIndex()
    {
        $big_menu = [
            'title' => '添加学校',
            'iframe' => url('School/add')
        ];
        $this->assign('big_menu', $big_menu);
        $this->assign('normal',true);
    }
    public function search()
    {
        $city    = input('param.city/d',0);
        $keyword = input('param.keyword');
        $where   = [];
        if($city)
        {
            $city_child = model('city')->get_child_ids($city,true);
            $where[] = ['city','in',$city_child];
        }
        $keyword && $where[] = ['title','like','%'.$keyword.'%'];
        $data = [
            'city' => $city,
            'keyword' => $keyword
        ];
        $this->queryData = $data;
        $this->assign('search',$data);
        return $where;
    }
    public function addDo()
    {
        \app\common\model\School::event('before_insert',function($obj){
            $map = input('post.map');
            if($map)
            {
                $location = explode(',',$map);
                $obj->lng = isset($location[0]) ? $location[0] : 0;
                $obj->lat = isset($location[1]) ? $location[1] : 0;
            }
        });
        parent::addDo();
    }
    public function editDo()
    {
        \app\common\model\School::event('before_update',function($obj){
            $map = input('post.map');
            if($map)
            {
                $location = explode(',',$map);
                $obj->lng = isset($location[0]) ? $location[0] : 0;
                $obj->lat = isset($location[1]) ? $location[1] : 0;
            }
        });
        parent::editDo();
    }

    /**
     * 删除
     */
    public function delete()
    {
        \app\common\model\School::event('after_delete',function($obj){
            \org\Relation::deleteBySchool($obj->id);//删除所有关联数据
        });
        parent::delete();
    }
}
<?php


namespace app\manage\controller;
use app\common\controller\ManageBase;
class Group extends ManageBase
{
    /**
     * @return array
     * 搜索条件
     */
    public function search()
    {
        $city    = input('get.city/d',0);
        $status  = input('get.status');
        $keyword = input('get.keyword');
        $where   = [];
        is_numeric($status) && $where['status'] = $status;
        $keyword && $where[] = ['title','like','%'.$keyword.'%'];
        if($city)
        {
            $city_child = model('city')->get_child_ids($city,true);
            $where[] = ['city','in',$city_child];
        }
        $data = [
            'city' => $city,
            'status' => $status,
            'keyword'=> $keyword
        ];
        $this->queryData = $data;
        $this->assign('search',$data);
        return $where;
    }
    public function addDo()
    {
        \app\common\model\Group::event('before_insert',function(Group $that,$obj){
            $pic       = $that->getPic();
            $obj->file = $pic;
            $obj->begin_time  = strtotime($obj->begin_time);
            $obj->end_time    = strtotime($obj->end_time);
            if($pic && empty($obj->img))
            {
                $obj->img = $pic[0]['url'];
            }
        });
        parent::addDo();
    }

    /**
     * 编辑
     */
    public function editDo()
    {
        \app\common\model\Group::event('before_update',function(Group $that,$obj){
            $pic       = $that->getPic();
            $obj->file = $pic;
            $obj->begin_time  = strtotime($obj->begin_time);
            $obj->end_time    = strtotime($obj->end_time);
            if($pic && empty($obj->img))
            {
                $obj->img = $pic[0]['url'];
            }
        });
        parent::editDo();
    }

    /**
     * 删除
     */
    public function delete()
    {
        \app\common\model\Group::event('after_delete',function($obj){
            //删除多图片及内容介绍 中的图片
            model('attachment')->deleteAttachment($obj->info,$obj->img,$obj->file);

        });
        parent::delete();
    }
    /**
     * @param $obj
     * 添加图片
     */
    private function getPic(){
        $insert = [];
        if(isset($_POST['pic']) && !empty($_POST['pic'])) {
            $images = $_POST['pic'];
            foreach ($images as $key => $v) {
                $insert[] = [
                    'url' => $v['pic'],
                    'title' => $v['alt'],
                ];
            }
        }
        return $insert;
    }
}
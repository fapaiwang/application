<?php
namespace app\agent\controller;
use app\common\controller\AgentBase;

class Developer extends AgentBase
{
    /**
     * @return array
     * 搜索条件
     */
    public function search()
    {
        $status  = input('get.status');
        $keyword = input('get.keyword');
        $where = [];
        if(is_numeric($status))
        {
            $where['status'] = $status;
        }
        $keyword && $where[] = ['title','like','%'.$keyword.'%'];
        $data = [
            'status'  => $status,
            'keyword' => $keyword
        ];
        $this->queryData = $data;
        $this->assign('search',$data);
        return $where;
    }
    public function ajaxGetDeveloper()
    {
        $where = $this->search();
        $this->list_field = "id,title";
        $this->onlyShowAgent = false;
        $this->lists($where,10);
        return  $this->fetch();
    }
}
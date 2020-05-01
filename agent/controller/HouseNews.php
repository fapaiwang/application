<?php

namespace app\agent\controller;
use app\common\controller\AgentBase;

class HouseNews extends AgentBase
{
    private $house_id;
    public function initialize(){
        $this->house_id = input('param.house_id/d',0);
        $this->param_extra = ['house_id'=>$this->house_id];
        parent::initialize();
        $this->_name = 'article';
        $this->sort = 'ordid';
        $this->order = 'asc,id desc';
        $this->getHouseName();
        $this->assign('house_id',$this->house_id);
        $this->assign('cate_id',1);
    }
    //添加前置事件
    public function addDo(){
        \app\common\model\Article::event('before_insert',function(Article $article,$obj){
            //自动提取摘要
            if($obj->description == '' && isset($obj->info)) {
                $content = stripslashes($obj->info);
                $obj->description = msubstr(str_replace(["'","\r\n","\t",'[page]','[/page]','&ldquo;','&rdquo;','&nbsp;'], '', strip_tags($content)),0,200);
                $obj->description = addslashes($obj->description);
            }
            isset($obj->update_time) && $obj->update_time = strtotime($obj->update_time);
            $obj->cate_alias = model('article_cate')->getCateAlias($obj->cate_id);
        });
        parent::addDo();
    }
    //获取楼盘名称
    private function getHouseName(){
        $info = model('house')->getHouseInfo(['id'=>$this->house_id]);
        $this->assign('house_title',$info['title']);
    }

}
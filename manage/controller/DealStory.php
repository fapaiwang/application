<?php
namespace app\manage\controller;
use app\common\controller\ManageBase;
class DealStory extends ManageBase
{
    /**
    *  成交故事列表
     */
    public function Index()
    {
        $keyword  = input('param.keyword');
        $where = array();
        if($keyword!=''){
            $where[] = array('customer_name|fapai_manager|community','like','%'.$keyword.'%');
        }
        $obj = model('deal_story');
        $list = $obj->where($where)->paginate(10);
        $this->assign('list',$list);
        $this->assign('pages',$list->render());
        $this->assign('search',array('keyword'=>$keyword));
        return $this->fetch();
    }
    /**
    *  添加成交故事
     */
    public function Add()
    {
        if(request()->isPost())
        {
            $data = input('post.');
            $data['create_time'] = date("Y-m-d H:i:s",time());
            $arr = model('deal_story')->insert($data);
            if($arr)
            {
                $this->success("添加成功");
            }else{
                $this->error("添加失败");
            }
        }else{
            return $this->fetch();
        }
    }
    /**
    *  编辑成交故事
     */
    public function edit()
    {
        if(request()->isPost())
        {
            $data = input('post.');
            $data['create_time'] = date("Y-m-d H:i:s",time());
            $arr = model('deal_story')->where(['id'=>$data['id']])->update($data);
            if($arr)
            {
                $this->success("编辑成功");
            }else{
                $this->error("编辑失败");
            }
        }else{
            $id  = input('param.id/d',0);
            if(!$id)
            {
                $this->error('参数错误');
            }
            $arr = model('deal_story')->where(['id'=>$id])->find();
            $this->assign('arr',$arr);
            return $this->fetch();
        }
    }
}
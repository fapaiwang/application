<?php


namespace app\home\controller;
use app\common\controller\HomeBase;
class SinglePage extends HomeBase
{
    public function index()
    {
        $alias = input('param.cate');
        if(!$alias)
        {
            $this->redirect('/');
        }
        $where['c.status'] = 1;
        $where['c.alias']  = $alias;
        $field = 'p.*';
        $join = [['pages p','c.id = p.cate_id']];
        $obj  = model('pages_cate');
        $info = $obj->alias('c')->join($join)->field($field)->where($where)->find();
        if(!$info)
        {
            return $this->fetch('public/404');
        }
        $cate = getCate('pagesCate','tree');
        $cate_arr = getCate('pagesCate');
        $pid      = $cate_arr[$info['cate_id']]['pid'];
        //看当前分类下是否有下级分类
        $child = $obj->get_child_ids($info['cate_id']);
        if($child)
        {
            $cate = $obj->field('id,name,alias')->where('id','in',$child)->where('status',1)->select();
            $where['c.alias'] = $cate[0]['alias'];
            $info = $obj->alias('c')->join($join)->field($field)->where($where)->find();
        }elseif(isset($cate[$pid]['_child'])){
            $cate = $cate[$pid]['_child'];
        }
        $this->setSeo($info);
        $this->assign('info',$info);
        $this->assign('cate',$cate);
        return $this->fetch();
    }
}
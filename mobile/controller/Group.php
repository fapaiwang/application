<?php


namespace app\mobile\controller;
use app\common\controller\MobileBase;
class Group extends MobileBase
{
    private $pageSize = 10;
    public function index()
    {
        $lists = $this->getLists();
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('total_page',$lists->lastPage());
        return $this->fetch();
    }

    /**
     * @return \think\response\Json
     * 异步加载列表
     */
    public function getGroupLists()
    {
        $page    = input('get.page/d',1);
        $data    = $this->getLists($page);
        $lists   = $data['lists'];
        if($lists)
        {
            foreach($lists as &$v)
            {
                $v['url']  = url('Group/detail',['id'=>$v['id']]);
                $v['city'] = getCityName($v['city']);
                $v['img']  = thumb($v['img'],200,150);
                $v['price'] = $v['price'].config('filter.house_price_unit');
                $v['end_time'] = $v['end_time'] - time();
            }
        }
        $return['code'] = 1;
        $return['data'] = $lists;
        $return['total_page'] = $data['total_page'];
        return json($return);
    }
    public function detail()
    {
        $id = input('param.id/d',0);
        if($id)
        {
            $where['g.id'] = $id;
            $where['g.status'] = 1;
            $field = 'g.*,h.lat,h.lng,h.address,h.sale_phone';
            $join  = [['house h','h.id = g.house_id']];
            $info = model('group')->alias('g')->join($join)->field($field)->where($where)->find();
            if(!$info)
            {
                return $this->fetch('public/404');
            }
            model('group')->where('id',$id)->setInc('hits');
            $info['sale_phone'] = json_decode($info['sale_phone'],true);
            $this->assign('info',$info);
            $this->assign('relation_group',$this->relationGroup($id));
        }
        return $this->fetch();
    }

    /**
     * @param int $page
     * @return array|\PDOStatement|string|\think\Collection|\think\Paginator
     * 团购列表
     */
    private function getLists($page = 0)
    {
        $time = time();
        $where['status'] = 1;
        $where[] = ['begin_time','lt',$time];
        $where[] = ['end_time','gt',$time];
        $this->getCityChild() && $where[] = ['city','in',$this->getCityChild()];
        $field   = "id,title,city,img,end_time,house_title,discount,price";
        $obj     = model('group');
        $obj     = $obj->where($where)->field($field)->order(['ordid'=>'asc','id'=>'desc']);;
        if($page)
        {
            $lists = $obj->page($page)->limit($this->pageSize)->select();
            $obj->removeOption();
            $count      = $obj->where($where)->count();
            $total_page = ceil($count/$this->pageSize);
            $lists      = ['lists'=>$lists,'total_page'=>$total_page];
        }else{
            $lists = $obj->paginate($this->pageSize);
        }
        return $lists;
    }
    private function relationGroup($id)
    {
        $where['status'] = 1;
        $where[] = ['id','neq',$id];
        $lists = model('group')->where($where)->field('id,title,city,house_title,img,price,discount')->order('id','desc')->limit(4)->select();
        return $lists;
    }
}
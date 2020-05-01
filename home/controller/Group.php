<?php


namespace app\home\controller;
use app\common\controller\HomeBase;
class Group extends HomeBase
{
    public function initialize(){
        $this->cur_url = 'House';
        parent::initialize();

    }
    public function index()
    {
        $time = time();
        $where['status'] = 1;
        $where[] = ['begin_time','lt',$time];
        $where[] = ['end_time','gt',$time];
        $this->getCityChild() && $where[] = ['city','in',$this->getCityChild()];
        $lists = model('group')->where($where)->order(['ordid'=>'asc','id'=>'desc'])->paginate(5);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }

    /**
     * @return mixed
     * 团购详情
     */
    public function detail()
    {
        $id = input('param.id/d',0);
        if($id)
        {
            $info = model('group')->where('id',$id)->find();
            if($info)
            {
                $info['address'] = model('house')->where('id',$info['house_id'])->value('address');
            }else{
                return $this->fetch('public/404');
            }
            model('group')->where('id',$id)->setInc('hits');
            $this->assign('info',$info);
            $this->assign('hot_house',$this->getHotHouse());
        }
        return $this->fetch();
    }
    private function getHotHouse($num = 5)
    {
        $where['status'] = 1;
        $this->getCityChild() && $where[] = ['city','in',$this->getCityChild()];
        $lists = model('house')->where($where)->field('id,title,img,price,city,opening_time,opening_time_memo')->limit($num)->select();
        return $lists;
    }
}
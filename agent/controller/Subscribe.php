<?php

namespace app\agent\controller;


use app\common\controller\AgentBase;

class Subscribe extends AgentBase
{
    public function index()
    {
        $arr   = ['house','second_house','rental'];
        $where = $this->search();
        $type  = input('param.type','house');
        if(!in_array($type,$arr))
        {
            $type = 'house';
        }
        $where['s.model'] = $type;
        $where['h.agent_id'] = $this->agentInfo['company_id'];
        $join  = [[$type.' h','h.id = s.house_id','left']];
        $field = 's.*,h.title';
        $lists = model('subscribe')->alias('s')->join($join)->field($field)->where($where)->order('s.create_time desc')->paginate(20);
        $this->_exclude = 'edit';
        $this->assign('list',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('options',$this->check());
        return $this->fetch();
    }
    public function search(){
        $map = [];
        $type = input('get.type');
        is_numeric($type) && $map['s.type'] = $type;
        $this->assign('search', [
            'type'  => $type,
        ]);
        return $map;
    }
}
<?php

namespace app\agent\controller;
use app\common\controller\AgentBase;
class TransactionRecord extends AgentBase
{
    private $estate_id;
    private $mod;
    public function initialize(){
        $this->estate_id = input('param.estate_id/d',0);
        $this->param_extra = ['estate_id'=>$this->estate_id];
        parent::initialize();
        $info = model('estate')->getEstateInfo(['id'=>$this->estate_id]);
        $this->mod = model('transaction_record');
        $this->assign('estateInfo',$info);
    }
    public function search()
    {
        $where['estate_id'] = $this->estate_id;
        return $where;
    }
}
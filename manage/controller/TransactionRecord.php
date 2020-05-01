<?php


namespace app\manage\controller;
use app\common\controller\ManageBase;
class TransactionRecord extends ManageBase
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
        $this->queryData = $where;
        return $where;
    }
}
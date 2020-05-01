<?php


namespace app\manage\controller;


use app\common\controller\ManageBase;

class Answer extends ManageBase
{

    public function index()
    {
        $join = [['question q','q.id = a.question_id']];
        $field = 'a.*,q.content as questioin_con';
        $lists = model('answer')->alias('a')->join($join)->field($field)->order('a.create_time desc')->paginate(20);
        $this->_exclude = 'edit';
        $this->assign('list',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('options',$this->check());
        return $this->fetch();
    }
}
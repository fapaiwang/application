<?php

namespace app\mobile\controller\user;


class Answer extends UserBase
{
    public function index()
    {
        $where['a.broker_id'] = $this->userInfo['id'];
        $join = [['question q','a.question_id = q.id']];
        $field = 'a.content as answer,a.create_time,q.id,q.content';
        $lists = model('answer')->alias('a')->where($where)->join($join)->field($field)->order(['q.create_time'=>'desc'])->paginate(10);

        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('title','我的回答');
        return $this->fetch();
    }
}
<?php


namespace app\home\controller\user;
use app\common\controller\UserBase;
class Question extends UserBase
{
    public function index()
    {
        $where['user_id'] = $this->userInfo['id'];
        $field = 'id,house_id,content,create_time,reply_num';
        $lists = model('question')->where($where)->field($field)->order('create_time','desc')->paginate(10);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }
}
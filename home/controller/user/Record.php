<?php


namespace app\home\controller\user;


use app\common\controller\UserBase;

class Record extends UserBase
{
    /**
     * @return mixed
     * 账单记录
     */
    public function index()
    {
        $type = input('get.type',0);
        $type && $where['op'] = $type;
        $where['user_id'] = $this->userInfo['id'];
        $lists = model('blance_record')->where($where)->order('create_time','desc')->paginate(15);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }

    /**
     * @return mixed
     * 订单记录
     */
    public function order()
    {
        $where['user_id'] = $this->userInfo['id'];
        $lists = model('user_pay')->where($where)->order('create_time','desc')->paginate(15);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }
}
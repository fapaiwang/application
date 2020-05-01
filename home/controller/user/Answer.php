<?php

namespace app\home\controller\user;
use app\common\controller\UserBase;
class Answer extends UserBase
{
    public function index()
    {
        $where['a.broker_id'] = $this->userInfo['id'];
        $join = [['question q','a.question_id = q.id']];
        $field = 'a.content as answer,a.id as aid,a.create_time,q.id,q.content';
        $lists = model('answer')->alias('a')->where($where)->join($join)->field($field)->order(['q.create_time'=>'desc'])->paginate(10);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }

    /**
     * @return \think\response\Json
     * 删除房源
     */
    public function delete()
    {
        $id = input('param.id/d',0);
        $return['code'] = 0;
        if(!$id)
        {
            $return['msg'] = '参数错误';
        }else{
            $where['id'] = $id;
            $where['broker_id'] = $this->userInfo['id'];
            if(model('answer')->where($where)->delete())
            {
                $return['code'] = 1;
                $return['msg']  = '删除成功';
            }else{
                $return['msg']  = '删除失败';
            }
        }
        return json($return);
    }
}
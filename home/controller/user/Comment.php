<?php


namespace app\home\controller\user;
use app\common\controller\UserBase;
class Comment extends UserBase
{
    /**
     * @return mixed
     * 评论列表
     */
    public function index()
    {
        $where['c.user_id'] = $this->userInfo['id'];
        $join  = [['user u','u.id = c.broker_id','left']];
        $field = 'c.id,c.content,c.user_id,c.broker_id,c.content,c.good,c.bad,c.status,c.create_time,u.nick_name';
        $lists = model('user_comment')->alias('c')
                                      ->field($field)
                                      ->where($where)
                                      ->join($join)
                                      ->order('c.create_time','desc')
                                      ->paginate(10);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }
    /**
     * @return mixed
     * 楼盘点评
     */
    public function house()
    {
        $where['c.user_id'] = $this->userInfo['id'];
        $join = [['house h','h.id = c.house_id','left']];
        $field = 'c.id,c.house_id,c.content,c.create_time,c.status,h.title';
        $lists = model('comment')->alias('c')
            ->field($field)
            ->where($where)
            ->join($join)
            ->order('c.create_time','desc')
            ->paginate(10);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }
    /**
     * @return \think\response\Json
     * 删除
     */
    public function delete()
    {
        $id = input('param.id/d',0);
        $return['code'] = 0;
        if($id)
        {
            $where['id'] = $id;
            $where['user_id'] = $this->userInfo['id'];
            if(model('user_comment')->where($where)->delete())
            {
                $return['code'] = 1;
                $return['msg']  = '删除成功';
            }else{
                $return['msg']  = '删除失败';
            }
        }else{
            $return['msg']      = '参数错误';
        }
        return json($return);
    }
}
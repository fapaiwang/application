<?php
namespace app\home\controller\user;
use app\common\controller\UserBase;
class SubscribeFollow extends UserBase
{
    public function initialize()
    {
        parent::initialize();
        if($this->userInfo['model'] == 1)
        {
            $this->error('无权限操作');
        }
    }
    /**
     * @return mixed
     * 写跟进页面
     */
    public function record()
    {
        $sid = input('param.id/d',0);
        $this->assign('sid',$sid);
        return $this->fetch();
    }

    /**
     * @return \think\response\Json
     * 跟进保存页面
     */
    public function save()
    {
        $data = input('post.');
        $data['create_time'] = strtotime($data['create_time']);
        $data['broker_id']   = $this->userInfo['id'];
        $data['broker_name'] = $this->userInfo['nick_name'];
        $return['code']      = 0;
        if($data['token'] != session('__token__')){
            $return['msg']   = '保存失败';
        }else if(request()->isAjax()){
            if(model('subscribe_follow')->allowField(true)->save($data))
            {
                $return['code']  = 1;
                $return['msg']   = '保存成功';
                model('subscribe')->where('id',$data['sid'])->where('status',0)->setField('status',1);
                session('__token__',null);
            }else{
                $return['msg']   = '保存失败';
            }
        }else{
            $return['msg']       = '操作失败';
        }
        return json($return);
    }

    /**
     * @return mixed
     * 查看跟进
     */
    public function view()
    {
        $id    = input('param.id/d',0);
        $lists = '';
        if($id)
        {
            $lists = model('subscribe_follow')->where('sid',$id)->order('create_time','desc')->select();
        }
        $this->assign('lists',$lists);
        return $this->fetch();
    }
}
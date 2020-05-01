<?php
namespace app\agent\controller;
use app\common\controller\AgentBase;
class Account extends AgentBase
{
    public function initialize()
    {
        parent::initialize();
        $this->_name = 'agent';
    }
    /**
     * @return mixed
     * 修改密码
     */
    public function editPassword()
    {
        if(request()->isPost())
        {
            $old_password = input('post.password');
            $new_password = input('post.new_password');
            $confirm_password = input('post.confirm_password');
            $where['id'] = $this->agentInfo['id'];
            $where['password'] = passwordEncode($old_password);
            $obj = model('agent');
            $info = $obj->where($where)->find();
            if(!$info)
            {
                $this->error('原密码不正确');
            }elseif($new_password != $confirm_password){
                $this->error('两次新密码输入不一致！');
            }else{
                $data['password'] = passwordEncode($new_password);
                if($obj->save($data,$where)){
                    if($this->agentInfo['surper_manager'] == 1)
                    {
                        model('agent_company')->save(['password'=>$new_password],['id'=>$this->agentInfo['company_id']]);
                    }
                    $this->success('密码修改成功');
                }else{
                    $this->error('修改失败，新密码和原密码一至致！');
                }
            }
        }
        return $this->fetch();
    }

    /**
     * @return mixed
     * 子账号列表
     */
    public function accountLists()
    {
        $big_menu = [
                'title' => '添加子账号',
                'iframe' => url('Account/add'),
                'id' => 'add',
                'width' => '500',
                'height' => '480'
         ];
        $this->_ajaxedit = true;
        $where['company_id'] = $this->agentInfo['company_id'];
        $where['surper_manager'] = 0;
        $field = 'id,user_name,mobile,true_name,status,create_time';
        $lists = model('agent')->where($where)->field($field)->order('create_time','desc')->paginate(20);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('big_menu', $big_menu);
        $this->assign('options',$this->check());
        return $this->fetch();
    }

    /**
     * 添加用户
     */
    public function addDo()
    {
        \app\common\model\Agent::event('before_insert',function(Account $account,$obj){
            $obj->password = passwordEncode($obj->password);
            $obj->company_id = $account->agentInfo['company_id'];
            $obj->create_time = time();
        });
        parent::addDo();
    }

    /**
     * 编辑
     */
    public function editDo()
    {
        \app\common\model\Agent::event('before_update',function($obj){
           if($obj->password)
           {
               $obj->password = passwordEncode($obj->password);
           }else{
               unset($obj->password);
           }
        });
        parent::editDo();
    }
    /**
     * @return \think\response\Json
     * 检查用户名或手机号码是否存在
     */
    public function ajaxCheckUser(){
        $username = input('param.user_name');
        $mobile   = input('param.mobile');
        $id       = input('param.id/d',0);
        if($username){
            $map['user_name'] = $username;
        }elseif($mobile){
            if(is_mobile($mobile)){
                $map['mobile'] = $mobile;
            }else{
                return  $this->ajaxReturn(0);
            }
        }else{
            return $this->ajaxReturn(0);
        }
        $id && $map[] = ['id','neq',$id];
        $total = model('agent')->where($map)->count('id');
        if($total){
            return $this->ajaxReturn(0);
        }else{
            return  $this->ajaxReturn(1);
        }
    }
}
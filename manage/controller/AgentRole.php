<?php


namespace app\manage\controller;
use app\common\controller\ManageBase;
class AgentRole extends ManageBase
{
    protected $beforeActionList = [
        'beforeIndex'=>['only'=>'index'],
    ];
    public function beforeIndex()
    {
        $big_menu = [
            'title' => '添加分类',
            'iframe' => url('add'),
            'id' => 'add',
            'width' => '500',
            'height' => '300'
        ];
        $this->assign('big_menu', $big_menu);
    }
    //编辑分类权限
    public function editmenu(){
        $tree = new \org\Tree();
        $tree->icon = ['│ ','├─ ','└─ '];
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        $lists  = model('agent_menu')->order('ordid')->select();
        $roleid = input('param.id/d');
        //读取权限表
        $menu = model('agent_role')->where(['id'=>$roleid])->value('rule');
        $menuarr = [];
        if($menu){
            $menuarr = explode(',',$menu);
        }
        $this->assign('menuarr',$menuarr);
        $this->assign('roleid',$roleid);
        $this->assign('list', recursion($lists));
        return $this->fetch();
    }
    //设置分类权限
    public function setrule(){
        $idarr     = input('param.menuid/a');
        $role_id   = input('param.role_id');
        $rolemenu  = model('agent_role');
        if($idarr){
            $ids = implode(',',$idarr);
            if($rolemenu->save(['rule'=>$ids],['id'=>$role_id])){
                $map[] = ['id','in',$idarr];
                $menu = model('agent_menu')->where($map)->order('ordid asc')->select();
                cache('agentmenu_'.$role_id,$menu);
                return $this->ajaxReturn(1,'权限设置成功','成功');
            }else{
                return  $this->ajaxReturn(0,'设置失败','失败');
            }
        }else{
            return $this->ajaxReturn(0,'请至少选择一项','请至少选择一项');
        }
    }
}
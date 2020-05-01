<?php


namespace app\manage\controller;
use app\common\controller\ManageBase;
class Role extends ManageBase
{
    private $mod;
    protected $beforeActionList = [
        'beforeIndex'=>['only'=>'index'],
    ];
    public function initialize() {
        parent::initialize();
        $this->mod = model('role');
    }
    public function beforeIndex()
    {
        $big_menu = [
            'title' => '添加角色',
            'iframe' => url('Role/add'),
            'id' => 'add',
            'width' => '500',
            'height' => '300'
        ];
        $this->assign('big_menu', $big_menu);
        $this->assign('roleid',$this->adminInfo['role']);
        $this->assign('superroleid',config('SUPERADMIN_ROLEID'));
       // return $this->fetch();
    }

    public function delete(){
        \app\common\model\Role::event('before_delete',function($obj){
            //不能删除自己的角色 和比自己权限高的角色 如果角色下面存在管理员也不能删除
            $id = $obj->id;
            $manager = model('admin')->where(['role_id'=>$id])->count('id');
            if($id == $this->adminInfo['role'] || $id < $this->adminInfo['role'] || $manager > 0){
                return false;
            }
            return true;
        });
        \app\common\model\Role::event('after_delete',function($obj){
            $id = $obj->id;
            //删除成功同时删除权限数据
            db('role_menu')->where(['roleid'=>$id])->delete();

            return true;
        });
        parent::delete();
    }
    public function editmenu(){
        $tree = new \org\Tree();
        $tree->icon = ['│ ','├─ ','└─ '];
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        $result =  cache('managemenu'.$this->adminInfo['role']);//M('menu')->order('ordid')->select();
        $array = [];
        $roleid = input('param.id/d');
        //读取权限表
        $menu = \think\Db::name('role_menu')->where(['roleid'=>$roleid])->field('id')->select();
        $menuarr = [];
        if($menu){
            foreach($menu as $v){
                $menuarr[] = $v['id'];
            }
        }
        $this->assign('menuarr',$menuarr);
        $this->assign('roleid',$roleid);
        $this->assign('list', recursion($result));
        return $this->fetch();
    }
    public function setrule(){
        $idarr  = input('param.menuid/a');
        $roleid = input('param.role_id');
        $obj    = model('menu');
        $rolemenu = db('role_menu');
        if($idarr){
            //删除原有的权限
            $rolemenu->where('roleid',$roleid)->delete();
            //复制数据到角色权限表
            $ids = implode(',',$idarr);
            $lists = $obj->where([['id','in',$ids]])->select();
            foreach($lists as $k=>$v){
                $lists[$k]['roleid'] = $roleid;
            }
            $lists = objToArray($lists);
            if($rolemenu->insertAll($lists)){
                cache('managemenu'.$roleid,$lists);//缓存权限文件
               return $this->ajaxReturn(1,'权限设置成功','成功');
            }else{
                return  $this->ajaxReturn(0,'设置失败','失败');
            }
        }else{
            return $this->ajaxReturn(0,'请至少选择一项','请至少选择一项');
        }
    }
}
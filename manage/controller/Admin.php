<?php


namespace app\manage\controller;
use app\common\controller\ManageBase;
class Admin extends ManageBase
{
    private $mod;
    protected $beforeActionList = [
        'beforeIndex'=>['only'=>'index'],
        'beforeEdit' =>['only'=>'add,edit']
    ];
    public function initialize() {
        parent::initialize();
        $this->mod = model('admin');
    }
    public function beforeIndex()
    {
        $big_menu = [
            'title' => '添加管理员',
            'iframe' => url('Admin/add'),
            'id' => 'add',
            'width' => '500',
            'height' => '400'
        ];
        $this->assign('big_menu', $big_menu);
        $this->assign('adminInfo',$this->adminInfo);
        $this->assign('superroleid',config('SUPERADMIN_ROLEID'));
        //return $this->fetch();
    }
    public function beforeEdit(){
        $lists = model('role')->field('id,title')->where(['status'=>1])->select();
        if($this->adminInfo['role']!=config('SUPERADMIN_ROLEID')){
            foreach($lists as $k=>$v){
                if($v['id'] < $this->adminInfo['role']){
                    unset($lists[$k]);
                }
            }
        }
        $this->assign('role',$lists);
    }
    public function editDo()
    {
        \app\common\model\Admin::event('before_update',function($obj){
            if(empty($obj->password)){
                unset($obj->password);
            }
            return true;
        });
        parent::editDo();
    }
    public function delete(){
        \app\common\model\Admin::event('before_delete',function($obj){
            //不能删除自己或其它角色管理员不能删除超级管理员的账号
            $id = $obj->id;
            if($id == $this->adminInfo['id'] || $this->adminInfo['role'] > config('SUPERADMIN_ROLEID')){
                return false;
            }
            return true;
        });
        parent::delete();
    }
    public function ajaxCheckName() {
        $name = input('param.username');
        $id = input('param.id/d');
        if ($this->mod->name_exists($name, $id)) {
            echo 0;
        } else {
            echo 1;
        }
    }

}
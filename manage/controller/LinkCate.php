<?php


namespace app\manage\controller;
use app\common\controller\ManageBase;
class LinkCate extends ManageBase
{
    private $mod;
    protected $beforeActionList = [
        'beforeIndex'=>['only'=>'index'],
    ];
    public function initialize() {
        parent::initialize();
        $this->mod = model('link_cate');
    }
    public function beforeIndex()
    {
        $big_menu = [
            'title' => '添加分类',
            'iframe' => url('LinkCate/add'),
            'id' => 'add',
            'width' => '500',
            'height' => '300'
        ];
        $this->_ajaxedit = true;
        $this->assign('big_menu', $big_menu);
    }

    public function delete(){
        \app\common\model\LinkCate::event('before_delete',function($obj){
            //分类下存在链接不能删除
            $id = $obj->id;
            $manager = model('link')->where(['cate_id'=>$id])->count('id');
            if($manager > 0){
                return false;
            }
            return true;
        });

        parent::delete();
    }
    public function ajaxCheckName() {
        $name = input('param.name');
        $id = input('param.id/d');
        if ($this->name_exists($name,$id)) {
            $this->ajaxReturn(0, '分类已存在');
        } else {
            $this->ajaxReturn(1);
        }
    }

    private function name_exists($name,$id=0){
        $pk = $this->mod->getPk();
        $where['name'] = $name;
        $where[]    = [$pk,'neq',$id];
        $result=$this->mod->where($where)->count($pk);
        if($result){
            return 1;
        }else{
            return 0;
        }
    }
}
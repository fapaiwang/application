<?php


namespace app\manage\controller;

use app\common\controller\ManageBase;
class LinkmenuCate extends ManageBase
{
    private $mod;
    protected $beforeActionList = [
      'beforeIndex' => ['only' => 'index']
    ];
    public function initialize(){
        parent::initialize();
        $this->mod = model('linkmenu_cate');
    }
    public function beforeindex() {
        $big_menu = [
            'title' => '增加分类',
            'iframe' => url('LinkmenuCate/add'),
            'id' => 'add',
            'width' => '500',
            'height' => '300'
        ];
        $this->_ajaxedit = 1;
        $this->_data = [
            'addchildmenu'    => [
                'c' => 'Linkmenu',
                'a' => 'add',
                'str'    => '<a data-height="500" data-width="500" data-id="add" data-uri="%s" data-title="添加 - 添加子菜单" class="J_showDialog layui-btn layui-btn-xs" href="javascript:;">添加子菜单</a>',
                'param' => ['menu_id'=>'@id@'],
                'isajax' => 1,
                'replace'=> ''
            ],
            'menulist'    => [
                'c' => 'Linkmenu',
                'a' => 'index',
                'str'    => '<a href="%s" class="layui-btn layui-btn-xs">子菜单管理</a>',
                'param' => ['menu_id'=>'@id@'],
                'isajax' => 0,
                'replace'=> ''
            ],
        ];
        $this->assign('big_menu', $big_menu);
    }
    public function delete(){
        //判断是否有下级菜单存在  存在则不能删除
        \app\common\model\LinkmenuCate::event('before_delete',function($obj){
            $id = $obj->id;
            //是否有下级菜单
            $child = model('linkmenu')->where('menuid',$id)->count();

            if(!empty($child)){
                return false;
            }
            return true;
        });
        parent::delete();
    }
}
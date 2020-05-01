<?php


namespace app\manage\controller;
use \app\common\controller\ManageBase;
class PositionCate extends ManageBase
{
    private $model;
    //前置操作定义
    protected $beforeActionList = [
        'beforeIndex' =>  ['only'=>'index'],
    ];
    public function initialize(){
        parent::initialize();
        $this->model = getPostionModel();
        $this->assign('model',$this->model);
    }
    protected function beforeIndex(){
        $big_menu = [
            'title' => '添加推荐位',
            'iframe' => url('add'),
            'id' => 'add',
            'width' => '500',
            'height' => '360'
        ];
        $this->_data = [

                 'poslist' => [
                'c' => 'Position',
                'a' => 'index',
                'str'    => '<a href="%s" class="layui-btn layui-btn-xs">内容管理</a>',
                'param' => ['pos_id'=>'@id@'],
                'isajax' => 0,
                'replace'=> ''
            ],
            ];
        $this->_ajaxedit = true;
        $this->assign('big_menu', $big_menu);
    }
    //搜索
    public function search(){
        $map      = [];
        $status   = input('get.status');
        is_numeric($status) && $map['status'] = $status;
        ($model = input('get.model')) && $map['model'] = $model;
        $this->assign('search', [
            'status'  => $status,
            'model' => $model
        ]);
        return $map;
    }
    public function delete(){
        \app\common\model\PositionCate::event('before_delete',function($obj){
            //验证分类下是否存在信息
            $count = model('position')->where(['cate_id'=>$obj->id])->count();
            if($count > 0){
                $this->errMsg = '该推荐位下还存在信息，不能被删除';
                return false;
            }
            return true;
        });
        parent::delete();
    }
}
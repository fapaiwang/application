<?php

namespace app\manage\controller;
use app\common\controller\ManageBase;
class PagesCate extends ManageBase
{
//前置操作定义
    protected $beforeActionList = [
        // 'second' =>  ['except'=>'hello'],
        'beforeEdit'  =>  ['only'=>'edit,add'],
    ];
    private $mod;
    public function initialize(){
        parent::initialize();
        $this->mod = model('pages_cate');
    }
    public function index(){
        $lists = $this->mod->order('ordid asc,id desc')->select();
        $lists = recursion(objToArray($lists));
        $this->_data = [
            'addchild' => [
                'c' => 'PagesCate',
                'a'  => 'add',
                'str' => '<a data-height="480" data-width="500" data-id="add" data-uri="%s" data-title="添加 - %s 子栏目" class="J_showDialog layui-btn layui-btn-xs" href="javascript:;">添加子栏目</a>',
                'param' => ['pid' => '@id@'],
                'isajax' => true,
                'replace' => ''
            ]
        ];
        $big_menu = [
            'title' => '添加栏目',
            'iframe' => url('PagesCate/add'),
            'id' => 'add',
            'width' => '500',
            'height' => '480'
        ];
        $this->_ajaxedit = true;
        $this->assign('options',$this->check());
        $this->assign('big_menu', $big_menu);
        $this->assign('list',$lists);
        return $this->fetch();
    }
    protected function beforeEdit(){
        $pid = input('param.pid/d');
        $spid = 0;
        if ($pid) {
            $spid = $this->mod->where(['id'=>$pid])->value('spid');
            $spid = $spid ? $spid.$pid : $pid;
        }
        $this->assign('spid', $spid);
    }
    public function addDo(){
        //注册前置事件 只对模型调用
        \app\common\model\PagesCate::event('before_insert',function(PagesCate $cate,$obj){
            //检测分类是否存在
            if($obj->name_exists($obj->name, $obj->pid,0)){
                $cate->errMsg = '分类名称已存在';
                return false;
            }
            //生成spid
            $obj->spid = $obj->get_spid($obj->pid);
            //栏目别名
                $py = new \org\Pinyin();
                $obj->alias=trim($py->getAllPY($obj->name));		//全部拼音
            if ($obj->name_exists($obj->alias, 0, 0)) {
                $cate->errMsg = '分类名称已存在';
                return false;
            }
            return true;
        });
        \app\common\model\PagesCate::event('after_insert',function(PagesCate $cate,$obj){
            $cate->doCache();
        });
        parent::addDo();
    }
    public function delete(){
        //判断是否有下级栏目或文章存在  存在则不能删除
        \app\common\model\PagesCate::event('before_delete',function($obj){
            $id = $obj->id;
            //是否有下级栏目
            $child = $obj->get_child_ids($id);
            //是否有文章
            if(!empty($child)){
                return false;
            }
            return true;
        });
        //注册删除后置事件 删除分类后再删除对应的文章
        \app\common\model\PagesCate::event('after_delete',function(PagesCate $cate,$obj){
            $id = $obj->id;
            //是否有下级栏目
            model('pages')->destroy($id);
            $cate->doCache();
            return true;
        });
        parent::delete();
    }
    public function ajaxGetchilds() {
        $id = input('param.id/d');
        $return = $this->mod->field('id,name')->where(['pid'=>$id])->select();
        if (!$return->isEmpty()) {
          return  $this->ajaxReturn(1, 'success', $return);
        } else {
          return  $this->ajaxReturn(0, 'error');
        }
    }
    /**
     * 更新单页分类缓存
     */
    public function cache(){
        if($this->doCache()){
            $this->success('单页分类缓存更新成功');
        }else{
            $this->error('单页分类缓存更新失败');
        }
    }
    public function doCache(){
        $lists = $this->mod->field('id,pid,name,alias')->where('status',1)->select();
        if($lists){
            $cate = objToArray($lists);//普通列表
            $temp = [];
            foreach($cate as $v){
                $temp[$v['id']] = $v;
            }
            $tree = list_to_tree($temp);//树形列表
            cache('pagesCate',['cate'=>$temp,'tree'=>$tree]);
            return true;
        }
        return false;

    }
}
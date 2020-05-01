<?php


namespace app\manage\controller;
use app\common\controller\ManageBase;
class ArticleCate extends ManageBase
{
//前置操作定义plus-square-o minus-square-o
    protected $beforeActionList = [
        'beforeIndex' =>  ['only'=>'index'],
        'beforeEdit'  =>  ['only'=>'edit,add'],
    ];
    private $mod;
    public function initialize(){
        parent::initialize();
        $this->mod = model('article_cate');
    }
    public function index(){
        $lists = $this->mod->order(['ordid'=>'asc','id'=>'desc'])->select();
        $lists = recursion(objToArray($lists));
        $this->assign('list',$lists);
        return $this->fetch();
    }
    public function beforeIndex(){
        $big_menu = [
            'title' => '添加栏目',
            'iframe' => url('ArticleCate/add'),
            'id' => 'add',
            'width' => '500',
            'height' => '480'
        ];
        $this->_data = [
            'addchild' => [
                'c' => 'ArticleCate',
                'a'  => 'add',
                'str' => '<a data-height="480" data-width="500" data-id="add" data-uri="%s" data-title="添加 - %s 子栏目" class="J_showDialog layui-btn layui-btn-xs" href="javascript:;">添加子栏目</a>',
                'param' => ['pid' => '@id@'],
                'isajax' => true,
                'replace' => ''
            ]
        ];
        $this->_ajaxedit = true;
        $this->assign('options',$this->check());
        $this->assign('big_menu', $big_menu);
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
        \app\common\model\ArticleCate::event('before_insert',function(ArticleCate $cate,$obj){
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
        parent::addDo();
    }
    public function delete(){
        //判断是否有下级栏目或文章存在  存在则不能删除
        \app\common\model\ArticleCate::event('before_delete',function(ArticleCate $cate,$obj){
            $id = $obj->id;
            //是否有下级栏目
            $child = $obj->get_child_ids($id);
            //是否有文章
            $count = model('article')->where(['cate_id'=>$id])->count('id');
            if(!empty($child)){
                $cate->errMsg = '请先删除下级分类';
                return false;
            }
            if($count != 0)
            {
                $cate->errMsg = '该分类下还存在文章';
                return false;
            }
            return true;
        });
        parent::delete();
    }
    public function ajaxGetchilds() {
        $id = input('param.id/d');
        $return = $this->mod->field('id,name')->where(['pid'=>$id])->select();
        if (!$return->isEmpty()) {
           return $this->ajaxReturn(1, 'success', $return);
        } else {
            return $this->ajaxReturn(0, 'error');
        }
    }

    /**
     * 更新文章分类缓存
     */
    public function cache(){
        if($this->doCache()){
            $this->success('文章分类缓存更新成功');
        }else{
            $this->error('文章分类缓存更新失败');
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
            cache('articleCate',['cate'=>$temp,'tree'=>$tree]);
            return true;
        }
        return false;

    }
}
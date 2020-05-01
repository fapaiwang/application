<?php

namespace app\manage\controller;
use app\common\controller\ManageBase;

class PosterSpace extends ManageBase
{
    private $mod;
    protected $beforeActionList = [
      'beforeIndex'  => ['only' => 'index'],
        'beforeEdit' => ['only' => 'add,edit']
    ];
    public function initialize() {
        parent::initialize();
        $this->mod = model('poster_space');
    }
    public function beforeIndex(){
        $big_menu = [
            'title' => '添加广告位',
            'iframe' => url('PosterSpace/add'),
            'id' => 'add',
            'width' => '500',
            'height' => '500'
        ];
        $this->_ajaxedit = 1;
        $this->_data = [
            'addspace'    => [
                'c' => 'Poster',
                'a' => 'add',
                'str'    => '<a href="%s" class="layui-btn layui-btn-xs">添加广告</a>',
                'param' => ['space_id'=>'@id@'],
                'isajax' => 0,
                'replace'=> ''
            ],
            'addlist'    => [
                'c' => 'Poster',
                'a' => 'index',
                'str'    => '<a href="%s" class="layui-btn layui-btn-xs">广告列表</a>',
                'param' => ['space_id'=>'@id@'],
                'isajax' => 0,
                'replace'=> ''
            ],
       ];
        $this->assign('big_menu', $big_menu);
    }
    public function beforeEdit(){
        $type = $this->mod->typeArr();
        $this->assign('type_list',$type);
    }

    /**
     * @return mixed
     * 查看广告位调用代码
     */
    public function showCode()
    {
        $id = input('param.id/d',0);
        $this->assign('id',$id);
        return $this->fetch();
    }
    public function delete(){
        //注册前置事件 如果该广告位下有广告则不能删除
        \app\common\model\PosterSpace::event('before_delete',function($obj){
            if($obj->items > 0){
                return false;
            }
            return true;
        });
        parent::delete();
    }
    /**
     * 检查名称是否有重复
     */
    public function ajaxCheckName() {
        $name = input('param.name');
        $id = input('param.id/d');
        if ($this->name_exists($name,$id)) {
           return $this->ajaxReturn(0, '广告位已存在');
        } else {
           return $this->ajaxReturn(1);
        }
    }

    /**
     * 广告js
     */
    public function cache(){
        if($this->doCache()){
            $this->success('广告js更新成功');
        }else{
            $this->error('广告js更新失败');
        }
    }
    public function doCache()
    {
        $flag = false;
        $space = model('poster_space')->where('status',1)->field('id')->select();
        if($space)
        {
            $file_path = env("root_path").'public/static/poster_js/';
            $delDir = new \org\Dir($file_path);
            is_dir($file_path) && $delDir->del($file_path);
            try{
                foreach($space as $v)
                {
                    $str = action('common/Poster/index',['id'=>$v['id']],'service');
                    file_put_contents($file_path.$v['id'].'.js',$str);
                }
                $flag = true;
            }catch(\Exception $e){
                \think\facade\Log::write($e->getMessage(),'error');
            }
        }
        return $flag;
    }
    private function name_exists($name,$id=0){
        $pk = $this->mod->getPk();
        $where['name'] = $name;
        $id && $where[]    = [$pk,'neq',$id];
        $result=$this->mod->where($where)->count($pk);
        if($result){
            return 1;
        }else{
            return 0;
        }
    }

}
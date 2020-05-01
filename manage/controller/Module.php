<?php


namespace app\manage\controller;


use app\common\controller\ManageBase;

class Module extends ManageBase
{
    protected $beforeActionList = [
      'beforeIndex' => ['only'=>'index'],
        'beforeEdit' => ['only'=>'add,edit']
    ];
    public function beforeIndex()
    {
        $terminal = input('get.terminal/d',1);
        $this->sort = "ordid asc,id desc";
        $this->_exclude = ['edit','delete'];
        $big_menu = [
            'title' => '添加模块',
            'iframe' => url('Module/add',['terminal'=>$terminal]),
            'id' => 'add',
            'btn' => false,
            'width' => '',
            'height' => ''
        ];
        $this->_data = [
            'edit'=>[
                'c' => 'Module',
                'a' => 'edit',
                'str' => '<a href="%s" class="layui-btn layui-btn-xs">编辑</a>',
                'param' => ['id' => '@id@','terminal'=>$terminal],
                'isajax' => false,
                'replace' => ''
            ]
        ];
        $this->assign('big_menu', $big_menu);
    }
    protected function search()
    {
        $terminal = input('get.terminal/d',1);
        $data['terminal'] = $terminal;
        $this->queryData = $data;
        return $data;
    }
    public function beforeEdit()
    {
        $terminal = input('param.terminal/d',1);
        $this->assign('terminal',$terminal);
    }
    public function addDo()
    {
        \app\common\model\Module::event('after_insert',function($obj){
            $terminal = input('post.terminal/d',1);
            $dir    = $terminal == 1 ? 'home' : 'mobile';
            $path = env('app_path').$dir.'/view/module/index/';
            $file_name = $path.$obj->id.'.html';
            @file_put_contents($file_name,$obj->content);
        });
        parent::addDo();
    }
    public function editDo()
    {
        \app\common\model\Module::event('after_update',function($obj){
            $terminal = input('post.terminal/d',1);
            $dir    = $terminal == 1 ? 'home' : 'mobile';
            $path = env('app_path').$dir.'/view/module/index/';
            $file_name = $path.$obj->id.'.html';
            @file_put_contents($file_name,$obj->content);
        });
        parent::editDo();
    }
    /**
     * @return bool
     * 生成缓存
     */
    public function doCache()
    {
        $lists = model('module')->field('id,content,terminal')->select();
        if(!$lists->isEmpty())
        {
            foreach($lists as $v)
            {
                $dir    = $v['terminal'] == 1 ? 'home' : 'mobile';
                $path = env('app_path').$dir.'/view/module/index/';
                $file_name = $path.$v['id'].'.html';
                @file_put_contents($file_name,$v['content']);
            }
            return true;
        }
        return false;
    }
}
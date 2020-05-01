<?php




namespace app\manage\controller;



use app\common\controller\ManageBase;

class Pages extends ManageBase

{

    private $onecate;

    private $one;

    public function initialize() {

        parent::initialize();

        $this->one= model('pages');

        $this->onecate = model('pages_cate');

    }



    //单页面列表

    public function index() {



        $tree = new \org\Tree();

        $tree->icon = ['&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ '];

        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $result = $this->onecate->order('id','desc')->select();

        $result = objToArray($result);

        $array = [];

        foreach($result as $r) {

            //是否有下一级

            if ($this->onecate->get_child_ids($r['id'])) {

                $r['str_manage'] = '';

            } else {

                $r['str_manage'] = '<a class="layui-btn layui-btn-xs" href="'.url('Pages/edit', ['id'=>$r['id']]).'">编辑</a>';

            }

            $r['parentid_node'] = ($r['pid'])? ' class="child-of-node-'.$r['pid'].'"' : '';

            $array[] = $r;

        }

        $str  = "<tr id='node-\$id' \$parentid_node>

                <td align='center'>\$id</td>

                <td style='text-align:left;padding-left:10px;'>\$spacer\$name</td>

                <td align='center'>\$str_manage</td>

                </tr>";

        // var_dump($array);

        $tree->init($array);

        $list = $tree->get_tree(0, $str);

        $this->assign('list', $list);

        $this->assign('list_table', true);

       return $this->fetch();

    }



    //单页面编辑

    public function edit() {

        $id = input('param.id/d');

        if($id){

            $cate = $this->onecate->field('id,name,alias')->where('id',$id)->find();

            $info = $this->one->where('cate_id',$id)->find();

            $this->assign('cate',$cate);

            if($info){

                $template = 'edit';

                $this->assign('info',$info);

            }else{

                $template = 'add';

            }

        }else{

            $this->error('参数错误');

        }

        return $this->fetch($template);

    }

    //编辑 前置事件

    public function editDo(){

        \app\common\model\Pages::event('before_update',function($obj){

            //自动提取摘要

            if($obj->description == '' && isset($obj->info)) {

                $content = stripslashes($obj->info);

                $obj->description = msubstr(str_replace(["'","\r\n","\t",'[page]','[/page]','&ldquo;','&rdquo;','&nbsp;'], '', strip_tags($content)),0,200);

                $obj->description = addslashes($obj->description);

            }

        });

        parent::editDo();

    }

    //添加前置事件

    public function addDo(){

        \app\common\model\Pages::event('before_insert',function(Pages $pages,$obj){

            //自动提取摘要

            if($obj->description == '' && isset($obj->info)) {

                $content = stripslashes($obj->info);

                $obj->description = msubstr(str_replace(["'","\r\n","\t",'[page]','[/page]','&ldquo;','&rdquo;','&nbsp;'], '', strip_tags($content)),0,200);

                $obj->description = addslashes($obj->description);

            }

            $obj->cate_alias = $pages->onecate->getCateAlias($obj->cate_id);

        });

        parent::addDo();

    }



}
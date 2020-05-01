<?php
namespace app\manage\controller;
use app\common\controller\ManageBase;
use org\Tree;
class AgentMenu extends ManageBase
{
    //前置操作定义
    protected $beforeActionList = [
        'beforeIndex' =>  ['only'=>'index'],
        'beforeEdit'  =>  ['only'=>'edit,add'],
    ];
    private $mod;

    public function initialize(){
        parent::initialize();
        $this->del_true = true;
        $this->mod = model('agent_menu');
    }
    public function beforeIndex(){
        $big_menu = [
            'title' => '添加菜单',
            'iframe' => url('add'),
            'id' => 'add',
            'width' => '500',
            'height' => '500',
        ];
        $this->_data = [
            'addchild' => [
                'c' => 'AgentMenu',
                'a'  => 'add',
                'str' => '<a data-height="480" data-width="500" data-id="add" data-uri="%s" data-title="添加 - %s 子栏目" class="J_showDialog layui-btn layui-btn-xs layui-btn-normal" href="javascript:;">添加子栏目</a>',
                'param' => ['pid' => '@id@'],
                'isajax' => true,
                'replace' => ''
            ]
        ];
        $this->_ajaxedit = true;
        $this->assign('options',$this->check());
        $this->assign('big_menu', $big_menu);
    }
    public function index()
    {
        $lists = $this->mod->field('id,pid,title as name,ordid')->select();
        $lists = recursion(objToArray($lists));
        $this->assign('list',$lists);
        return $this->fetch();
    }
    //edit前置操作
    protected function beforeEdit(){
        $id = input('param.id/d');
        $info['pid'] = input('param.pid/d');
        if($id){
            $info = $this->mod->field('id,pid')->find($id);
        }
        $tree = new Tree();
        $result = $this->mod->select();
        $result = objToArray($result);
        $array = [];
        foreach($result as $r) {
            $r['selected'] = $r['id'] == $info['pid'] ? 'selected' : '';
            $array[] = $r;
        }
        $str  = "<option value='\$id' \$selected>\$spacer \$title</option>";
        $tree->init($array);
        $select_menus = $tree->get_tree(0, $str);
        $this->assign('select_menus', $select_menus);
    }

    /**
     * @return \think\response\Json
     * ajax修改单个值
     */
    public function ajaxEdit()
    {
        //AJAX修改数据
        $pk = $this->mod->getPk();
        $id = input($pk);
        $field = input('param.field');
        $val = input('param.val');
        $this->mod->where([$pk => $id])->setField($field, $val);
        $lists = $this->mod->order('ordid')->select();
        return $this->ajaxReturn(1);
    }

}

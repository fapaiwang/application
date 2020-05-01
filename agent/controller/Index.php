<?php
namespace app\agent\controller;
use app\common\controller\AgentBase;
use org\AgentRole;
use think\facade\App;

class Index extends AgentBase
{
    public function initialize(){
        parent::initialize();
    }
    public function index()
    {
        $cache_url = url('Cache/cacheAll');
        $agentInfo = $this->agentInfo;
        $my_admin = ['username'=>$agentInfo['user_name'], 'role'=>$agentInfo['role_id']];
        $this->assign('my_admin', $my_admin);
        $this->assign('cache_url',$cache_url);
        $this->assign('menu',$this->left());
        return $this->fetch();
    }
    public function panel(){
        $where['c.id'] = $this->agentId;
        $field = 'c.*,r.title';
        $join  = [['agent_role r','r.id = c.cate_id']];
        $info = model('agent_company')->alias('c')->join($join)->where($where)->field($field)->find();
        $this->assign('info', $info);
        return $this->fetch();
    }


    public function left(){
        $obj = model('agent_menu');
        $temp = [
            'id' => 9997,
            'title' => '账号管理',
            'module_name' => 'Account',
            'action_name' => 'index',
            'icon' => '&#xe605;',
            'url'  => url('Account/index'),
            'spread' => true,
            'children' =>[]
        ];
        $temp['children'][] =  [
            'id' => 9998,
            'title' => '修改密码',
            'module_name' => 'Account',
            'action_name' => 'editPassword',
            'icon' => '&#xe63f;',
            'url'  => url('Account/editPassword'),
            'spread' => true,
        ];
        //创始账号才有权限添加修改子账号
        if($this->agentInfo['surper_manager'] == 1)
        {
            $temp['children'][] = [
                'id' => 9999,
                'title' => '子账号管理',
                'module_name' => 'Account',
                'action_name' => 'accountLists',
                'icon' => '&#xe63f;',
                'url'  => url('Account/accountLists'),
                'spread' => true,
            ];
        }
        $left_menu = $obj->adminMenu(0);
        foreach ($left_menu as $key=>$val) {
            if(empty($val['icon']) || $val['icon'] == '&#xe63f;'){
                $left_menu[$key]['icon'] ='fa-bars';
            }
            $left_menu[$key]['spread']   = true;
            $left_menu[$key]['children'] = $obj->adminMenu($val['id']);

        }
        $data = array_merge([$temp],$left_menu);
      return $data;
    }
}

<?php
namespace app\manage\controller;
use app\common\controller\ManageBase;
use org\Role;
use think\facade\App;

class Index extends ManageBase
{
    public function initialize(){
        parent::initialize();
    }
    public function index()
    {
        $cache_url = url('Cache/cacheAll');
        $adminInfo = $this->adminInfo;
        $my_admin = ['username'=>$adminInfo['username'], 'role'=>$adminInfo['role']];
        $this->assign('my_admin', $my_admin);
        $this->assign('cache_url',$cache_url);
        return $this->fetch();
    }
    public function getTopMenu(){
        $adminInfo = $this->adminInfo;
        $info['roleid'] = $adminInfo['role'];
        $top_menus= Role::getTopMenu($info);
        return json($top_menus);
    }
    public function panel(){
        $system_info = [
            'version' => '<b>v<span id="version">1.0 </span></b>',
            'server_domain' => $_SERVER['SERVER_NAME'] . ' [ ' . gethostbyname($_SERVER['SERVER_NAME']) . ' ]',
            'server_os' => PHP_OS,
            'web_server' => $_SERVER["SERVER_SOFTWARE"],
            'php_version' => PHP_VERSION,
            'mysql_version' => $this->_mysql_version(),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'max_execution_time' => ini_get('max_execution_time') . '秒',
            'safe_mode' => (boolean) ini_get('safe_mode') ?  '是' : '否',
            'zlib' => function_exists('gzclose') ?  '是' : '否',
            'curl' => function_exists("curl_getinfo") ? '是' : '否',
            'timezone' => function_exists("date_default_timezone_get") ? date_default_timezone_get() : '否'
        ];
        $this->assign('system_info', $system_info);
        return $this->fetch();
    }
    private function _mysql_version()
    {
        $version = db()->query("select version() as ver");
        return $version[0]['ver'];
    }
    public function map(){
        $obj = model('menu');
        $r = $obj->adminMenu(0);
        $list = [];
        foreach ($r as $v) {
            $v['sub'] = $obj->adminMenu($v['id']);
            foreach ($v['sub'] as $key=>$sv) {
                $v['sub'][$key]['sub'] = $obj->adminMenu($sv['id']);
            }
            $list[] = $v;
        }
        $this->assign('list', $list);
        $result = $this->fetch();
        echo $result;exit;
    }
    public function left(){
        $menuid = input('param.menuid/d');
        $obj = model('menu');
        if ($menuid) {
            $left_menu = $obj->adminMenu($menuid);
            foreach ($left_menu as $key=>$val) {
                if(empty($val['icon']) || $val['icon'] == '&#xe63f;'){
                    $left_menu[$key]['icon'] ='fa-bars';
                }
                $left_menu[$key]['spread']   = true;
                $left_menu[$key]['children'] = $obj->adminMenu($val['id']);

            }
        } else {
            $left_menu[0] = ['id'=>0,'name'=>'常用菜单'];
            $left_menu[0]['children'] = [];
            if ($r = $obj->where(['often'=>1])->order('ordid asc')->select()) {
                $left_menu[0]['children'] = $r;
            }
        }
      return json($left_menu);
    }
}

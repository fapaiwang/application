<?php

namespace app\common\controller;


class UserBase extends \think\Controller
{
    protected $userInfo;
    public function initialize()
    {
        parent::initialize();
        if(!$this->checkUserLogin())
        {
            $this->error('请登录后再操作！',url('Login/index'));
        }
        $controller = request()->controller();
        $action     = request()->action();
        $this->getMenu();
        $this->assign('city',getCity());
        $this->assign('site',getSettingCache('site'));
        $this->assign('cityInfo',cookie('cityInfo'));
        $this->assign('controller',strtolower($controller));
        $this->assign('action',$action);
    }
    private function checkUserLogin(){
        $info = cookie('userInfo');
        $info = \org\Crypt::decrypt($info);
        $this->userInfo = $info;
        $this->assign('userInfo',$info);
        return $info;
    }

    /**
     * @param $id
     * @param string $field
     * @return array|null|\PDOStatement|string|\think\Model
     * 获取用户信息
     */
    protected function getUserInfo($id,$field='id,nick_name,mobile,model')
    {
        $where['id'] = $id;
        $where['status'] = 1;
        $info = model('user')->where($where)->field($field)->find();
        return $info;
    }
    /**
     * 读取站点导航
     */
    private function getMenu(){
        $menu = cache('nav');
        if(!$menu){
            $map['status'] = 1;
            $lists = model('nav')->where($map)->order('ordid asc')->select();
            if($lists){
                $cate = objToArray($lists);//普通列表
                $temp = [];
                foreach($cate as $v){
                    if(empty($v['alias']) && empty($v['model'])){
                        $temp[$v['id']] = $v;
                    }elseif(!empty($v['alias'])){
                        $temp[$v['alias']] = $v;
                    }else{
                        $temp[$v['model']] = $v;
                    }
                }
                cache('nav',$temp);
                $menu = $temp;
            }

        }
        $c = request()->module().'/'.request()->controller();
        if($c !='home/Index'){
            if(isset($menu[$c])){
                $this->seo = [
                    'title'     =>$this->site['title'],
                    'seo_title' => empty($menu[$c]['seo_title']) ? $menu[$c]['title'] : $menu[$c]['seo_title'],
                    'seo_keys'  => $menu[$c]['seo_keys'],
                    'seo_desc'  => $menu[$c]['seo_desc']
                ];
            }
        }
        $this->assign('menu',$menu);
    }
}
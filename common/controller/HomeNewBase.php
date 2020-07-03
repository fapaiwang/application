<?php
namespace app\common\controller;

use think\image\Exception;

class HomeNewBase extends \think\Controller{

    protected $site;  //所有信息

    public function initialize(){
        parent::initialize();
        $this->checkUserLogin();
        //公司基本信息
        $site = getSettingCache('site');
        if($site['status'] == 0){
            die($site['reson']);
        }
        !isset($site['city_domain']) && $site['city_domain'] = 0;
        $this->site = $site;


        //当前所在页面
        $module = $this->request->module();//模块名
        $controller = $this->request->controller();//控制器名
        $action = $this->request->action();//方法名
        $model_url = $module.'/'.$controller.'@'.$action;

        //获取页头 页脚 导航栏
        $head_nav = model('nav')->field('id,title,url,action,seo_title,seo_keys,seo_desc,model_action')
            ->where([['status','=',1],['pos','=',1]])->order("ordid asc")->select();

        $this->assign('head_nav',$head_nav);
        $this->assign('model_url',$model_url);
        //todo ->cache('86400')
        $footer_nav = model('nav')->field('title,url,action,seo_title,seo_keys,seo_desc')->where(['status'=>1,'pos'=>2])->select();
        $this->assign('footer_nav',$footer_nav);
        $model_url = $module.'/'.$controller;
        $seo_s =  model('nav')->field('id,title,url,action,seo_title,seo_keys,seo_desc,model_action')
            ->where([['status','=',1],['pos','=',1],['model_action','=',$model_url]])->find();
        $seo_s['title'] =$seo_s->seo_title ?? "";
        $seo_s['keys'] =$seo_s->seo_keys ?? "";
        $seo_s['desc'] =$seo_s->seo_desc ?? "";
        $this->assign('seo',$seo_s);
        //友情链接
        $link = model('link')->field('name,url')->where([['city','=',39],['status','=',1]])->select();
        $this->assign('site',$site);
        $this->assign('link',$link);
    }

    /**
     * 用户信息
     * @param mixed
     * @author: al
     */
    private function checkUserLogin(){
        $info = cookie('userInfo');
        $info = \org\Crypt::decrypt($info);
        $this->userInfo = $info;
        $this->assign('userInfo',$info);
    }
    /**
     * @return mixed
     * 空操作 找不到操作方法时执行
     */
    public function _empty(){

        return $this->fetch('public/404');

    }


}
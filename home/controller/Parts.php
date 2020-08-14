<?php
/**
 * Created by PhpStorm.
 * @author: al
 * Date: 2020/6/9
 * Time: 10:11
 */
namespace app\home\controller;
use app\common\controller\HomeBase;
use think\Controller;

class Parts extends HomeBase
{
    public function company_profile(){
        $seo_s =  model('nav')->field('id,title,url,action,seo_title,seo_keys,seo_desc,model_action')
            ->where([['status','=',1],['url','=','/parts/company_profile.html']])->find();
        $seo_s['title'] =$seo_s->seo_title ?? "";
        $seo_s['keys'] =$seo_s->seo_keys ?? "";
        $seo_s['desc'] =$seo_s->seo_desc ?? "";
        $this->assign('seo',$seo_s);
        return $this->fetch();
    }

}
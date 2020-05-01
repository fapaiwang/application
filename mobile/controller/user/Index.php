<?php

namespace app\mobile\controller\user;
class Index extends UserBase
{
    public function index()
    {
        $this->getMenu();
        $this->assign('site',getSettingCache('site'));
        return $this->fetch();
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
        $c = request()->controller();
        if($c !='Index'){
            if(isset($menu[$c])){
                $title = $this->site['title'];
                $seo_title = empty($menu[$c]['seo_title']) ? $menu[$c]['title'] : $menu[$c]['seo_title'];
                $seo_keys  = $menu[$c]['seo_keys'];
                $seo_desc  = $menu[$c]['seo_desc'];
                $this->seo = [
                    'title'     =>$title,
                    'seo_title' => $seo_title,
                    'seo_keys'  => $seo_keys,
                    'seo_desc'  => $seo_desc
                ];
            }
        }
        $this->assign('menu',$menu);
    }
}
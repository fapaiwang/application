<?php

namespace app\common\service;


class NavCache
{
    public static function create()
    {
        $menu  = cache('nav');
        $child = cache('nav_child');
        $obj = model('nav');
        if(request()->isMobile())
        {
            return ['menu'=>$menu,'child'=>$child];
        }
        try{
            if(!$menu){
                $lists = $obj->where('status',1)->order('ordid asc')->select();
                $lists = list_to_tree(objToArray($lists));
                if($lists){
                    $temp = [];
                    $child = [];
                    foreach($lists as &$v){
                        if(isset($v['_child']))
                        {
                            foreach($v['_child'] as &$val)
                            {
                                if(empty($val['alias']) && empty($val['model'])){
                                }elseif(!empty($val['alias'])){
                                    $val['url'] = url($val['model'].'/'.$val['action'],['cate'=>$val['alias']]);
                                }else{
                                    $val['url'] = url($val['model'].'/'.$val['action']);
                                    $child[$val['model']] = $val;
                                }
                            }
                        }
                        if(empty($v['alias']) && empty($v['model'])){
                            $temp[$v['id']] = $mobile_temp[$v['id']] = $v;
                        }elseif(!empty($v['alias'])){
                            $v['url'] = url($v['model'].'/'.$v['action'],['cate'=>$v['alias']]);
                            $temp[$v['alias']] = $mobile_temp[$v['alias']] = $v;
                        }else{
                            $v['url'] = url($v['model'].'/'.$v['action']);
                            $temp[$v['model']] = $v;
                        }
                    }
                    $menu = $temp;
                    cache('nav',$temp);
                    cache('nav_child',$child);
                }
            }
           return ['menu'=>$menu,'child'=>$child];
        }catch(\Exception $e){
            \think\facade\Log::write('导航生成出错'.$e->getFile().$e->getLine().$e->getMessage());
            return [];
        }
    }
}
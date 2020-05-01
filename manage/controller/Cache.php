<?php

namespace app\manage\controller;
use app\common\controller\ManageBase;
class Cache extends ManageBase
{
    public function cacheAll() {
        $model = [
            ['controller' => 'Setting','action' => 'doCache','name'=>'更新站点设置缓存'],
            ['controller' => 'ArticleCate','action' => 'doCache' ,'name'=>'更新文章分类缓存'],
            ['controller' => 'pagesCate','action' => 'doCache' ,'name'=>'更新单页分类缓存'],
            ['controller' => 'City','action' => 'doCache' ,'name'=>'更新城市缓存'],
            ['controller' => 'Linkmenu','action' => 'doCache' ,'name'=>'更新扩展属性缓存'],
            ['controller' => 'Nav','action' => 'doCache' ,'name'=>'更新前台导航缓存'],
            ['controller' => 'PosterSpace','action' => 'doCache','name'=>'更新广告js文件'],
            ['controller' => 'UserCate','action'=> 'doCache','name'=>'更新用户分类缓存'],
            ['controller' => 'Module','action' => 'doCache' ,'name'=>'更新PC/Mobile首页模块缓存'],
            //['controller' => 'Badip','action' => 'doCache' ,'name'=>'更新禁止IP缓存'],
        ];
        if(input('param.sub')){
            $p = input('param.page/d',0);
            if($p == 0)
            {
                $path   = env('runtime_path').'cache/';
                $delDir = new \org\Dir($path);
                is_dir($path) && $delDir->delDir($path);
            }
            if(array_key_exists($p,$model)){
                $m = $model[$p];
                $flag = action($m['controller'].'/'.$m['action']);
                if($flag){
                    $str = $m['name'].'成功....';
                }else{
                    $str = $m['name'].'<span style="color:#ff0000">失败....</span>';
                }
                $status = 1;
            }else{
                $str = '<span style="color:#ff0000">更新全站缓存完成....</span>';
                $status = 0;
            }
           return $this->ajaxReturn($status,$str,$p+1);
        }else{
            $this->assign('model',json_encode($model));
            return $this->fetch('index/cacheall');
        }

    }
}
<?php

namespace app\manage\controller;
use app\common\controller\ManageBase;

class Link extends ManageBase
{
    private $mod;
    protected $beforeActionList = [
        'beforeIndex'=>['only'=>'index'],
        'beforeEdit' =>['only'=>'add,edit']
    ];
    public function initialize() {
        parent::initialize();
        $this->mod = model('link');
    }
    public function beforeIndex()
    {
        $big_menu = [
            'title' => '添加链接',
            'iframe' => url('Link/add'),
            'id' => 'add',
            'width' => '500',
            'height' => '450'
        ];
        $this->_ajaxedit = true;
        $cate_list = model('link_cate')->where('status',1)->select();
        $this->assign('options',$this->check());
        $this->assign('cate_list',$cate_list);
        $this->assign('big_menu', $big_menu);
    }
    public function beforeEdit(){
        $cate_list = model('link_cate')->where('status',1)->select();
        $this->assign('cate_list',$cate_list);
    }
    public function search(){
        $cate_id = input('param.cate_id/d');
        $keyword = input('param.keyword');
        $city    = input('param.city/d',0);
        $map = [];
        $cate_id && $map['cate_id'] = $cate_id;
        $keyword && $map[] = ['name','like','%'.$keyword.'%'];
        $city && $map[] = ['city','eq',$city];
        $search = [
          'cate_id' => $cate_id,
          'keyword' => $keyword,
            'city'  => $city
        ];
        $this->queryData = $search;
        $this->assign('search',$search);
        return $map;
    }


}
<?php


namespace app\manage\controller;
use app\common\controller\ManageBase;
class Keyword extends ManageBase
{
    private $mod;
    protected $beforeActionList = [
        'beforeIndex' => ['only'=>'index']
    ];
    public function initialize(){
        parent::initialize();
        $this->mod = model('keyword');
    }
    public function beforeIndex(){
        $big_menu = [
            'title' => '添加关联链接',
            'iframe' => url('Keyword/add'),
            'id' => 'add',
            'width' => '500',
            'height' => '360'
        ];
        $this->_ajaxedit = true;
        $this->assign('big_menu', $big_menu);
    }
    protected function search(){
        $map = [];
        $status = input('get.status');
        is_numeric($status) && $map['status']  = $status;
        ($keyword = input('param.keyword')) && $map[] = ['word','like','%'.$keyword.'%'];
        $search['keyword'] = $keyword;
        $search['status']  = $status;
        $this->queryData   = $search;
        $this->assign('search',$search);
        return $map;
    }

    /**
     * 更新关联链接缓存
     */
    public function cache(){
        if($this->docache()){
            $this->success('关联链接缓存更新成功');
        }else{
            $this->error('关联链接缓存更新失败');
        }
    }
    public function docache(){
        $lists = $this->mod->where('status',1)->select();
        if($lists){
            cache('keyword',objToArray($lists));
            return true;
        }else{
            return false;
        }
    }
}
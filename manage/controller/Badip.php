<?php


namespace app\manage\controller;
use app\common\controller\ManageBase;

class Badip extends ManageBase
{
    private $mod;
    protected $beforeActionList = [
        'beforeIndex' => ['only'=>'index']
    ];
    public function initialize(){
        parent::initialize();
        $this->mod = model('badip');
    }
    public function beforeIndex(){
        $big_menu = [
            'title' => '添加禁止IP',
            'iframe' => url('Badip/add'),
            'id' => 'add',
            'width' => '500',
            'height' => '360'
        ];
        $this->_ajaxedit = true;
        $this->assign('big_menu', $big_menu);
    }
    protected function search(){
        $map = [];
        ($keyword = input('get.keyword')) && $map[] = ['ip','like','%'.$keyword.'%'];
        $status = input('get.status');

        is_numeric($status) && $map['status'] = $status;
        $search['keyword'] = $keyword;
        $search['status']  = $status;
        $this->queryData = $search;
        $this->assign('search',$search);
        return $map;
    }
    /**
     * 更新禁止IP缓存
     */
    public function cache(){
        if($this->docache()){
            $this->success('禁止IP缓存更新成功');
        }else{
            $this->error('暂无可缓存数据');
        }
    }
    public function docache(){
        $lists = $this->mod->where('status',1)->select();
        if($lists){
            cache('badip',objToArray($lists));
            return true;
        }else{
            return false;
        }
    }
}
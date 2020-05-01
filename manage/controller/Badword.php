<?php


namespace app\manage\controller;
use app\common\controller\ManageBase;

class Badword extends ManageBase
{
    private $mod;
    protected $beforeActionList = [
        'beforeIndex' => ['only'=>'index'],
        'beforeEdit'  => ['only' => 'add,edit']
    ];
    public function initialize(){
        parent::initialize();
        $this->mod = model('badword');
    }
    public function beforeIndex(){
        $big_menu = [
            'title' => '添加敏感词',
            'iframe' => url('Badword/add'),
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
        is_numeric($status) && $map['status'] = $status;
        ($keyword = input('get.keyword')) && $map[] = ['word','like','%'.$keyword.'%'];
        $search['keyword'] = $keyword;
        $search['status']  = $status;
        $this->queryData = $search;
        $this->assign('search',$search);
        return $map;
    }
    public function beforeEdit(){
        $level = $this->mod->levelArr();
        $this->assign('level',$level);
    }
    /**
     * 更新敏感词缓存
     */
    public function cache(){
        if($this->docache()){
            $this->success('敏感词缓存更新成功');
        }else{
            $this->error('暂无可缓存数据');
        }
    }
    public function docache(){
        $lists = $this->mod->where('status',1)->select();
        if($lists){
            cache('badword',objToArray($lists));
            return true;
        }else{
            return false;
        }
    }
}
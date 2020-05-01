<?php


namespace app\manage\controller;
use app\common\controller\ManageBase;
class UserCate extends ManageBase
{
    protected $beforeActionList = [
        'beforeIndex' => ['only'=>'index']
    ];
    public function initialize(){
        parent::initialize();
        $this->sort = 'ordid asc,id desc';
    }
    protected function beforeIndex()
    {
        $big_menu = [
            'title' => '添加用户分类',
            'iframe' => url('UserCate/add'),
            'id' => 'add',
            'width' => '',
            'height' => '',
            'btn' => false
        ];
        $this->_exclude = 'edit';
        $this->_data = [
            'edit2' => [
                'c' => 'UserCate',
                'a' => 'edit',
                'str' => '<a data-height="" data-width="" data-uri="%s" data-title="编辑 - %s" data-show_btn="false" class="J_showDialog layui-btn layui-btn-xs" href="javascript:;">编辑</a>',
                'param' => ['id' => '@id@'],
                'isajax' => true,
                'replace' => ''
            ],
        ];
        $check = [0=>'是',1=>'否'];
        $this->assign('check',$check);
        $this->assign('big_menu', $big_menu);
    }

    /**
     * 添加
     */
    public function addDo()
    {
        \app\common\model\UserCate::event('after_insert',function(UserCate $cate){
            $cate->doCache();
        });
        parent::addDo();
    }

    /**
     * 编辑
     */
    public function editDo()
    {
        \app\common\model\UserCate::event('after_update',function(UserCate $cate){
            $cate->doCache();
        });
        parent::editDo();
    }
    /**
     * 更新分类缓存
     */
    public function cache(){
        if($this->doCache()){
            $this->success('用户分类缓存更新成功');
        }else{
            $this->error('用户分类缓存更新失败');
        }
    }
    public function doCache(){
        $lists = model('user_cate')->where('status',1)->order('ordid asc,id desc')->select();
        $data  = [];
        if(!$lists->isEmpty())
        {
            foreach($lists as $v)
            {
                $data[$v['id']] = $v;
            }
            cache('user_cate',$data);
            return true;
        }
        return false;
    }
}
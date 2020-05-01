<?php


namespace app\manage\controller;


use app\common\controller\ManageBase;

class HouseComment extends ManageBase
{
    protected $beforeActionList = [
        'beforeIndex' => ['only'=>['index']]
    ];
    public function initialize(){
        parent::initialize();
        $this->sort = 'create_time desc';
        $this->_name = 'comment';
    }
    public function beforeIndex()
    {
        $this->_exclude = 'edit';
        $this->_data = [
            'view'    => [
                'c' => 'HouseComment',
                'a' => 'reply',
                'str'    => '<a data-height="" data-width="" data-id="add" data-show_btn="false" data-uri="%s" data-title="查看回复" class="J_showDialog layui-btn layui-btn-xs layui-btn-normal" href="javascript:;">查看回复</a>',
                'param' => ['pid'=>'@id@'],
                'isajax' => true,
                'replace'=> ''
            ],
        ];
        $this->assign('options',$this->check());
    }
    public function index()
    {
        $where[] = $this->search();
        $join = [['house h','h.id = c.house_id','left']];
        $field = "c.*,h.title as house_name";
        $lists = model('comment')->alias('c')
                ->join($join)
                ->field($field)
                ->where($where)
                ->order('create_time desc')
                ->paginate(20);
        $this->assign('list',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }
    public function reply()
    {
        $pid = input('param.pid/d',0);
        $where[] = ['c.pid','eq',$pid];
        $join = [['house h','h.id = c.house_id','left']];
        $field = "c.*,h.title as house_name";
        $lists = model('comment')->alias('c')
            ->join($join)
            ->field($field)
            ->where($where)
            ->order('create_time desc')
            ->paginate(20);
        $this->_exclude = 'edit';
        $this->assign('list',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('options',$this->check());
        return $this->fetch();
    }

    /**
     * 删除
     */
    public function delete()
    {
        \app\common\model\Comment::event('after_delete',function($obj){
            model('comment')->where('pid',$obj->id)->delete();
        });
        parent::delete();
    }

    /**
     * @return array
     * 搜索条件
     */
    protected function search()
    {
        $status  = input('get.status');
        $keyword = input('get.keyword');
        $where[] = ['c.pid','eq',0];
        is_numeric($status) && $where[] = ['c.status','eq',$status];
        $keyword && $where[] = ['h.title','like','%'.$keyword.'%'];
        $data = [
            'status' => $status,
            'keyword'=> $keyword
        ];
        $this->queryData = $data;
        $this->assign('search',$data);
        return $where;
    }
}
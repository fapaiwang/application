<?php


namespace app\manage\controller;
use \app\common\controller\ManageBase;

class Nav extends ManageBase
{
//前置操作定义
    protected $beforeActionList = [
        'beforeEdit'  => ['only'=>'edit'],
    ];
    public function initialize(){
        parent::initialize();
    }
    public function index(){
        $this->sort = 'ordid';
        $this->order = 'asc,id desc';
        $big_menu = [
            'title' => '添加导航',
            'iframe' => url('add'),
            'id' => 'add',
            'width' => '500',
            'height' => '500'
        ];
        $this->_exclude = 'delete';
        $this->_ajaxedit = true;
        $lists = model('nav')->order(['ordid'=>'asc','id'=>'desc'])->select();
        $lists = recursion(objToArray($lists));
        $this->assign('list',$lists);
        $this->assign('options',$this->check());
        $this->assign('big_menu', $big_menu);
        return $this->fetch();
    }
    public function beforeEdit(){
        $id      = input('param.id/d',0);
        $spid    = 0;
        if($id){
            $cate_id = model('nav')->where(['id'=>$id])->value('cate_id');
            $detail  = model('pages_cate')->where(['status'=>1,'id'=>$cate_id])->field('id,spid')->find();
            $spid    = !empty($detail['spid'])?$detail['spid'].$detail['id']:$detail['id'];
        }
        $this->assign('spid',$spid);
    }
    public function addDo(){
        \app\common\model\Nav::event('after_insert',function(Nav $nav,$obj){
            $nav->doCache();
        });
        parent::addDo();
    }
    public function editDo(){
        \app\common\model\Nav::event('after_update',function(Nav $nav,$obj){
            $nav->doCache();
        });
        parent::editDo();
    }
    public function delete(){
        \app\common\model\Nav::event('after_delete',function(Nav $nav,$obj){
            $nav->doCache();
        });
        parent::delete();
    }
    /**
     * 更新导航缓存
     */
    public function cache(){
        if($this->doCache()){
            $this->success('导航缓存更新成功');
        }else{
            $this->error('导航缓存更新失败');
        }
    }
    public function doCache(){
        $obj   = model('nav');
        $lists = $obj->where('status',1)->order('ordid asc')->select();
        $lists = list_to_tree(objToArray($lists));
        try{
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
                cache('nav',$temp);
                cache('nav_child',$child);
            }
            return true;
        }catch(\Exception $e){
            \think\facade\Log::write('生成导航缓存出错：'.$e->getMessage());
            return false;
        }
    }
}
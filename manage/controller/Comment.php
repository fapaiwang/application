<?php


namespace app\manage\controller;


use app\common\controller\ManageBase;
use think\facade\Log;

class Comment extends ManageBase
{
    public function initialize()
    {
        parent::initialize();
        $this->_name = 'fydp';
    }
    public function index()
    {
        $where = $this->search();
//        dd($where);
        Log::INFO($where);
//        $join  = [['user b','b.id = c.broker_id']];
//        $field = 'c.*,b.nick_name';
//        //
//        $lists = model('user_comment')->alias('c')->join($join)->field($field)->where($where)->order('c.create_time desc')->paginate(20);
        $join  = [['user u','u.id = f.broker_id'],['second_house s','s.id = f.house_id']];
        $field = 'f.*,u.nick_name,s.title';
        $lists = model('fydp')->alias('f')->join($join)->field($field)->where($where)->paginate(20,false,['query'=>request()->param()]);


        $this->_exclude = 'edit';
        $this->assign('list',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('options',$this->check());
        return $this->fetch();
    }
    public function search(){
        $map = [];
//        $status = input('get.status');
        $admin_id = input('get.admin_id');
        $map['f.user_id'] = $admin_id;
        $data = [
            'broker_id'  => $admin_id,
        ];
        $admin = model('user')->field('id,user_name')->where('model',4)->select();
        $this->queryData = $data;
//        $this->assign('search', $data);
        $this->assign('admin', $admin);
        $this->assign('admin_id', $admin_id);
        return $map;
    }
    public function delete()
    {
        \app\common\model\Comment::event('after_delete',function($obj){
        });
        parent::delete();
    }
      /**
     * 修改评论内容
     */
    public function beforeEdit()
    {
        if($this->request->isPost()){
            $id = input('post.id');
            $house_name = input('post.house_name');
             $arr = model('fydp')->where(array("id"=>$id))->update(['house_name' =>$house_name]);
             $this->success('保存成功','Comment/index');
             

        }else{
            $id = input('param.id/d');
            $join  = [['user u','u.id = f.broker_id'],['second_house s','s.id = f.house_id']];
            $field = 'f.*,u.nick_name,s.title';
            $arr = model('fydp')->alias('f')->join($join)->field($field)->where(array("f.id"=>$id))->find();//7.2sun#分页失效，分页时传递参数
            $this->assign('arr',$arr);
            return $this->fetch();
        }
    }
}
<?php

namespace app\manage\controller;
use app\common\controller\ManageBase;
use app\common\service\ImageServer;

class Article extends ManageBase
{
//前置操作定义
    protected $beforeActionList = [
        // 'second' =>  ['except'=>'hello'],
        'beforeEdit'  =>  ['only'=>'edit'],
    ];
    private $mod;
    private $_cate_mod;
    private $house_id;
    public function initialize(){
        $this->house_id = input('param.house_id/d',0);
        $this->param_extra = ['house_id'=>$this->house_id];
        parent::initialize();
        $this->mod = model('article');
        $this->_cate_mod = model('article_cate');
        $this->view_del = true;
        $this->list_field = 'id,cate_id,title,img,create_time,update_time,status,editor,ordid';
        $this->sort = 'ordid asc,id desc';
    }

    public function search(){
        $map = [];
        $status = input('get.status');
        if(is_numeric($status)){
            $map['status'] = $status;
        }
        $this->house_id && $map['house_id'] = $this->house_id;
        ($keyword = input('get.keyword')) && $map[] = ['title','like', '%'.$keyword.'%'];
        $cate_id = input('get.cate_id');
        $selected_ids = '';
        if ($cate_id) {
            $id_arr = $this->_cate_mod->get_child_ids($cate_id, true);
            $map[] = ['cate_id','in', $id_arr];
            $spid = $this->_cate_mod->where(['id'=>$cate_id])->value('spid');
            $selected_ids = $spid ? $spid . $cate_id : $cate_id;
        }
        $data = [
            'cate_id' => $cate_id,
            'selected_ids' => $selected_ids,
            'status'  => $status,
            'keyword' => $keyword,
            'house_id' => $this->house_id
        ];
        $this->queryData = $data;
        $this->assign('search', $data);
        return $map;
    }
    protected function beforeEdit(){
        $id = input('param.id/d');
        $article = $this->mod->withTrashed()->field('id,house_id,cate_id,label')->where(['id'=>$id])->find();
        $spid = $this->_cate_mod->where(['id'=>$article['cate_id']])->value('spid');
        if( $spid==0 ){
            $spid = $article['cate_id'];
        }else{
            $spid .= $article['cate_id'];
        }
        $house_info = model('house')->getHouseInfo(['id'=>$article['house_id']]);
        if($article['label']){
            $label_list = explode(',',$article['label']);
        }else{
            $label_list = array();
        }
        $this->assign('label_list',$label_list);
        $this->assign('label_num',count($label_list));
        $this->assign('house_info',$house_info);
        $this->assign('selected_ids',$spid);
    }
    //编辑 前置事件
    public function editDo(){
        \app\common\model\Article::event('before_update',function(Article $article,$obj){
            //自动提取摘要
            if($obj->description == '' && isset($obj->info)) {
                $content = stripslashes($obj->info);
                $obj->description = msubstr(str_replace(["'","\r\n","\t",'[page]','[/page]','&ldquo;','&rdquo;','&nbsp;'], '', strip_tags($content)),0,200);
                $obj->description = addslashes($obj->description);
            }
            $obj->cate_alias = $article->_cate_mod->getCateAlias($obj->cate_id);
            //给图片打水印star
            $ImageServer = new ImageServer();
            $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
            preg_match_all($pattern,$obj->info,$match);
            if(!empty($match[1])){
                $info = model('article')->where(['id'=>$obj->id])->value('info');
                preg_match_all($pattern,$info,$match2);
                if(!empty($match2[1])){
                    foreach($match[1] as $k=>$v){
                        foreach($match2[1] as $ki=>$vi){
                            if($v==$vi){
                                unset($match[1][$k]);
                            }
                        }
                    }
                }
                foreach($match[1] as $k=>$v){
                    $ImageServer->ImageWater('../public'.$v,'../public/static/shuiyin/ppshuiyin.png',10);
                }
            }
            //给图片打水印end
            if(!empty($obj->label)){
                $obj->label = implode(',',$obj->label);
            }
        });
        parent::editDo();
    }
    //添加前置事件
    public function addDo(){
        \app\common\model\Article::event('before_insert',function(Article $article,$obj){
            //自动提取摘要
            if($obj->description == '' && isset($obj->info)) {
                $content = stripslashes($obj->info);
                $obj->description = msubstr(str_replace(["'","\r\n","\t",'[page]','[/page]','&ldquo;','&rdquo;','&nbsp;'], '', strip_tags($content)),0,200);
                $obj->description = addslashes($obj->description);
            }
            $obj->cate_alias = $article->_cate_mod->getCateAlias($obj->cate_id);

            $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
            preg_match_all($pattern,$obj->info,$match);
            if(!empty($match[1])){
                //给图片打水印star
                $ImageServer = new ImageServer();
                foreach($match[1] as $k=>$v){
                    $ImageServer->ImageWater('../public'.$v,'../public/static/shuiyin/ppshuiyin.png',10);
                }
                //给图片打水印end
            }
            if(!empty($obj->label)){
                $obj->label = implode(',',$obj->label);
            }
        });
        parent::addDo();
    }
    //回收站
    public function rollBack(){
        $this->_delAction = 'delTure';
        $this->_data = [
            'recovery'    => [
                'c' => 'Article',
                'a' => 'recovery',
                'str'    => '<a data-acttype="ajax" data-uri="%s" data-msg="确定要恢复该条数据么？" class="J_confirm layui-btn layui-btn-xs" href="javascript:;">恢复</a>',
                'param' => ['id'=>'@id@'],
                'isajax' => 1,
                'replace'=> ''
            ]
        ];
        $map = $this->search();
        $list = $this->mod->onlyTrashed()->where($map)->order('delete_time desc,id desc')->paginate(20);//查询软删除数据
        $this->assign('options',$this->check());
        $this->assign('pages',$list->render());
        $this->assign('list',$list);
        return $this->fetch();
    }
    //真实删除
    public function delTure(){
        $this->del_true = true;
        \app\common\model\Article::event('after_delete',function($obj){
            //删除图片
            model('attachment')->deleteAttachment($obj->info,$obj->img);
        });
        parent::delete();
    }
    //恢复
    public function recovery(){
        $pk = $this->mod->getPk();
        $ids = trim(input($pk), ',');
        if ($ids) {
            $edit['delete_time'] = null;
            if($this->mod->onlyTrashed()->where([[$pk,'in',$ids]])->update($edit)){
               $this->success('数据恢复成功');
            }else{echo $this->mod->getLastSql();
               $this->error('数据恢复失败');
            }
        } else {
           $this->error('参数错误');
        }
    }
}
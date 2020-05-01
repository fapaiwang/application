<?php


namespace app\manage\controller;
use app\common\controller\ManageBase;
class CollectionPublish extends ManageBase
{
    public function initialize()
    {
        parent::initialize();
        $this->_name = 'collection_content';
    }
    public function search()
    {
        $status = input('param.status/d',0);
        $id     = input('param.id/d',0);
        $data['status'] = $status;
        $data['c_id']   = $id;
        $this->assign('c_id',$id);
        return $data;
    }
    //删除
    public function delete()
    {
        \app\common\model\CollectionContent::event('after_delete',function($obj){
            $md5 = md5($obj->url);
            model('collection_history')->where('md5',$md5)->delete();
        });
        parent::delete();
    }

    /**
     * 导入选中
     */
    public function import()
    {
        $ids = trim(input('get.id'), ',');
        if($ids)
        {
            $where[]     = ['id','in',$ids];
            $where[]     = ['status','eq',1];
            $content_obj = model('collection_content');
            $lists       = $content_obj->where($where)->select();
            if(!$lists->isEmpty())
            {
                $data = [];
                foreach($lists as $v)
                {
                    $data[] = $v['data'];
                }
                if(model('article')->saveAll($data))
                {
                    $content_obj->save(['status'=>2],$where);
                }
                $this->success('导入成功');
            }else{
                $this->error('暂无可导入项目');
            }
        }else{
            $this->error('请选择要导入的项目');
        }
    }
    public function importAll()
    {
        $c_id = input('param.c_id/d',0);
        $page = input('param.page/d',1);
        $return['code'] = 0;
        $content_obj = model('collection_content');
        $lists = $content_obj->where('c_id',$c_id)->where('status',1)->limit(20)->order('id asc')->select();
        if(!$lists->isEmpty())
        {
            $data = [];
            $ids = [];
            foreach($lists as $v)
            {
                $data[] = $v['data'];
                $ids[]  = $v['id'];
            }
            if(model('article')->saveAll($data))
            {
                $content_obj->save(['status'=>2],[['id','in',$ids]]);
                $return['code'] = 1;
                $return['msg']  = '第'.$page.'页导入完成';
            }
        }else{
            $return['code'] = 2;
            $return['msg'] = '全部导入完成';
        }
        return json($return);
    }
}
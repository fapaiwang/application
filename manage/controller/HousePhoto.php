<?php


namespace app\manage\controller;
use \app\common\controller\ManageBase;

class HousePhoto extends ManageBase
{
    private $house_id;
    private $mod;
    public function initialize(){
        $this->house_id = input('param.house_id/d',0);
        $this->param_extra = ['house_id'=>$this->house_id];
        parent::initialize();
        $info = model('house')->getHouseInfo(['id'=>$this->house_id]);
        $this->mod = model('house_photo');
        $this->assign('houseInfo',$info);
    }
    public function search()
    {
        $where['house_id'] = $this->house_id;
        $keyword = input('get.keyword');
        $cate    = input('get.cate/d',0);
        $status  = input('get.status');
        if(is_numeric($status))
        {
            $where[] = ['status','eq',$status];
        }
        $cate && $where[] = ['cate_id','eq',$cate];
        $keyword && $where[] = ['title','like','%'.$keyword.'%'];

        $data = [
            'house_id' => $this->house_id,
            'keyword'  => $keyword,
            'status'   => $status,
            'cate'     => $cate
        ];
        $this->queryData = $data;
        $this->assign('search',$data);
        return $where;
    }
    //添加图片
    public function addDo(){
        $this->addPic();
    }
    //删除图片
    public function delete(){
        \app\common\model\HousePhoto::event('after_delete',function($obj){
            model('attachment')->deleteAttachment('',$obj->url);
        });
        parent::delete();
    }
    /**
     * @param $obj
     * 添加图片
     */
    private function addPic(){
        $house_id = input('post.house_id');
        $cate_id  = input('post.cate_id');
        $status   = input('post.status/d',1);
        $cate_name = input('post.cate_name');
        if(isset($_POST['pic']) && !empty($_POST['pic'])) {
            $insert = [];
            $images = $_POST['pic'];
            foreach ($images as $key => $v) {
                $insert[] = [
                    'url' => $v['pic'],
                    'title' => $v['alt'],
                    'create_time'=>time(),
                    'house_id' => $house_id,
                    'cate_id' => $cate_id,
                    'status' => $status,
                    'cate_name' => $cate_name
                ];
            }
            if($insert){
                if($this->mod->saveAll($insert)){
                    $this->success('添加图片成功');
                }else{
                    $this->error('添加图片失败');
                }
            }
        }else{
            $this->error('请选择图片');
        }
    }
}
<?php


namespace app\manage\controller;
use \app\common\controller\ManageBase;
class Position extends ManageBase
{
    private $models;
    private $cate_id;
    //前置操作定义
    protected $beforeActionList = [
        'beforeIndex' =>  ['only'=>'index'],
    ];
    public function initialize(){
        parent::initialize();
        $this->cate_id = input('param.pos_id/d',0);
        $this->getPositionCateInfo($this->cate_id);
        $this->models = getPostionModel();
        $this->assign('models',$this->models);
        $this->sort = 'ordid';
        $this->order = 'asc,id desc';
    }
    public function beforeIndex()
    {
        $this->_exclude = 'edit';
    }
    public function search(){
        $where['cate_id'] = $this->cate_id;
        return $where;
    }
    //添加操作
    public function addDo(){
        $model   = input('post.model');
        $ids     = input('post.house_id');
        $cate_id = input('post.cate_id');
        if($ids){
            if($this->addPosition($model,$ids,$cate_id)){
                return $this->ajaxReturn(1,'添加成功','');
            }else{
                return $this->ajaxReturn(0,'添加失败');
            }
        }else{
           return $this->ajaxReturn(0,'请选择楼盘');
        }

    }
    public function addPosition($model,$ids,$cate_id){
        $data = $this->getInfoByModel($model,$ids,$cate_id);
        if($result=model('position')->saveAll($data)){
            if(is_numeric($ids)){
                $num = 1;
            }else{
                $num = count($result);
                model($model)->save(['rec_position'=>1],[['id','in',trim($ids,',')]]);//更新楼盘推荐位状态
            }
            model('position_cate')->where([['id','in',$cate_id]])->setInc('num',$num);//更新推荐位内容计数
            return true;
        }else{
            return false;
        }
    }
    public function delete(){
        \app\common\model\Position::event('after_delete',function($obj){
            $count = model('position')->where(['house_id'=>$obj->house_id])->count();
            if($count == 0){
                //如果 不存在该楼盘的推荐，则更改楼盘推荐状态为0
                model($obj->model)->save(['rec_position'=>0],['id'=>$obj->house_id]);
            }
            $where['id'] = $obj->cate_id;
            $where[] = ['num','gt',0];
            model('position_cate')->where($where)->setDec('num');
        });
        parent::delete();
    }
    //通过楼盘id删除
    public function deleteByHouseId($house_id,$model='house'){
        $all_cate = model('position')->where(['house_id'=>$house_id])->column('cate_id');
        if($all_cate){
            model('position')->where(['house_id'=>$house_id,'model'=>$model])->delete();
            $where[] = ['id','in',$all_cate];
            $where[] = ['num','gt',0];
            model('position_cate')->where($where)->setDec('num');
        }
    }
    //获取信息
    /**
     * @param $model 表名
     * @param $ids id字符串
     */
    private function getInfoByModel($model,$ids,$cate_id){
        $obj = model($model);
        $data  = [];
        if(is_numeric($ids) && is_array($cate_id)){
            $info = $obj->where(['id'=>$ids])->field('id,title')->find();
            if($info){
                foreach($cate_id as $v){
                    $data[] = [
                        'house_id' => $info['id'],
                        'title'    => $info['title'],
                        'model'    => $model,
                        'cate_id'  => $v
                    ];
                }
            }
        }else{
            $lists = $obj->where([['id','in',trim($ids,',')]])->field('id,title')->select();
            if($lists){
                foreach($lists as $v){
                    $data[] = [
                        'house_id' => $v['id'],
                        'title'    => $v['title'],
                        'model'    => $model,
                        'cate_id'  => $cate_id,
                    ];
                }
            }
        }
        return $data;
    }
    //获取推荐位信息
    private function getPositionCateInfo($cate_id){
        if($cate_id)
        {
            $model = model('position_cate')->where(['id'=>$cate_id])->value('model');
            $this->assign('model',$model);
            $this->assign('cate_id',$cate_id);
        }
    }
}
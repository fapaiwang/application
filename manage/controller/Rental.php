<?php

namespace app\manage\controller;
use \app\common\controller\ManageBase;
class Rental extends ManageBase
{
    private $model = 'rental';
    protected $beforeActionList = [
        'beforeEdit' => ['only'=>['edit']],
        'beforeAdd' => ['only'=>['add']],
        'beforeIndex'=> ['only'=>['index']]
    ];
    protected function beforeIndex()
    {
        $this->sort = ['ordid'=>'asc','id'=>'desc'];
    }
    /**
     * @return array
     * 搜索条件
     */
    public function search()
    {
        $status  = input('get.status');
        $keyword = input('get.keyword');
        $where   = [];
        is_numeric($status) && $where['status'] = $status;
        $keyword && $where[] = ['title','like','%'.$keyword.'%'];
        $data = [
            'status' => $status,
            'keyword'=> $keyword
        ];
        $this->queryData = $data;
        $this->assign('search',$data);
        return $where;
    }
    public function beforeAdd()
    {
        $position_lists = \app\manage\service\Position::lists($this->model);
        $this->assign('position_lists',$position_lists);
    }
    public function beforeEdit()
    {
        $id  = input('param.id/d',0);
        if(!$id)
        {
            $this->error('参数错误');
        }
        $data = model('rental_data')->where(['house_id'=>$id])->find();
        $position_lists = \app\manage\service\Position::lists($this->model);
        $house_position_cate_id = \app\manage\service\Position::getPositionIdByHouseId($id,$this->model);
        $this->assign('position_lists',$position_lists);
        $this->assign('position_cate_id',$house_position_cate_id);
        $this->assign('data',$data);
    }
    /**
     * 添加
     */
    public function addDo()
    {
        $data = input('post.');
        $result = $this->validate($data,'Rental');//调用验证器验证
        $code   = 0;
        $msg    = '';
        $obj = model('rental');
        if(true !== $result)
        {
            // 验证失败 输出错误信息
            $this->error($result);
        }else{
            \think\Db::startTrans();
            try{
                !empty($data['map']) && $location = explode(',',$data['map']);
               // $data['house_type'] = isset($data['house_type']) ? implode(',',$data['house_type']) : 0;
                $data['lng']     = isset($location[0]) ? $location[0] : 0;
                $data['lat']     = isset($location[1]) ? $location[1] : 0;
                if (isset($data['position'])) {
                    $data['rec_position'] = 1;
                }
                $data['file'] = $this->getPic();
                (empty($data['img']) && !empty($data['file'])) && $data['img'] = $data['file'][0]['url'];
                if($obj->allowField(true)->save($data))
                {
                    $house_id = $obj->id;
                    $this->optionHouseData($house_id,$data);
                    $this->addHousePrice($house_id,$data['price']);
                    \app\manage\service\Position::option($house_id,$this->model);
                    $msg = '添加房源信息成功';
                    $code = 1;
                    \org\Relation::addSchool('rental',$data['lng'],$data['lat'],$house_id,$data['city']);
                    \org\Relation::addMetro('rental',$data['lng'],$data['lat'],$house_id,$data['city']);
                }else{
                    $msg = '添加房源信息失败';
                }

                \think\Db::commit();
            }catch(\Exception $e){
                \think\facade\Log::record('添加房源信息出错：'.$e->getMessage());
                \think\Db::rollback();
                $msg = $e->getMessage();
            }
        }
        if($code == 1)
        {
            $this->success($msg);
        }else{
            $this->error($msg);
        }
    }
    /**
     * 编辑
     */
    public function editDo()
    {
        $data = input('post.');
        $result = $this->validate($data,'Rental');//调用验证器验证
        $code   = 0;
        $msg    = '';
        $obj = model('rental');
        $url = null;
        isset($data['refer']) && $url = $data['refer'];
        if(true !== $result)
        {
            // 验证失败 输出错误信息
            $this->error($result);
        }elseif(!$data['id']){
            $this->error('参数错误');
        }else{
            \think\Db::startTrans();
            try{
                !empty($data['map']) && $location = explode(',',$data['map']);
               // $data['house_type'] = isset($data['house_type']) ? implode(',',$data['house_type']) : 0;
                $data['lng']     = isset($location[0]) ? $location[0] : 0;
                $data['lat']     = isset($location[1]) ? $location[1] : 0;
                if (!isset($data['position'])) {
                    $obj->rec_position = 0;
                } else {
                    $obj->rec_position = 1;
                }
                $data['file'] = $this->getPic();
                (empty($data['img']) && !empty($data['file'])) && $data['img'] = $data['file'][0]['url'];
                $data['ratio'] = $this->addHousePrice($data['id'],$data['price']);
                if($obj->allowField(true)->save($data,['id'=>$data['id']]))
                {
                    $this->optionHouseData($data['id'],$data,true);
                    \app\manage\service\Position::option($data['id'],$this->model);
                    \org\Relation::addSchool('rental',$data['lng'],$data['lat'],$data['id'],$data['city']);
                    \org\Relation::addMetro('rental',$data['lng'],$data['lat'],$data['id'],$data['city']);
                }
                $msg = '编辑房源信息成功';
                $code = 1;
                \think\Db::commit();
            }catch(\Exception $e){
                \think\facade\Log::record('编辑房源信息出错：'.$e->getMessage());
                \think\Db::rollback();
                $msg = $e->getMessage();
            }
        }
        if($code == 1)
        {
            $this->success($msg,$url);
        }else{
            $this->error($msg,$url);
        }
    }
    public function delete()
    {
        \app\common\model\Rental::event('after_delete',function($obj){
            //删除扩展数据
            $mod = model('rental_data');
            $where = ['house_id'=>$obj->id];
            $info= $mod->where($where)->find();
            if($mod->where($where)->delete())
            {
                model('attachment')->deleteAttachment($info['info'],$obj->img,$info['file']);
            }
            //删除价格数据
            db('house_price')->where(['house_id'=>$obj->id,'model'=>$this->model])->delete();
            //删除推荐数据
            action('manage/Position/deleteByHouseId', ['house_id'=>$obj->id,'model'=>$this->model], 'controller');
            //删除地铁关联数据
            \org\Relation::deleteByHouse($obj->id,'rental');
            //删除学校关联数据
            \org\Relation::deleteByHouse($obj->id,'rental','school');
        });
        parent::delete();
    }
    /**
     * @param $house_id
     * @param $data
     * 添加扩展数据
     */
    private function optionHouseData($house_id,$data,$update = false)
    {
        if (empty($data['seo']['seo_title'])) {
            $info['seo_title'] = $data['title'];
        }else{
            $info['seo_title'] = $data['seo']['seo_title'];
        }
        $info['supporting'] = isset($data['supporting'])?implode(',',$data['supporting']):0;
        $info['house_id']  = $house_id;
        $info['info']      = isset($data['info']) ? $data['info'] : '';
        $info['seo_keys']  = $data['seo']['seo_keys'];
        $info['seo_desc']  = $data['seo']['seo_desc'];
        $info['file']      = $data['file'];//$this->getPic();
        if($update)
        {
            model('rental_data')->allowField(true)->save($info,['house_id'=>$house_id]);
        }else{
            model('rental_data')->allowField(true)->save($info);
        }

    }

    /**
     * @param $house_id
     * @param $price
     * 添加价格
     */
    private function addHousePrice($house_id,$price)
    {
        $priceObj  = model('house_price');
        $rate = 0;
        //读取上一次价格
        $prev_price = $priceObj->where(['house_id'=>$house_id,'model'=>$this->model])->order('create_time desc')->value('price');
        if($prev_price != $price && intval($price) > 0)
        {
            $data['price'] = $price;
            $data['create_time'] = time();
            $data['house_id'] = $house_id;
            $data['model']    = 'rental';
            //计算涨幅比
            $prev_price && $rate = number_format((($price - $prev_price) / $prev_price) * 100,1);
            $priceObj->insert($data);
        }
        return $rate;
    }
    /**
     * @param $obj
     * 添加图片
     */
    private function getPic(){
        $insert = [];
        if(isset($_POST['pic']) && !empty($_POST['pic'])) {
            $images = $_POST['pic'];
            foreach ($images as $key => $v) {
                $insert[] = [
                    'url' => $v['pic'],
                    'title' => $v['alt'],
                ];
            }
        }
        return $insert;
    }
}
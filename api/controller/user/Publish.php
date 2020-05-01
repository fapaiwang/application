<?php
namespace app\api\controller\user;
use app\common\service\PublishCount;
class Publish extends UserBase
{
   private $allow = ['second_house','rental'];
    public function index()
    {
        return;
    }

    /**
     * 保存二手房数据
     */
    public function saveSecond()
    {
        $data = input('post.');
        $obj = model('second_house');
        $return['code'] = 0;
        \think\Db::startTrans();
        try{
            $status = getSettingCache('user','check_second');//读取配置 是否需要审核
            $data['contacts'] = ['contact_name'=>$data['contact_name'],'contact_phone'=>$data['contact_phone']];
            $data['average_price'] = 0;
            $data['broker_id'] = $this->userInfo['id'];
            $data['user_type'] = $this->userInfo['model'];
            $data['status']    = is_numeric($status)?$status:1;
            if($data['price']>0 && $data['acreage']>0)
            {
                $data['average_price'] = ceil($data['price'] * 10000 / $data['acreage']);
            }
            $data['file'] = isset($data['file'])?json_decode($data['file'],true):'';
            unset($data['contact_name'],$data['contact_phone']);
            if(isset($data['id']) && $data['id'])
            {
                if(isset($data['timeout']) && $data['timeout']) {
                    $check = PublishCount::check($this->userInfo['id'], $this->userInfo['model']);
                    if($check['code'] == 1)
                    {
                        (empty($data['img']) && !empty($data['file'])) && $data['img'] = $data['file'][0]['url'];
                        $data['ratio'] = $this->addHousePrice($data['id'], $data['price']);
                        if ($obj->allowField(true)->save($data, ['id' => $data['id']])) {
                            $this->optionHouseData($data['id'], $data, 'second_house', true);
                        }
                        $return['code'] = 200;
                        $return['msg']  = isset($check['msg'])?'上架成功！'.$check['msg']:'编辑房源信息成功';
                    }else{
                        $return['msg'] = $check['msg'];
                    }
                }else{
                    (empty($data['img']) && !empty($data['file'])) && $data['img'] = $data['file'][0]['url'];
                    $data['ratio'] = $this->addHousePrice($data['id'], $data['price']);
                    if ($obj->allowField(true)->save($data, ['id' => $data['id']])) {
                        $this->optionHouseData($data['id'], $data, 'second_house', true);
                    }
                    $return['code'] = 200;
                    $return['msg']  = '编辑房源信息成功';
                }
            }else{
                $check = PublishCount::check($this->userInfo['id'],$this->userInfo['model']);
                if($check['code'] == 1)
                {
                    (empty($data['img']) && !empty($data['file'])) && $data['img'] = $data['file'][0]['url'];
                    if($obj->allowField(true)->save($data))
                    {
                        $house_id = $obj->id;
                        $this->optionHouseData($house_id,$data);
                        $this->addHousePrice($house_id,$data['price']);
                    }
                    $return['code'] = 200;
                    $return['msg'] = isset($check['msg'])?'发布成功！'.$check['msg']:'添加房源信息成功';
                }else{
                    $return['msg'] = $check['msg'];
                }

            }
            \think\Db::commit();
        }catch(\Exception $e){
            \think\facade\Log::write('添加房源信息出错：'.$e->getMessage(),'error');
            \think\Db::rollback();
            $return['msg'] = $e->getMessage();
        }
        return json($return);
    }

    /**
     * @return \think\response\Json
     * 保存出租房数据
     */
    public function saveRental()
    {
        $data = input('post.');
        $obj  = model('rental');
        $return['code'] = 0;
        \think\Db::startTrans();
        try{
            $status = getSettingCache('user','check_rental');//读取配置 是否需要审核
            $data['contacts']  = ['contact_name'=>$data['contact_name'],'contact_phone'=>$data['contact_phone']];
            $data['broker_id'] = $this->userInfo['id'];
            $data['user_type'] = $this->userInfo['model'];
            $data['status']    = is_numeric($status)?$status:1;
            $data['file'] = isset($data['file']) ? json_decode($data['file'],true) : '';
            if(isset($data['id']) && $data['id'])
            {
                if(isset($data['timeout']) && $data['timeout']) {
                    $check = PublishCount::check($this->userInfo['id'], $this->userInfo['model']);
                    if($check['code'] == 1)
                    {
                        (empty($data['img']) && !empty($data['file'])) && $data['img'] = $data['file'][0]['url'];
                        $data['ratio'] = $this->addHousePrice($data['id'], $data['price'], 'rental');
                        if ($obj->allowField(true)->save($data, ['id' => $data['id']])) {
                            $this->optionHouseData($data['id'], $data, 'rental', true);
                        }
                        $return['code'] = 200;
                        $return['msg']  = isset($check['msg'])?'上架成功！'.$check['msg']:'编辑房源信息成功';
                    }else{
                        $return['msg'] = $check['msg'];
                    }
                }else{
                    (empty($data['img']) && !empty($data['file'])) && $data['img'] = $data['file'][0]['url'];
                    $data['ratio'] = $this->addHousePrice($data['id'], $data['price'], 'rental');
                    if ($obj->allowField(true)->save($data, ['id' => $data['id']])) {
                        $this->optionHouseData($data['id'], $data, 'rental', true);
                    }
                    $return['code'] = 200;
                    $return['msg']  = '编辑房源信息成功';
                }
            }else{
                $check = PublishCount::check($this->userInfo['id'],$this->userInfo['model']);
                if($check['code'] == 1)
                {
                    (empty($data['img']) && !empty($data['file'])) && $data['img'] = $data['file'][0]['url'];
                    if($obj->allowField(true)->save($data))
                    {
                        $house_id = $obj->id;
                        $this->optionHouseData($house_id,$data,'rental');
                        $this->addHousePrice($house_id,$data['price'],'rental');
                    }
                    $return['code'] = 200;
                    $return['msg']  = isset($check['msg'])?'发布成功！'.$check['msg']:'添加房源信息成功';
                }else{
                    $return['msg'] = $check['msg'];
                }

            }
            \think\Db::commit();
        }catch(\Exception $e){
            \think\facade\Log::write('添加房源信息出错：'.$e->getMessage(),'error');
            \think\Db::rollback();
            $return['msg'] = $e->getMessage();
        }
        return json($return);
    }

    /**
     * @return \think\response\Json
     * 删除房源数据
     */
    public function delete()
    {
        $id    = input('delete.id/d',0);
        $model = input('delete.model','second_house');
        $return['code'] = 0;
        if(!$id)
        {
            $return['msg'] = '参数错误';
        }elseif(!in_array($model,$this->allow)){
            $return['msg'] = '不允许的数据模型';
        }else{
            $where['id']        = $id;
            $where['broker_id'] = $this->userInfo['id'];
            $obj  = model($model);
            $info = $obj->where($where)->find();
            if($obj->where($where)->delete())
            {
                $this->deleteExtra($info,$model);
                $return['code'] = 200;
                $return['msg'] = '删除成功';
            }else{
                $return['msg'] = '删除失败';
            }
        }
        return json($return);
    }

    /**
     * @param $info
     * @param $model
     * 删除扩展数据
     */
    private function deleteExtra($info,$model)
    {
        $extra = model($model.'_data');
        $where = ['house_id'=>$info['id']];
        $detail = $extra::get($where);
        //删除房源图片
        if($extra->where($where)->delete())
        {
            model('attachment')->deleteAttachment($detail['info'],$info['img'],$detail['file']);
        }
        //model('attachment')->deleteVideo($info['video']);//删除视频
        //删除价格数据
        db('house_price')->where(['house_id'=>$info['id'],'model'=>$model])->delete();
        //删除地铁关联数据
        \org\Relation::deleteByHouse($info['id'],$model);
        //删除学校关联数据
        \org\Relation::deleteByHouse($info['id'],$model,'school');
    }

    /**
     * @param $house_id
     * @param $data
     * 添加扩展数据
     */
    private function optionHouseData($house_id,$data,$model = 'second_house',$update = false)
    {
        $info['seo_title'] = $data['title'];
        $info['seo_keys']  = $data['estate_name'];
        $info['house_id']  = $house_id;
        $info['info']      = isset($data['info']) ? $data['info'] : '';
        $info['file']      = isset($data['file']) ? $data['file'] : '';
        isset($data['supporting']) && $info['supporting'] = $data['supporting'];
        if($update)
        {
            model($model.'_data')->allowField(true)->save($info,['house_id'=>$house_id]);
        }else{
            model($model.'_data')->allowField(true)->save($info);
        }
        //关联学校
        \org\Relation::addSchool($model,$data['lng'],$data['lat'],$house_id,$data['city']);
        //关联地铁站
        \org\Relation::addMetro($model,$data['lng'],$data['lat'],$house_id,$data['city']);
    }

    /**
     * @param $house_id
     * @param $price
     * 添加价格
     */
    private function addHousePrice($house_id,$price,$model='second_house')
    {
        $priceObj  = model('house_price');
        $rate = 0;
        //读取上一次价格
        $prev_price = $priceObj->where(['house_id'=>$house_id,'model'=>'second_house'])->order('create_time desc')->value('price');
        if($prev_price != $price && intval($price) > 0)
        {
            $data['price'] = $price;
            $data['create_time'] = time();
            $data['house_id'] = $house_id;
            $data['model']    = $model;
            //计算涨幅比
            $prev_price && $rate = number_format((($price - $prev_price) / $prev_price) * 100,1);
            $priceObj->removeOption();
            $priceObj->save($data);
        }
        return $rate;
    }
}
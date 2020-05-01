<?php
namespace app\agent\controller;
use \app\common\controller\AgentBase;
class SecondHouse extends AgentBase
{
    private $model = 'second_house';
    protected $beforeActionList = [
        'beforeEdit' => ['only'=>['edit']],
    ];
    public function initialize(){
        parent::initialize();
        $storage = getSettingCache('storage');
        $this->assign('storage',$storage);
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
    public function beforeEdit()
    {
        $id  = input('param.id/d',0);
        if(!$id)
        {
            $this->error('参数错误');
        }
        $data = model('second_house_data')->where(['house_id'=>$id])->find();
        $this->assign('data',$data);
    }
    /**
     * 添加
     */
    public function addDo()
    {
        $data = input('post.');
        $result = $this->validate($data,'SecondHouse');//调用验证器验证
        $code   = 0;
        $msg    = '';
        $obj = model('second_house');
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
                $data['average_price'] = 0;
                $data['tags'] = isset($data['tags']) ? implode(',',$data['tags']) : 0;
                $data['agent_id'] = $this->agentId;
                if($data['price']>0 && $data['acreage']>0)
                {
                    $data['average_price'] = ceil($data['price'] * 10000 / $data['acreage']);
                }
                $data['img']  = $this->uploadImg();
                $data['file'] = $this->getPic();
                (empty($data['img']) && !empty($data['file'])) && $data['img'] = $data['file'][0]['url'];
                if($obj->allowField(true)->save($data))
                {
                    $house_id = $obj->id;
                    $this->optionHouseData($house_id,$data);
                    $this->addHousePrice($house_id,$data['average_price']);
                    \app\manage\service\Price::calculationPrice($data['estate_id']);
                    //关联学校
                    \org\Relation::addSchool('second_house',$data['lng'],$data['lat'],$house_id,$data['city']);
                    //关联地铁站
                    \org\Relation::addMetro('second_house',$data['lng'],$data['lat'],$house_id,$data['city']);
                    $msg = '添加房源信息成功';
                    $code = 1;
                }else{
                    $msg = '添加房源信息失败！';
                }
                \think\Db::commit();
            }catch(\Exception $e){
                \think\facade\Log::record('添加房源信息出错：'.$e->getFile().$e->getLine().$e->getMessage());
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
        $result = $this->validate($data,'SecondHouse');//调用验证器验证
        $code   = 0;
        $msg    = '';
        $obj = model('second_house');
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
                //$data['house_type'] = isset($data['house_type']) ? implode(',',$data['house_type']) : 0;
                $data['lng']     = isset($location[0]) ? $location[0] : 0;
                $data['lat']     = isset($location[1]) ? $location[1] : 0;
                $data['tags'] = isset($data['tags']) ? implode(',',$data['tags']) : 0;
                $data['average_price'] = 0;
                if($data['price']>0 && $data['acreage']>0)
                {
                    //计算均价
                    $data['average_price'] = ceil($data['price'] * 10000 / $data['acreage']);
                }
                $img = $this->uploadImg();
                if($img)
                {
                    $data['img'] = $img;
                }
                $data['file'] = $this->getPic();
                (empty($data['img']) && !empty($data['file'])) && $data['img'] = $data['file'][0]['url'];
                $data['ratio'] = $this->addHousePrice($data['id'],$data['average_price']);
                if($obj->allowField(true)->save($data,['id'=>$data['id'],'agent_id'=>$this->agentId]))
                {
                    $this->optionHouseData($data['id'],$data,true);
                    \app\manage\service\Price::calculationPrice($data['estate_id']);
                    //关联学校
                    \org\Relation::addSchool('second_house',$data['lng'],$data['lat'],$data['id'],$data['city']);
                    //关联地铁站
                    \org\Relation::addMetro('second_house',$data['lng'],$data['lat'],$data['id'],$data['city']);
                    $msg = '编辑房源信息成功';
                    $code = 1;
                }else{
                    $msg = '编辑房源信息失败！';
                }

                \think\Db::commit();
            }catch(\Exception $e){
                \think\facade\Log::record('编辑房源信息出错：'.$e->getFile().$e->getLine().$e->getMessage());
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
    public function delete()
    {
        \app\common\model\SecondHouse::event('after_delete',function($obj){
            //删除扩展数据
            $mod = model('second_house_data');
            $where = ['house_id'=>$obj->id];
            $info= $mod->where($where)->find();
            if($mod->where($where)->delete())
            {
                model('attachment')->deleteAttachment($info['info'],$obj->img,$info['file']);//删除图片
            }
            model('attachment')->deleteVideo($obj->video);//删除视频
            //删除价格数据
            db('house_price')->where(['house_id'=>$obj->id,'model'=>'second_house'])->delete();
            //删除地铁关联数据
            \org\Relation::deleteByHouse($obj->id,'second_house');
            //删除学校关联数据
            \org\Relation::deleteByHouse($obj->id,'second_house','school');
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
            model('second_house_data')->allowField(true)->save($info,['house_id'=>$house_id]);
        }else{
            model('second_house_data')->allowField(true)->save($info);
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
        $prev_price = $priceObj->where(['house_id'=>$house_id,'model'=>'second_house'])->order('create_time desc')->value('price');
        if($prev_price != $price)
        {
            $data['price'] = $price;
            $data['create_time'] = time();
            $data['house_id'] = $house_id;
            $data['model']    = 'second_house';
            //计算涨幅比
            $prev_price && $rate = number_format((($price - $prev_price) / $prev_price) * 100,1);
            //$obj->removeOption();
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
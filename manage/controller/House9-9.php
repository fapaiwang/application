<?php


namespace app\manage\controller;
use app\common\controller\ManageBase;
class House extends ManageBase
{
    private $house_id;
    protected $beforeActionList = [
        'beforeIndex' => ['only'=>['index']],
        'beforeAdd'   => ['only'=>['add']],
        'beforeEdit'  => ['only'=>['edit']]
    ];
    public function initialize(){
        $this->house_id = input('param.house_id/d',0);
        $this->param_extra = ['house_id'=>$this->house_id];
        parent::initialize();
        $storage = getSettingCache('storage');
        $this->assign('storage',$storage);
    }
    public function beforeIndex()
    {
        $this->list_field = 'id,city,title,img,price,video,red_packet,unit,create_time,update_time,status,ordid,rec_position';
        $this->sort = ['ordid'=>'asc','id'=>'desc'];
        $this->_data = [
            'addhuxing' => [
                'c' => 'HouseType',
                'a' => 'add',
                'str' => '<a href="%s" class="layui-btn layui-btn-xs">户型</a>',
                'param' => ['house_id' => '@id@', 'menuid' => 466],
                'isajax' => 0,
                'replace' => ''
            ],
            'addpic' => [
                'c' => 'HousePhoto',
                'a' => 'add',
                'str' => '<a href="%s" class="layui-btn layui-btn-xs">相册</a>',
                'param' => ['house_id' => '@id@', 'menuid' => 472],
                'isajax' => 0,
                'replace' => ''
            ],
            'addnews' => [
                'c' => 'HouseNews',
                'a' => 'add',
                'str' => '<a href="%s" class="layui-btn layui-btn-xs">动态</a><br />',
                'param' => ['house_id' => '@id@', 'menuid' => 476],
                'isajax' => 0,
                'replace' => ''
            ],
            'property' => [
                'c' => 'HouseSand',
                'a' => 'index',
                'str' => '<a href="%s" class="layui-btn layui-btn-xs">沙盘</a>',
                'param' => ['house_id' => '@id@','menuid'=>480],
                'isajax' => 0,
                'replace' => ''
            ],
        ];
    }
    public function beforeAdd()
    {
        $obj = model('house');
        $unit = $obj->getUnitData();
        $position_lists = \app\manage\service\Position::lists();
        $this->assign('position_lists',$position_lists);
        $this->assign('unit',$unit);
    }
    public function beforeEdit()
    {
        $id  = input('param.id/d',0);
        if(!$id)
        {
            $this->error('参数错误');
        }
        $obj = model('house');
        $unit = $obj->getUnitData();
        $this->assign('unit',$unit);
        $data = model('house_data')->where(['house_id'=>$id])->find();
        $position_lists = \app\manage\service\Position::lists();
        $house_position_cate_id = \app\manage\service\Position::getPositionIdByHouseId($id);
        $this->assign('position_lists',$position_lists);
        $this->assign('position_cate_id',$house_position_cate_id);
        $this->assign('data',$data);
    }

    /**
     * @return array
     * 搜索条件
     */
    public function search()
    {
        $city    = input('get.city/d',0);
        $status  = input('get.status');
        $keyword = input('get.keyword');
        $where   = [];
        is_numeric($status) && $where['status'] = $status;
        $keyword && $where[] = ['title','like','%'.$keyword.'%'];
        if($city)
        {
            $city_child = model('city')->get_child_ids($city,true);
            $where[] = ['city','in',$city_child];
        }
        $data = [
            'city' => $city,
            'status' => $status,
            'keyword'=> $keyword
        ];
        $this->queryData = $data;
        $this->assign('search',$data);
        return $where;
    }
    /*
     * 添加
     */
    public function addDo()
    {
        $data = input('post.');
        $result = $this->validate($data,'House');//调用验证器验证
        $code   = 0;
        $msg    = '';
        $obj = model('house');
        if(true !== $result)
        {
            // 验证失败 输出错误信息
            $this->error($result);
        }elseif($obj->checkTitleExists($data['title'])){
            $this->error('该楼盘已存在！');
        }else{
            \think\Db::startTrans();
            try{
                !empty($data['map']) && $location = explode(',',$data['map']);
                $data['type_id'] = isset($data['type_id']) ? implode(',',$data['type_id']) : 0;
                $data['tags_id'] = isset($data['tags_id']) ? implode(',',$data['tags_id']) : 0;
                $data['lng']     = isset($location[0]) ? $location[0] : 0;
                $data['lat']     = isset($location[1]) ? $location[1] : 0;
                $data['opening_time']  = !empty($data['opening_time'])?strtotime($data['opening_time']):0;
                $data['complate_time'] = !empty($data['complate_time'])?strtotime($data['complate_time']):0;
                if (isset($data['position'])) {
                    $data['rec_position'] = 1;
                }
                if($obj->allowField(true)->save($data))
                {
                    $house_id = $obj->id;
                    $this->addHouseData($house_id,$data);
                    $this->addHousePrice($house_id,$data['price']);
                    \app\manage\service\Position::option($house_id);
                    \org\Relation::addSchool('house',$data['lng'],$data['lat'],$house_id,$data['city']);
                    \org\Relation::addMetro('house',$data['lng'],$data['lat'],$house_id,$data['city']);
                }
                $msg = '添加楼盘信息成功';
                $code = 1;
               \think\Db::commit();
           }catch(\Exception $e){
                \think\facade\Log::record('添加楼盘出错：'.$e->getFile().$e->getLine().$e->getMessage());
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
        $data   = input('post.');
        $result = $this->validate($data,'House');//调用验证器验证
        $code   = 0;
        $msg    = '';
        $obj    = model('house');
        $url = null;
        isset($data['refer']) && $url = $data['refer'];
        if(true !== $result)
        {
            // 验证失败 输出错误信息
            $this->error($result);
        }elseif(!$data['id']){
            $this->error('参数错误');
        }elseif($obj->checkTitleExists($data['title'],$data['id'])){
            $this->error('该楼盘已存在！');
        }else{
            \think\Db::startTrans();
            try{
                !empty($data['map']) && $location = explode(',',$data['map']);
                $data['type_id'] = isset($data['type_id']) ? implode(',',$data['type_id']) : 0;
                $data['tags_id'] = isset($data['tags_id']) ? implode(',',$data['tags_id']) : 0;
                $data['lng']     = isset($location[0]) ? $location[0] : 0;
                $data['lat']     = isset($location[1]) ? $location[1] : 0;
                !isset($data['is_discount']) && $data['is_discount'] = 0;
                $data['opening_time']  = !empty($data['opening_time'])?strtotime($data['opening_time']):0;
                $data['complate_time'] = !empty($data['complate_time'])?strtotime($data['complate_time']):0;
                if (!isset($data['position'])) {
                    $obj->rec_position = 0;
                } else {
                    $obj->rec_position = 1;
                }
                //添加价格并计算涨幅比
                $rate = $this->addHousePrice($data['id'],$data['price']);
                $rate && $data['ratio'] = $rate;
                if($obj->allowField(true)->save($data,['id'=>$data['id']]))
                {
                    $this->updateHouseData($data['id'],$data);
                    \app\manage\service\Position::option($data['id']);
                    \org\Relation::addSchool('house',$data['lng'],$data['lat'],$data['id'],$data['city']);
                    \org\Relation::addMetro('house',$data['lng'],$data['lat'],$data['id'],$data['city']);
                }
                $msg = '编辑楼盘信息成功';
                $code = 1;
                \think\Db::commit();
            }catch(\Exception $e){
                \think\facade\Log::record('编辑楼盘出错：'.$e->getFile().$e->getLine().$e->getMessage());
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
        \app\common\model\House::event('after_delete',function($obj){
            $where = ['house_id'=>$obj->id];
            //删除扩展数据
            $mod = model('house_data');
            $info = $mod->where($where)->find();
            //删除户型
            model('house_type')->where($where)->delete();
            //删除相册并同时删除图片
            $mod_photo = model('house_photo');
            $photo_lists = $mod_photo->where($where)->field('url')->select();
            $mod_photo->where($where)->delete();
            //删除沙盘
            model('house_sand')->where($where)->delete();
            //删除沙盘图片
            model('house_sand_pic')->where($where)->delete();
            //删除搜索关联数据
            db('house_search')->where($where)->delete();
            //删除价格数据
            db('house_price')->where(['house_id'=>$obj->id,'model'=>'house'])->delete();
            model('attachment')->deleteAttachment($info['info'],$obj->img,$photo_lists);
            model('attachment')->deleteVideo($obj->video);
            //删除推荐楼盘数据
            action('Position/deleteByHouseId', $where, 'controller');
            //删除地铁关联数据
            \org\Relation::deleteByHouse($obj->id,'house');
            //删除学校关联数据
            \org\Relation::deleteByHouse($obj->id,'house','school');
        });
        parent::delete();
    }
    /**
     * @param $house_id
     * @param $data
     * 修改扩展数据
     */
    public function updateHouseData($house_id,$data)
    {
        if (empty($data['seo']['seo_title'])) {
            $info['seo_title'] = $data['title'];
        }else{
            $info['seo_title'] = $data['seo']['seo_title'];
        }
        if (empty($data['seo']['seo_keys'])) {
            $info['seo_keys'] = $this->setSeo($data['title']);
        }else{
            $info['seo_keys'] = $data['seo']['seo_keys'];
        }
        $info['attr']     = $data['attr'];
        $info['info']     = isset($data['info']) ? $data['info'] : '';
        $info['seo_desc']  = $data['seo']['seo_desc'];
        model('house_data')->allowField(true)->save($info,['house_id'=>$house_id]);
    }

    /**
     * @param $house_id
     * @param $data
     * 添加扩展数据
     */
    private function addHouseData($house_id,$data)
    {
        if (empty($data['seo']['seo_title'])) {
            $info['seo_title'] = $data['title'];
        }
        if (empty($data['seo']['seo_keys'])) {
            $info['seo_keys'] = $this->setSeo($data['title']);
        }
        $info['house_id'] = $house_id;
        $info['attr']     = $data['attr'];
        $info['info']     = isset($data['info']) ? $data['info'] : '';
        $info['seo_desc']  = $data['seo']['seo_desc'];
        model('house_data')->allowField(true)->save($info);
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
        $prev_price = $priceObj->where(['house_id'=>$house_id,'model'=>'house'])->order('create_time desc')->value('price');
        if($prev_price != $price && intval($price) > 0)
        {
            $data['price'] = $price;
            $data['create_time'] = time();
            $data['house_id'] = $house_id;
            //计算涨幅比
            $prev_price && $rate = number_format((($price - $prev_price) / $prev_price) * 100,1);
            $priceObj->save($data);
        }
        return $rate;
    }

    /**
     * @return mixed
     * 弹窗选择楼盘
     */
    public function ajaxGetLists()
    {
        $lists = $this->getHouseLists();
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }

    /**
     * @return mixed
     * 文章关联楼盘
     */
    public function ajaxSelectHouse()
    {
        $lists = $this->getHouseLists();
       $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }
    private function getHouseLists()
    {
        $keyword = input('get.keyword');
        $where['status'] = 1;
        $keyword && $where[] = ['title','like','%'.$keyword.'%'];
        $lists = model('house')->where($where)->field('id,title,city,price')->order(['ordid','id'=>'desc'])->paginate(10);
        return $lists;
    }
    /**
     * @return \think\response\Json
     * 根据楼盘id获取楼盘详细内容
     */
    public function ajaxGetInfoById()
    {
        $id = input('get.id/d',0);
        $return['code'] = 0;
        if($id)
        {
            $info = model('house_data')->where('id',$id)->value('info');
            $return['code'] = 1;
            $return['data'] = $info;
        }else{
            $return['msg'] = '参数错误';
        }
        return json($return);
    }
    //关键词设置
    private function setSeo($title)
    {
        $seo_keys = "title,title房价,title优惠";
        $seo_keys = str_replace('title', $title, $seo_keys);
        return $seo_keys;
    }
}
<?php
namespace app\manage\controller;
use app\common\controller\ManageBase;
class DealStory extends ManageBase
{
    /**
    *  成交故事列表
     */
    public function Index()
    {
        $obj = model('deal_story');
        $list = $obj->where('status',1)->paginate(10);
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        return $this->fetch();
    }
    /**
    *  添加成交故事
     */
    public function Add()
    {
        if(request()->isPost())
        {
            $data = input('post.');
            $data['create_time'] = date("Y-m-d H:i:s",time());
            $arr = model('deal_story')->insert($data);
            if($arr)
            {
                $this->success("添加成功");
            }else{
                $this->error("添加失败");
            }
        }else{
            return $this->fetch();
        }
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
}
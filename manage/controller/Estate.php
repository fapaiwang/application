<?php




namespace app\manage\controller;

use \app\common\controller\ManageBase;
use app\manage\service\Synchronization;
use think\Db;
use think\facade\Log;

class Estate extends ManageBase

{

    private $model = 'estate';

    private $estate_id;


    protected $beforeActionList = [

        'beforeAdd'   => ['only'=>['add']],

        'beforeEdit'  => ['only'=>['edit']],

        'beforeIndex' => ['only'=>['index']]

    ];

    public function initialize(){

        $this->estate_id = input('param.estate_id/d',0);

        $this->param_extra = ['estate_id'=>$this->estate_id];

        parent::initialize();

    }

    public function beforeIndex()

    {
        //小区数据同步
//        $sy = new Synchronization();
//        dd($sy->synchronization_estate_all(4100));
        $this->sort = ['ordid'=>'asc','id'=>'desc'];

        $this->_data = [

            'record' => [

                'c' => 'TransactionRecord',

                'a' => 'add',

                'str' => '<a href="%s" class="layui-btn layui-btn-xs">成交记录</a>',

                'param' => ['estate_id' => '@id@', 'menuid' => 549],

                'isajax' => 0,

                'replace' => ''

            ],

        ];

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

        $position_lists = \app\manage\service\Position::lists($this->model);

        $house_position_cate_id = \app\manage\service\Position::getPositionIdByHouseId($id,$this->model);

        $this->assign('position_lists',$position_lists);

        $this->assign('position_cate_id',$house_position_cate_id);

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

    public function addDo(){

        \app\common\model\Estate::event('before_insert',function(Estate $estate,$obj){

            if($obj->checkTitleExists($obj->title))

            {

                $estate->errMsg = '该小区已存在!';

                return false;

            }




            $map       = explode(',',input('post.map'));

            $obj->lat  = isset($map[1])?$map[1]:0;

            $obj->lng  = isset($map[0])?$map[0]:0;

            if (empty($obj->seo_title)) {

                $obj->seo_title = $obj->title;

            }

            if (empty($obj->seo_keys)) {

                $obj->seo_keys = $estate->setSeo($obj->title);

            }

            $images = $estate->getPic();

            if($images){

                $obj->file = $images;

            }

            if (isset($_POST['position'])) {

                $data['rec_position'] = 1;

            }

            return true;

        });

        \app\common\model\Estate::event('after_insert',function($obj){

            //同步小区
            $data = input('post.');
            //查询小区
            $fa_community=Db::connect('db2')->name('community')->where('title',$data['title'])->find();
            if (empty($fa_community['id'])){
                $sy = new Synchronization();
                //添加小区
                $arr = $sy->fa_estate_arr($data,$obj->id);
                model('community')->allowField(true)->save($arr);
            }
            //结束同步

            \app\manage\service\Position::option($obj->id,'estate');

            \org\Relation::addSchool('estate',$obj->lng,$obj->lat,$obj->id,$obj->city);

            \org\Relation::addMetro('estate',$obj->lng,$obj->lat,$obj->id,$obj->city);

        });

        parent::addDo();

    }

    public function editDo(){
        \app\common\model\Estate::event('before_update',function(Estate $estate,$obj){

            if($obj->checkTitleExists($obj->title,$obj->id))

            {

                $estate->errMsg = '该小区已存在!';

                return false;

            }
            //同步小区
            $data = input('post.');
            //获取法拍网小区id
            $sy = new Synchronization();
            $fa_community_id = $sy->get_community_id($data);
            if (!empty($fa_community_id)){
                //编辑小区信息
                $arr = $sy->fa_estate_arr($data,$data['id']);
                model('community')->allowField(true)->save($arr,['id'=>$fa_community_id]);
            }
            //结束同步


            $map       = explode(',',input('post.map'));

            $obj->lat  = isset($map[1])?$map[1]:0;

            $obj->lng  = isset($map[0])?$map[0]:0;

            $images = $estate->getPic();

            if($images){

                $obj->file = $images;

            }

            if (!isset($_POST['position'])) {

                $obj->rec_position = 0;

            } else {

                $obj->rec_position = 1;

            }

            return true;

        });

        \app\common\model\Estate::event('after_update',function($obj){

            \app\manage\service\Position::option($obj->id,'estate');

            \org\Relation::addSchool('estate',$obj->lng,$obj->lat,$obj->id,$obj->city);

            \org\Relation::addMetro('estate',$obj->lng,$obj->lat,$obj->id,$obj->city);

        });

        parent::editDo();

    }



    /**

     * 删除

     */

    public function delete()

    {

        \app\common\model\Estate::event('after_delete',function($obj){
            $data=[
                'id'=>$obj->id,
                'title'=>$obj->title,
            ];
            $sy = new Synchronization();
            $community_id =$sy->get_community_id($data);
            if($community_id){
                Db::connect('db2')->name('community')->where(['id'=>$community_id])->delete();
            }



            model('attachment')->deleteAttachment($obj->info,$obj->img,$obj->file);

            //删除推荐数据

            action('manage/Position/deleteByHouseId', ['house_id'=>$obj->id,'model'=>'estate'], 'controller');

            //删除地铁关联数据

            \org\Relation::deleteByHouse($obj->id,'estate');

            //删除学校关联数据

            \org\Relation::deleteByHouse($obj->id,'estate','school');

        });


        parent::delete();

    }



    /**

     * 异步获取小区列表

     */

    public function ajaxGetEstate()

    {
        // echo "456";
        $keyword = input('get.keyword');

        $where['status'] = 1;

        $keyword && $where[] = ['title','like','%'.$keyword.'%'];

        $lists = model('estate')->where($where)->field('id,title,lng,lat,address')->order('id desc')->paginate(10);

        $this->assign('lists',$lists);

        $this->assign('pages',$lists->render());

        return $this->fetch();

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

    //关键词设置

    private function setSeo($title)

    {

        $seo_keys = "title,title二手房,title二手房价格,title怎么样,title出租房";

        $seo_keys = str_replace('title', $title, $seo_keys);

        return $seo_keys;

    }

}
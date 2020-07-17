<?php
namespace app\home\controller\user;
use app\common\controller\UserBase;
use app\home\service\ToolsServer;

class Second extends UserBase

{

    private $queryData;



    /**

     * @return mixed

     * 二手房列表

     */

    public function index()

    {

        $where = $this->search();

        $field = "id,title,estate_name,img,room,living_room,price,acreage,status,update_time,top_time,timeout";

        $lists = model('second_house')->where($where)->field($field)->order(['top_time'=>'desc','id'=>'desc'])->paginate(15,false,['query'=>$this->queryData]);

        $this->assign('list',$lists);

        $this->assign('pages',$lists->render());

        return $this->fetch();

    }



    /**

     * @return mixed

     * 添加二手房

     */

    public function add()

    {

        return $this->fetch();

    }



    /**

     * @return mixed

     * 编辑二手房

     */

    public function edit()
    {
        $ToolsServer= new ToolsServer();
        $id  = input('param.id/d',0);
        if(!$id)
        {
            $this->error('参数错误');
        }
        $url   = request()->server('HTTP_REFERER');
        $data = model('second_house_data')->where(['house_id'=>$id])->find();
        if(empty($data)){
            model('second_house_data')->insert(array('house_id'=>$id,'update_time'=>time()));
            $data = model('second_house_data')->where(['house_id'=>$id])->find();
        }
        $position_lists = model('position_cate')->where(['model'=>"second_house"])->field('id,title')->select();
        $house_position_cate_id = model('position')->where(['house_id' => $id, 'model' => 'second_house'])->column('cate_id');//获取该楼盘 所属的推荐位id
        $info = model('second_house')->find($id);
        $estate = model('estate')->where(['id'=>$info['estate_id']])->field("years,data")->find();
        if($data['bidding_cycle']==0){
            $data['bidding_cycle']=24;
        }
        $jiage  = 0;
        $mianji = $info['acreage'];
        if($info['ckprice']>0){
            $jiage = $info['ckprice'];
        }elseif($info['price']>0){
            $jiage = $info['price'];
        }
        if(empty($data['deed_tax'])&&!empty($info['qipai'])){
            $data['deed_tax'] = $ToolsServer->deen_tax($jiage,$mianji);
        }
        if(empty($data['first_suite'])&&!empty($jiage)){
            $data['first_suite'] = $ToolsServer->deen_tax($jiage,$mianji);
        }
        if(empty($data['second_suite'])&&!empty($jiage)){
            $data['second_suite'] = $ToolsServer->deen_tax($jiage,$mianji,501);
        }
        if(empty($data['lending_rate'])){
            $lending_rate = getSettingCache('tools');
            $data['lending_rate'] = $lending_rate['lilv'];
        }
        if(empty($data['bidding_rules'])){
            $data['bidding_rules'] = '至少一人报名且出价不低于变卖价，方可成交。';
        }
        //查询已编辑次数
        $broker_id = $this->userInfo['id'];
        $where['operator'] = $broker_id;
        $where['house_id'] = $id;
        $where['type'] = 2;
        $edit_number = model('operatio_log')->where($where)->count();
        $this->assign('edit_number',$edit_number);
        $this->assign('years',$estate['years']);
        $this->assign('developer',$estate['data']['developer']);
        $this->assign('back_url',$url);
        $this->assign('info', $info);
        $this->assign('position_lists',$position_lists);
        $this->assign('position_cate_id',$house_position_cate_id);
        $this->assign('data',$data);
        return $this->fetch();
    }
    /**
    *   修改面积和市场价更新契税第一套和第二套契税
     */
    function deen_tax(){
        $acreage  = input('post.acreage');
        $jiage = input('post.jiage');
        $qipai = input('post.qipai');
        $ToolsServer= new ToolsServer();
        $data['deed_tax'] = $ToolsServer->deen_tax($qipai,$acreage);
        $data['first_suite'] = $ToolsServer->deen_tax($jiage,$acreage);
        $data['second_suite'] = $ToolsServer->deen_tax($jiage,$acreage,501);
        return $data;
    }

    /**

     * @return array

     * 搜索条件

     */

    private function search()

    {

        $status  = input('get.status');

        $keyword = input('get.keyword');

        $where   = [];

        $where['broker_id'] = $this->userInfo['id'];

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

}
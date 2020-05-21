<?php

namespace app\api\controller;

use app\home\service\IndexServer;
use app\home\service\SecondServer;
use app\server;
use app\tools\ApiResult;
use think\Controller;

header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
header("Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");
//……
class SecondHouse extends Controller
{
    use ApiResult;
    protected $Second_Server;
    protected $Index_Server;
    public function __construct(SecondServer $Second_Server,IndexServer $Index_Server)
    {
        $this->Second_Server = $Second_Server;
        $this->Index_Server = $Index_Server;
    }
    //获取今日新增房源
    public function get_today_add(){
        $res = $this->Second_Server->get_today_add();
        if (!$res->isEmpty()){
            return $this->success_o($res);
        }else{
            return $this->error_o("未查询到今日房源");
        }

    }
    //推荐房源
    public function recommend_house(){
        $limit =input('get.limit') ?? 6;
        $res =  $this->Index_Server->get_recommend_house($limit);
        if (!$res->isEmpty()){
            return $this->success_o($res);
        }else{
            return $this->error_o("未查询到推荐房源");
        }
    }
    //自由购房源
    public function restrict_house(){
        $limit =input('get.limit') ?? 6;
        $res =  $this->Index_Server->get_restrict_house($limit);
        if (!$res->isEmpty()){
            return $this->success_o($res);
        }else{
            return $this->error_o("未查询到自由购房源");
        }
    }
    //获取优质小区
    public function quality_estate(){
        $limit =input('get.limit') ?? 10;
       $res =  $this->Index_Server->get_quality_estate($limit);
        if (!$res->isEmpty()){
            return $this->success_o($res);
        }else{
            return $this->error_o("未查询到优质小区");
        }
    }
    
    
    /**
     * @description 推荐房源详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @auther xiaobin
     */
    public function houseDetail()
    {
        $id = input('param.id',0);
        $house = new server();
        $houseRes = $house->second_model($id);
        $houseRes['toilet'] = getLinkMenuName(29,$houseRes['toilet']);
        $houseRes['acreage'] = $houseRes['acreage'].config('filter.acreage_unit');
        $houseRes['estate'] = $house->estate($houseRes['estate_id']);
        $houseRes['orientations'] = getLinkMenuName(4,$houseRes['orientations']);
        $houseRes['user'] = $house->user_info($id,$houseRes['broker_id']);
        $houseRes['user'] =  $houseRes['user'] && $houseRes['user']['history_complate'] ? $houseRes['user']['history_complate'] : 0;
        $houseRes['user_info'] = $house->user($id,$houseRes['broker_id']);
        $houseRes['user_info'] = $houseRes['user_info'] && $houseRes['user_info']['lxtel_zhuan'] ? $houseRes['user_info']['lxtel_zhuan'] : '';
        $houseRes['pinglun'] = model('user')->where('id',$houseRes['broker_id'])->find();//客服
        $user = login_user();
        $houseRes['is_guanzhu'] = $house->is_guanzhu($houseRes['id'],$user['id']);
        $houseRes['user_id'] = $user['id'];
        $SecondServer = new SecondServer();
        //推荐房源
        $recommend_house = $SecondServer->get_recommend_house($houseRes['city'],$id,$houseRes['estate_id']);
        foreach ($recommend_house as &$house){
            $house['toilet'] = getLinkMenuName(29,$house['toilet']).'-'.$house['acreage']."/㎡";
        }
        $houseRes['recommend_house'] = $recommend_house;
        return $this->success_o($houseRes);
    }
}
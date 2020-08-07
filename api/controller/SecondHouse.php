<?php

namespace app\api\controller;

use app\home\service\IndexServer;
use app\home\service\SecondServer;
use app\server;
use app\tools\ApiResult;
use think\Controller;
use think\Db;

//……
class SecondHouse extends Controller
{
    use ApiResult;
    protected $Second_Server;
    protected $Index_Server;
    protected $cityInfo;
    protected $server;
    protected $second;

    
    public function __construct(SecondServer $Second_Server,IndexServer $Index_Server,server $server,\app\home\controller\Second $second)
    {
        $this->Second_Server = $Second_Server;
        $this->Index_Server = $Index_Server;
        $this->server = $server;
        $this->second = $second;//todo 非法调用控制器
    }

    /**
     * 获取今日新增房源
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function get_today_add(){
        $res = $this->Second_Server->get_today_add();
        if (!$res->isEmpty()){
            return $this->success_o($res);
        }else{
            return $this->error_o("未查询到今日房源");
        }

    }

    /**
     * 推荐房源
     * @param mixed
     * @return \think\response\Json
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function recommend_house(){
        $limit =input('get.limit') ?? 6;
        $res =  $this->Index_Server->get_recommend_house($limit);
        if (!$res->isEmpty()){
            return $this->success_o($res);
        }else{
            return $this->error_o("未查询到推荐房源");
        }
    }

    /**
     * 自由购房源
     * @param mixed
     * @return \think\response\Json
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function restrict_house(){
        $limit =input('get.limit') ?? 6;
        $res =  $this->Index_Server->get_restrict_house($limit);
        if (!$res->isEmpty()){
            return $this->success_o($res);
        }else{
            return $this->error_o("未查询到自由购房源");
        }
    }

    /**
     * 获取优质小区
     * @param mixed
     * @return \think\response\Json
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
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
        $user_id = input('param.user_id/d',0);
        if(empty($id)){
            return $this->error_o("房源id不能为空");
        }
        $house = new server();
        $houseRes = $house->second_model($id);
        if(empty($houseRes)){
            return $this->error_o("未找到当前房源");
        }
        $traffic = $rim = "";
        if (!empty($houseRes['basic_info'])) {
            $traffic = $houseRes['basic_info'][8];//交通出行
            $rim = $houseRes['basic_info'][7];//周边配套
        }
        $houseRes['traffic'] = $traffic;//交通出行
        $houseRes['rim'] = $rim;//周边配套
        
        $houseRes['toilet'] = getLinkMenuName(29,$houseRes['toilet']);
        $houseRes['acreage'] = $houseRes['acreage'].config('filter.acreage_unit');
        $houseRes['estate'] = $house->estate($houseRes['estate_id']);
        $houseRes['orientations'] = getLinkMenuName(4,$houseRes['orientations']);
        $houseRes['user'] = $house->user_info($id,$houseRes['broker_id']);
        $houseRes['user'] =  $houseRes['user'] && $houseRes['user']['history_complate'] ? $houseRes['user']['history_complate'] : 0;
        $houseRes['user_info'] = $house->user($id,$houseRes['broker_id']);
        $houseRes['user_info'] = $houseRes['user_info'] && $houseRes['user_info']['lxtel_zhuan'] ? $houseRes['user_info']['lxtel_zhuan'] : '';
        $houseRes['pinglun'] = model('user')->where('id',$houseRes['broker_id'])->find();//客服
        $user = login_user($user_id);
        $houseRes['is_guanzhu'] = 0;
        if ($user){
            $houseRes['is_guanzhu'] = $house->is_guanzhu($houseRes['id'],$user['id']);
            $houseRes['user_id'] = $user['id'];
        }
        $SecondServer = new SecondServer();
        //推荐房源
        $recommend_house = $SecondServer->get_recommend_house($houseRes['city'],$id,$houseRes['estate_id']);
        foreach ($recommend_house as &$house){
            $house['toilet'] = getLinkMenuName(29,$house['toilet']).'-'.$house['acreage']."/㎡";
        }
        $houseRes['recommend_house'] = $recommend_house;
        //房源特色标签
        $house_tag =$SecondServer->get_house_characteristic_obj($houseRes['xsname'],$houseRes['jieduan_name'],
            $houseRes['marketprice'],$houseRes['is_commission'],$houseRes['is_school'],$houseRes['is_metro']);
        $houseRes['house_tag'] = $house_tag;
        //本小区拍卖套数
        $houseRes['estate_num']= $SecondServer->estate_second_num($id,$houseRes['estate_name']);
        //法拍专员点评/点评个数
        $houseRes['second_house_user_comment'] = $SecondServer->second_house_user_comment($id);
        //公告处理
        if ($houseRes["info"]){
            $info = strip_tags($houseRes["info"]);
            $info = mb_substr($info,0,150);
            $info = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", " ", strip_tags($info));
            $houseRes["info"] = $info;
        }
        return $this->success_o($houseRes);
    }

    public function second_detail_ohter(){
        $id = input('param.id',0);
        if(empty($id)){
            return $this->error_o("房源id不能为空");
        }
        $info = $this->server->second_detail_ohter($id);
        if(empty($info)){
            return $this->error_o("未找到当前房源");
        }
        return $this->success_o($info);
    }
    /**
     * @description 获取房源列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @auther xiaobin
     */
    public function houseList(){
        $parameter = input('param.a');
        $estate_id     = input('param.estate_id/d',0);//小区id
        $ids  = input('param.ids');//房源批量id(,)
        $info = $this->second->getLists($parameter,$estate_id,$ids);
//        $lists  = $info['lists'];
//        return $this->success_o($lists);
        return $this->success_o($info);
    }

    /**
     * @description 获取指定城市id下的所有区域
     * @param int $city_id 城市ID
     * @return bool|mixed
     */
    public function getCityChild($city_id = 0) {
        $city_id = $city_id ? $city_id : $this->cityInfo['id'];
        if ($city_id) {
            $city_ids = cache('city_all_child_'.$city_id);
            if (!$city_ids) {
                $city_ids = model('city')->get_child_ids($city_id,true);
                cache('city_all_child_'.$city_id,$city_ids,7200);
            }
            return $city_ids;
        }
        return false;
    }
    
    /**
     * @param $area_id
     * @return array
     * 获取区域下的商圈
     */
     public function getRadingByAreaId($area_id) {
        $city        = getCity();
        $city_cate   = getCity('cate');
        $rading      = [];
        $city_id     = $this->cityInfo['id'];
        
        if (array_key_exists($area_id, $city_cate)) {
            $pid = $city_cate[$area_id]['pid'];
            if ($pid == $city_id) {//如果 父级id==城市id  说明当前选择的是区域   否则当前选择的是商圈
                $rading = isset($city[$pid]['_child'][$area_id]['_child']) ? $city[$pid]['_child'][$area_id]['_child'] : [];
            } else {
                $rading = isset($city[$city_id]['_child'][$pid]['_child']) ? $city[$city_id]['_child'][$pid]['_child'] : [];
            }
        }
        return $rading;
    }
    
    /**
     * @description 区域
     * @return \think\response\Json
     * @auther xiaobin
     */
    public function getAreaByCityId() {
        $city_id = input("param.city_id");//城市ID
        $city = getCity();
        $city_cate = getCity('cate');
        $area = [];
        !$city_id && $city_id = $this->cityInfo['id'];
        if (array_key_exists($city_id,$city_cate)) {
            $pid = $city_cate[$city_id]['pid'];
            if ($pid == 0) {
                $area = isset($city[$city_id]['_child']) ? $city[$city_id]['_child'] : [];
            } else {
                $area = isset($city[$pid]['_child']) ? $city[$pid]['_child'] : [];
            }
        }
        return $this->success_o($area);
    }
    
    /**
     * @description
     * @auther xiaobin
     * @return \think\response\Json
     * 逻辑
     * 9形式 如：法拍房源、国有资产、涉诉房产、社会委托
     * 26类型 如：别墅、平房.四合院、商办、住宅、写字楼、厂房、土地、酒店、公寓
     * 25 房拍阶段  一拍、二拍、变卖
     * 27 房屋状态 预告、进行、结束、终止。。。
     */
    public function houseType() {
        $id = input("param.id");
        if ($id < 1) {
            return $this->error_o("不合法的参数");
        }
        return $this->success_o(getLinkMenuCache($id));
    }

    /**
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function getStage(){
        $info = $this->Second_Server->roomSplicing();
        return $this->success_o($info);
    }
    
    /**
     * @description
     * @param $val
     * @param $type
     * @return array
     * @auther xiaobin
     */
    public function familyType($val ,$type)
    {
        $param = [];
        if ($type != "") {
            $types = explode(",", $type);
            if ($types ==1) {
                $param = ["s.".$val,"eq",$type];
            }
            if ($types >1)  {
                $param = ["s.".$val,"in",$types];
            }
        }
        return $param;
    }
        /**
     * @description 总价
     * @return \think\response\Json
     * @auther xiaobin
     * 如： 500万以下、500-1000万、1000-3000万
     */
    public function getSecondPrice()
    {
        return $this->success_o(getSecondPrice());
    }
    
    /**
     * @description 房屋面积
     * @return \think\response\Json
     * @auther xiaobin
     * 如：50m²以下、50-100m²、100-200m²
     */
    public function getAcreage() {
        return $this->success_o(getAcreage());
    }
    
    /**
     * @description 房屋户型
     * @return \think\response\Json
     * @auther xiaobin
     * 如：一室、二室、三室
     */
    public function getRoom() {
        $info = $this->Second_Server->roomSplicing();
        return $this->success_o($info);
    }
    
    /**
     *
     * @param $sort
     * @return array
     * 排序
     */
    private function getSort($sort) {
        switch ($sort) {
            case 0:
//                $order = ['fcstatus'=>'asc','ordid'=>'asc','id'=>'desc']; jieduan kptime
                $order = ['jieduan'=>'asc','kptime'=>'desc'];
                break;
            case 1:
                $order = ['price'=>'asc','id'=>'desc'];
                break;
            case 2:
                $order = ['price'=>'desc','id'=>'desc'];//房屋总价
                break;
            case 3:
                $order = ['average_price'=>'asc','id'=>'desc'];
                break;
            case 4:
                $order = ['average_price'=>'desc','id'=>'desc'];
                break;
            case 5:
                $order = ['acreage'=>'asc','id'=>'desc'];//房屋面积
                break;
            case 6:
                $order = ['acreage'=>'desc','id'=>'desc'];
                break;
            case 7:
                $order = ['fabutimes'=>'desc','ordid'=>'desc','id'=>'desc'];//最新房源
                break;
            case 8:
                $order = ['marketprice'=>'desc'];
                break;
            case 9:
                $order = ['rec_position'=>'desc','fcstatus'=>'asc','marketprice'=>'desc'];
                break;
            case 10://开拍时间
                $order = ['kptime'=>'desc'];
                break;
            default:
                $order = ['ordid'=>'asc','id'=>'desc'];
                break;
        }
        return $order;
    }
    
    /**
     * @description 总价多选
     * @param $name
     * @param $Price
     * @return array
     * @auther xiaobin
     */
    public function getPrice($name, $Price) {
        $param = [];
        if ($Price != "") {
            $acreages = explode(',', $Price);
            if (count($acreages) ==1) {
                $req = "egt";
                if (strlen($Price) == 3) {
                    $req = "elt";
                }
                $param = ["s."."{$name}","{$req}",$Price];
            } else {
                if (strpos($Price, "-") !==false) {
                    $start = $this->getVal(reset($acreages));
                    $end = $this->getVal(end($acreages));
                    $param = ["s."."{$name}","between",[$start,$end]];
                }
            }
        }
        return $param;
    }
    
    /**
     * @description 面积多选，获取查询范围
     * @param $name string 字段名
     * @param $acreage
     * @return array
     * @auther xiaobin
     */
    public function Acreage($name, $acreage) {
        $param = [];
        if ($acreage !="") {
            $acreages = explode(',', $acreage);
            if (count($acreages) ==1) {
                if (strpos($acreage, "-") !==false) {
                    $start = $this->getVal($acreage);
                    $end = $this->getEndVal($acreage);
                    $param = ["s."."{$name}","between",[$start,$end]];
                } else {
                    $req = "egt";
                    if (strlen($acreage) == 3) {
                        $req = "elt";
                    }
                    $param = ["s."."{$name}","{$req}",$acreage];
                }
            } else {
                if (strpos($acreage, "-") !==false) {
                    $start = $this->getVal(reset($acreages));
                    $end = $this->getEndVal(end($acreages));
                    $param = ["s."."{$name}","between",[$start,$end]];
                }
            }
        }
        return $param;
    }
    
    /**
     * @description 获取值
     * @param $val
     * @return bool|string
     * @auther xiaobin
     */
    public function getVal($val)
    {
        if (strpos($val, "-") !==false) {
            $endVal = substr($val,0,strpos($val, "-"));
        } else {
            $endVal = $val;
        }
        return $endVal;
    }
    
    /**
     * @description 获取值
     * @param $val
     * @return bool|string
     * @auther xiaobin
     */
    public function getEndVal($val)
    {
        if (strpos($val, "-") !==false) {
            $endVal = substr($val,strpos($val, "-")+1,strlen($val));
        } else {
            $endVal = $val;
        }
        return $endVal;
    }
    
    /**
     * @description 户型多选
     * @param $room
     * @return array
     * @auther xiaobin
     */
    public function getRooms($room)
    {
        $param = [];
        if ($room !="") {
            $rooms = explode(',', $room);
            if (count($rooms) >1) {
                $first = reset($rooms);
                $end = end($rooms);
                if ($end - $first ==1) {
                    $param = ["s.room","between",[$first,$end]];
                } else {
                    $param = ["s.room","in",[$room]];
                }
            } else {
                if ($room ==6) {
                    $param = ["s.room","egt",$room];
                }
                if ($room ==1) {
                    $param = ["s.room","eq",$room];
                }
            }
        }
        return $param;
    }
    //特色房源
    public function characteristicHouse(){
        $res[0]["name"]="为你选房";
        $res[0]["img"] =$this->Index_Server->get_home_banner(20);
//        $res[1]["name"]="特色房源";
//        $res[1]["img"]=$this->Index_Server->get_home_banner(21);
        return $this->success_o($res);
    }

    /**
     * 特色房源详情
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function characteristicHouseInfo(){
        $extension_name = input('param.name');
        if (empty($extension_name)){
            return $this->error_o("名称不能为空");
        }
        $second_house_extension = $this->Second_Server->characteristic_detail($extension_name);
        return $this->success_o($second_house_extension);
    }
    /**
     * 获取当前房源的小区拍卖总数
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function second_estate_num(){
        $second_house_id = input('param.second_house_id');
        $estate_name = input('param.estate_name');
        if (empty($second_house_id)){
            return $this->error_o("房源id不能为空");
        }
        if (empty($estate_name)){
            return $this->error_o("小区名称不能为空");
        }
        $num = $this->Second_Server->estate_second_num($second_house_id,$estate_name);
        return $this->success_o($num);
    }

    /**
     * 获取房源资讯
     * @param mixed
     * @return \think\response\Json
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function second_house_comment(){
       $house_id = input('house_id');
       $limit = input('limit');
       $is_rand = input('is_rand');
        if (empty($house_id)){
            return $this->error_o("房源id不能为空");
        }
        $info = $this->Second_Server->second_house_user_comment($house_id,$limit,$is_rand);
        return $this->success_o($info);
    }
    public function houseSingleField(){
        $second_house_id = input('param.house_id',0);
        $house_field = input('param.house_field');
        if(empty($second_house_id)){
            return $this->error_o("房源id不能为空");
        }
        $info = $this->Second_Server->single($second_house_id,$house_field);
        if(empty($info)){
            return $this->error_o("未找到当前房源");
        }
        return $this->success_o($info);
    }
    public function houseCommentAdd(){
        return action('home/Api/fydp');
    }

}
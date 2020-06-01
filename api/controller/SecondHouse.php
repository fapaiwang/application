<?php

namespace app\api\controller;

use app\home\service\IndexServer;
use app\home\service\SecondServer;
use app\server;
use app\tools\ApiResult;
use think\Controller;

//……
class SecondHouse extends Controller
{
    use ApiResult;
    protected $Second_Server;
    protected $Index_Server;
    protected $cityInfo;
    
    public function __construct(SecondServer $Second_Server,IndexServer $Index_Server)
    {
        $this->Second_Server = $Second_Server;
        $this->Index_Server = $Index_Server;
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
        $house = new server();
        $houseRes = $house->second_model($id);
        $traffic = $rim = "";
        if (!$houseRes['basic_info'] == "") {
            $basic_info = explode(',',$houseRes['basic_info']);
            $traffic = $basic_info[8];//交通出行
            $rim = $basic_info[7];//周边配套
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
        //房源特色标签
        $houseRes['house_tag'] = $SecondServer->get_house_characteristic($houseRes['xsname'],$houseRes['jieduan_name'],
            $houseRes['marketprice'],$houseRes['is_commission'],$houseRes['is_school'],$houseRes['is_metro']);
    
        //本小区拍卖套数
        $houseRes['estate_num']= $SecondServer->estate_second_num($id,$houseRes['estate_name']);
        //法拍专员点评/点评个数
        $houseRes['second_house_user_comment'] = $SecondServer->second_house_user_comment($id);;
        return $this->success_o($houseRes);
    }
    
    
    /**
     * @description 获取房源列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @auther xiaobin
     */
    public function houseList()
    {
        $time    = time();
        $where   = $this->search();
        $sort    = input('param.sort/d',0);
        $acreage = $this->Acreage('acreage',input('param.acreage',''));//房屋面积
        $rooms = $this->getRooms(input('param.room',''));//户型多选
        $qipai = $this->getPrice('qipai',input('param.qipai',''));//房屋价格多选
        $jieduan = $this->familyType('jieduan',input('param.jieduan',''));//阶段多选
        $types = $this->familyType('types',input('param.types',''));//房屋户型
        $type = $this->familyType('house_type',input('param.type',''));//房屋类型
        //todo 地铁
        
        $keyword = input('param.keyword');//搜索小区名称/房屋名称
        $field   = "s.id,s.title,s.estate_id,s.estate_name,s.chajia,s.junjia,s.marketprice,s.city,s.video,s.total_floor,s.floor,s.img,s.qipai,s.pano_url,s.room,s.living_room,s.toilet,s.price,s.cjprice,s.average_price,s.tags,s.address,s.acreage,s.orientations,s.renovation,s.user_type,s.contacts,s.update_time,s.kptime,s.jieduan,s.fcstatus,s.types,s.onestime,s.oneetime,s.oneprice,s.twostime,s.twoetime,s.twoprice,s.bianstime,s.bianetime,s.bianprice,s.is_free";
        $obj     = model('second_house')->alias('s');
        if (!empty($type)) {//面积
            $where[] = $type;
        }
        if (!empty($acreage)) {//面积
            $where[] = $acreage;
        }
        if (!empty($keyword)) {//户型
            $where[] = $acreage;
        }
        if (!empty($qipai)) {//总价多选
            $where[] = $qipai;
        }
        if (!empty($jieduan)) {//阶段多选
            $where[] = $jieduan;
        }
        if (!empty($types)) {//房屋户型
            $where[] = $types;
        }
        if (!empty($rooms)) {//面积
            $where[] = $rooms;
        }
        //二手房列表
        if(isset($where['m.metro_id']) || isset($where['m.station_id'])){
            //查询地铁关联表
            $field .= ',m.metro_name,m.station_name,m.distance';
            $join  = [['metro_relation m','m.house_id = s.id']];
            $lists = $obj->join($join)->where($where)->where('m.model','second_house')->where('s.top_time','lt',$time)->field($field)->group('s.id')->order($this->getSort($sort))->paginate(30,false,['query'=>['keyword'=>$keyword]]);
        } else {
            
            if($sort==8){
                $lists   = $obj->where($where)->where('s.top_time','lt',$time)->where('s.fcstatus','neq',169)->field($field)->order($this->getSort($sort))->paginate(30,false,['query'=>['keyword'=>$keyword]]);
            }else if($sort==7){
                $lists   = $obj->where($where)->where('s.top_time','lt',$time)->where('s.fcstatus','eq',170)->field($field)->order($this->getSort($sort))->paginate(30,false,['query'=>['keyword'=>$keyword]]);
            }else{
                $lists   = $obj->where($where)->where('s.top_time','lt',$time)->field($field)->order($this->getSort($sort))->paginate(30,false,['query'=>['keyword'=>$keyword]]);
            }
        }
        if($lists->currentPage() == 1){
            //二手房置顶列表
            $obj = $obj->removeOption()->alias('s');
            //关联地铁表
            if(isset($where['m.metro_id']) || isset($where['m.station_id'])){
                $field .= ',m.metro_name,m.station_name,m.distance';
                $join  = [['metro_relation m','m.house_id = s.id']];
                $obj->join($join)->where('m.model','second_house')->group('s.id');
            }
        }
        foreach ($lists as $key => $value) {
            $estate_id=$lists[$key]['estate_id'];
            $sql=model('estate')->where('id','eq',$estate_id)->alias('years')->find();
            $years=$sql['years'];
            $lists[$key]['years']=$years;
            $city_id=$lists[$key]['city'];
            $sqls = model('city')->field('id,pid,spid,name,alias')->where('id','eq',$city_id)->find();
            $spid=$sqls['spid'];
            $city_name=$sqls['name'];
            if(substr_count($spid,'|')==2){
                $listsss = model('city')->field('id,name')->where('id','eq',$sqls['pid'])->find();
                $shi=$listsss['name'];
                $lists[$key]['city']=$shi.$city_name;
            }else{
                $lists[$key]['city']=$city_name;
            }
            $lists[$key]['jieduan_name']=getLinkMenuName(25,$lists[$key]['jieduan']);;
            $lists[$key]['types_name'] =getLinkMenuName(26,$lists[$key]['types']);
            $lists[$key]['chajia']=intval($lists[$key]['price'])-intval($lists[$key]['qipai']);
        }
        return $this->success_o($lists);
    }
    
    /**
     * @description 搜索条件
     * @return array
     * @auther xiaobin
     */
    public function search() {
        $estate_id     = input('param.estate_id/d',0);//小区id
        $param['area'] = input('param.area/d', $this->cityInfo['id']);
        $param['rading']     = 0;
        $param['tags']       = input('param.tags/d',0);
        $param['qipai']      = input('param.qipai',0);
        $param['room']       = input('param.room',0);//户型
//        $param['types']       = input('param.types',0);//户型
//        $param['jieduan']       = input('param.jieduan',0);//户型
        $param['fcstatus']       = input('param.fcstatus',0);//状态
//        $param['type']       = input('param.type',0);//物业类型
        $param['renovation'] = input('param.renovation',0);//装修情况
        $param['metro']      = input('param.metro/d',0);//地铁线
        $param['metro_station'] = input('param.metro_station/d',0);//地铁站点
        $param['sort']          = input('param.sort/d',0);//排序
        $param['is_free']          = input('param.is_free/d',0);//自由购
        $param['orientations']  = input('param.orientations/d',0);//朝向
        $param['user_type']  = input('param.user_type/d',0);//1个人房源  2中介房源
        $param['area'] == 0 && $param['area'] = $this->cityInfo['id'];
        $param['search_type']   = input('param.search_type/d',1);//查询方式 1按区域查询 2按地铁查询
        $data['s.status']    = 1;
        
        $keyword = input('param.keyword');//搜索小区名称/房屋名称
        //显示街道时
        if ($param['area']  > 57){
            $param['street'] =$param['area'];
        }
        $zprice1 =0;
        $param['zprice1']=$_GET['zprice1'] ??"";
        $param['zprice2']=$_GET['zprice2'] ?? "";
        $param['zmianji1']=$_GET['zmianji1'] ?? "";
        $param['zmianji2']=$_GET['zmianji2'] ?? "";
        if(!empty($_GET['zprice1'])){
            $zprice1=$_GET['zprice1'];
        }
        if(!empty($_GET['zprice2'])){
            $zprice2=$_GET['zprice2'];
        }
        $zmianji1 =0;
        if(!empty($_GET['zmianji1'])){
            $zmianji1=$_GET['zmianji1'];
        }
        if(!empty($_GET['zmianji2'])){
            $zmianji2=$_GET['zmianji2'];
        }
        if($estate_id) {
            $data['s.estate_id'] = $estate_id;
        }
//        if(!empty($param['type'])){
//            $data['s.house_type'] = $param['type'];
//        }
        if(!empty($param['user_type'])){
            $data['s.user_type'] = $param['user_type'];
        }
        if(!empty($param['orientations'])){
            $data['s.orientations'] = $param['orientations'];
        }
        if($param['renovation']){
            $data['s.renovation'] = $param['renovation'];
        }
        if($keyword){
            $data[] = ['s.title','like','%'.$keyword.'%'];
        }
        
        if ($param['search_type'] == 2) {
            if (!empty($param['metro'])){
                $data['m.metro_id'] = $param['metro'];
            } else {
                $data[] = ['s.city','in',$this->getCityChild()];
            }
            if (!empty($param['metro_station'])) {
                $data['m.station_id'] = $param['metro_station'];
            }
        } else {
            if (!empty($param['area'])) {
                $data[] = ['s.city','in',$this->getCityChild($param['area'])];
            }
        }
       
//        if (!empty($param['types'])) {
//            $data['s.types'] = $param['types'];
//        }
//        if (!empty($param['jieduan'])) {
//            $data['s.jieduan'] = $param['jieduan'];
//        }
        if (!empty($param['fcstatus'])) {
            $data['s.fcstatus'] = $param['fcstatus'];
        }

        if (!empty($param['tags'])) {
            $data[] = ['','exp',\think\Db::raw("find_in_set({$param['tags']},s.tags)")];
        }
        $data[] = ['s.timeout','gt',time()];
        //是否是自由购
        if(!empty($param['is_free'])){
            $data['s.is_free'] = $param['is_free'];
        }
        if(!empty($_GET['zprice2'])){
            $data[] = ['s.qipai','between',[$zprice1,$zprice2]];
        }
        if(!empty($_GET['rec_position'])){
            $data[] = ['rec_position','eq',1];
        }
        if(!empty($_GET['zmianji2'])){
            $data[] = ['s.acreage','between',[$zmianji1,$zmianji2]];
        }
        $data = array_filter($data);
        return $data;
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
        return $this->success_o(getRoom());
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
        $res[1]["name"]="特色房源";
        $res[1]["name"]=$this->Index_Server->get_home_banner(21);
        return $this->success_o($res);
    }
}
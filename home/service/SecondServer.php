<?php

namespace app\home\service;



use app\home\controller\Poster;
use app\tools\ApiResult;
use function GuzzleHttp\debug_resource;
use think\console\command\make\Model;
use think\Db;
use think\facade\Cache;
use think\Log;

class SecondServer
{
    use ApiResult;
    /**
     * 房源点评
     * @param $second_house_id 房源id
     * @param mixed
     * @return array|\PDOStatement|string|\think\Collection
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function second_house_user_comment($second_house_id,$limit=2,$is_rand=0){

        $obj     = model('fydp')->alias('s');
        $fydp =$obj->join([['user m','m.id = s.user_id']])->join([['user_info info','info.user_id = s.user_id']])
            ->field('s.id,s.user_id,s.house_name,s.house_id,m.nick_name,m.lxtel,info.history_complate,m.kflj')
            ->where('s.house_id',$second_house_id)->where('s.model','second_house')
            ->group('s.user_id') ->limit($limit);

            if (!empty($is_rand)){
                $fydp = $fydp->orderRaw('rand()');
            }
        $fydp =$fydp->select();
        foreach ($fydp as $v){
            $v["img"] = getAvatar($v['user_id'],90);
        }
       return $fydp;
    }

    public function second_house_user_comment_list($second_house_id,$user_id){
        $obj     = model('fydp')->alias('s');
        $fydp =$obj->join([['user m','m.id = s.user_id']])->join([['user_info info','info.user_id = s.user_id']])
            ->field('s.id,s.user_id,s.house_name,s.house_id,m.nick_name,m.lxtel,info.history_complate,m.kflj')
            ->where('s.house_id',$second_house_id)->where('s.model','second_house')->where("s.user_id",'<>',$user_id)
            ->group('s.user_id')->find();
        return $fydp;
    }
    /**
     * 获取小区的所有房源
     * @param $estate_id 小区id
     * @param string $limit 条数
     * @param mixed
     * @return array|\PDOStatement|string|\think\Collection
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function estate_second($estate_id,$limit='10'){
        $estate_second = model('second_house')->where('estate_id',$estate_id)
            ->field('endtime,qipai,acreage,cjprice,average_price')
            ->where('fcstatus',175)->where('status',1)
            ->cache('estate_second_house'.$estate_id,'84000')
            ->limit($limit)->select();
        return $estate_second;
    }

    /**
     * 获取新增房源
     * @param mixed
     * @return mixed
     * @author: al
     */
    public function get_today_add(){
        $time    = time();
        $field ="id,title";
        $house =model('second_house')->field($field)->where([['status','=',1],["timeout",'>',$time]])
            ->cache("second_house_today_add",3600)->limit(20)->select();
        return $house;
    }
    /**
     * @param $xsname 房屋属性(住宅,商业..)
     * @param $jieduan 拍卖阶段
     * @param $qp_price 起拍价
     * @param $s_price 市场价
     * @param $is_commission 是否免佣金
     * @param $is_school 是否学校
     * @param $is_metro 是否地铁沿线
     * @param mixed
     * @return array
     * @author: al
     */
    public function get_house_characteristic($xsname,$jieduan,$marketprice=0,$is_commission=0,$is_school=0,$is_metro=0){
        if ($xsname != "变卖"){
            $arr[] = $xsname;
        }
        $arr[] = $jieduan;
        if (!empty($is_commission)){
            $arr[] = "免佣金";
        }
        if (!empty($qp_price)){
            $arr[] = $marketprice;
        }
        if (!empty($marketprice) && ($marketprice >=4) ){
            $arr[] = "六折房源";
        }
        if ($xsname == "变卖"){
            $arr[] = $xsname;
        }
        if (!empty($is_school)){
            $arr[] = "学校周边";
        }
        if (!empty($is_metro)){
            $arr[] = "地铁沿线";
        }
        return json_encode($arr,JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取一个特色标签用于拼接
     * @param $marketprice
     * @param $is_commission
     * @param $is_school
     * @param $is_metro
     * @param mixed
     * @author: al\
     */
    public function house_characteristic_one($marketprice,$is_commission,$is_school,$is_metro){
        $characteristic_all = $this->get_house_characteristic(1,1,$marketprice,$is_commission,$is_school,$is_metro);
        $characteristic_arr = json_decode($characteristic_all,true);
        if (count($characteristic_arr) < 2){
            return "";
        }
        $characteristic_real = array_slice($characteristic_arr,2);
        if (reset($characteristic_real)){
            return reset($characteristic_real);
        }else{
            return "";
        }
    }

    /**
     * 根据 区域/小区 获取用户感兴趣的房源
     * @param $city_id 城市id
     * @param $house_id 房源id
     * @param $estate_id 小区id
     * @param mixed
     * @return array|\PDOStatement|string|\think\Collection
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function get_recommend_house($city_id,$house_id,$estate_id){
        $model = model('second_house')->field("lng,lat")->where("id",$house_id)->find();
        $res = Db::connect('www_fangpaiwang')->query("SELECT id,title,room,living_room,toilet,acreage,fcstatus,price,img,qipai,(2 * 6378.137 * ASIN(	SQRT(POW( SIN( PI( ) * ( " . $model->lng. "- fang_second_house.lng ) / 360 ), 2 ) + COS( PI( ) * " .$model->lat. " / 180 ) * COS(  fang_second_house.lat * PI( ) / 180 ) * POW( SIN( PI( ) * ( " . $model->lat . "- fang_second_house.lat ) / 360 ), 2 )))) AS distance FROM `fang_second_house`
where  fcstatus=170 and status =1 and id != ".$house_id." ORDER BY distance ASC LIMIT 4");
        return $res;
    }

     /*
      *参数说明：
      *$lng  经度
      *$lat   纬度
      *$distance  周边半径  默认是500米（0.5Km）
      */
     public function returnSquarePoint($lng, $lat,$distance = 20) {
        $dlng =  2 * asin(sin($distance / (2 * 6371)) / cos(deg2rad($lat)));
        $dlng = rad2deg($dlng);
        $dlat = $distance/6371;//地球半径，平均半径为6371km
        $dlat = rad2deg($dlat);
         return array(
            'left-top'=>array('lat'=>$lat + $dlat,'lng'=>$lng-$dlng),
            'right-top'=>array('lat'=>$lat + $dlat, 'lng'=>$lng + $dlng),
            'left-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng - $dlng),
            'right-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng + $dlng)
         );

     }
     function Distance($longitude,$latitude,$house_id){

         $array = $this->returnSquarePoint($longitude, $latitude);
         $res = model('second_house')
             ->field('id,title,room,living_room,toilet,acreage,fcstatus,price,img,qipai')
             ->where([['lat','EGT',$array['right-bottom']['lat']],["lat",'ELT',$array['left-top']['lat']],
                 ["lng",'EGT',$array['left-top']['lng']],["lng",'ELT',$array['right-bottom']['lng']],
             ["fcstatus","=",170] ])
             ->field('id,title,room,living_room,toilet,acreage,fcstatus,price,img,qipai')
             ->limit(4)
             ->select();
         return $res;
         echo $res->getLastSql();
         die(); //打印thinkPHP中对SQL语句

     }
    function get_house($limit){
        $field ="id,title,room,living_room,toilet,acreage,fcstatus,price,img,qipai";
        $res = model('second_house')->field($field)->where()->select();
        return $res;
    }
    /**
     * 获取小区的房源-套数
     * @param $second_house_id 房源id
     * @param $estate_name 小区名称
     * @param mixed todo 修改测试套数
     * @return float|string
     * @author: al
     */
    public function estate_second_num($second_house_id,$estate_name){
        $estate_second_house_num ='estate_second_house_num_'.$estate_name;
        $estate_num = model('second_house')->where('estate_name',$estate_name)
            ->cache($estate_second_house_num,3600)->count();
        return $estate_num;
    }
    /**
     * 列表页搜索栏数据
     * @param int $area_id
     * @param mixed
     * @return array
     * @author: al
     */
    function list_page_search_field($area_id =0){
        //todo 城市id 问题
        $res = [];
        //获取区域
        $city_info = getCity();
        $res[] = $this->area_one_arr($city_info[39]);

        //获取街道

        if (!empty($area_id) && $area_id < 100 && $area_id != 39){
            $res[] = $this->street_one_arr($city_info[39],$area_id);
        }
        //获取形式
        $res[] =$this->get_linkmenu_one_arr(getLinkMenuCache(9),9);
        //获取类型
        $res[]=$a =$this->get_linkmenu_one_arr(getLinkMenuCache(26),2);
        //总价
        $res[] =$this->get_linkmenu_one_arr(getSecondPrice(),"area");
        //面积
        $res[] =$this->get_linkmenu_one_arr(getAcreage(),"price");
        //户型
        $res[] =["一室","二室","三室","四室","五室","五室以上"];
        //阶段
        $res[] =$this->get_linkmenu_one_arr(getLinkMenuCache(25),25);
        //状态
        $res[] =$this->get_linkmenu_one_arr(getLinkMenuCache(27),27);
        //时间
        $res[] =array_values(config('filter.all_search'));
        return $res;
    }

    /**
     * 类型一维数组
     * @param mixed
     * @return array|mixed
     * @author: al
     */
    function get_linkmenu_one_arr($arr,$num){
        $cache_name = "get_linkmenu_one_arr_".$num;
        $type_cache = cache('house_type_one_arr');
        if (!$type_cache){
            $type_cache = ['全部'];
            foreach ($arr as $v){
                $type_cache[] = $v['name'];
            }
            cache($cache_name,$type_cache,86400);
        }
       return $type_cache;
    }
    /**
     * 获取自己区域数据(一维数组)
     * @param $city
     * @param mixed
     * @return array|mixed
     * @author: al
     */
    public function area_one_arr($city){
        $cache_name = 'area_one_'.$city['id'];
        $city_cache =  cache($cache_name);
        if (!$city_cache){
            $city_cache = ['全部'];
            foreach ($city['_child'] as $k=>$v){
                $city_cache[] = $v['name'];
            }
            cache($cache_name,$city_cache,86400);
        }
        return $city_cache;
    }

    /**
     * 获取指定街道的一维数组
     * @param $area
     * @param $street
     * @param mixed
     * @return array|mixed
     * @author: al
     */
    public function street_one_arr($area,$street){
        $cache_name = 'street_one_'.$area['id'];
        $street_cache =  cache($cache_name);
        if (!$street_cache){
            $street_cache = ['全部'];
            foreach ($area['_child'][$street]['_child'] as $k=>$val){
                $street_cache[] = $val['name'];
            }
            cache($cache_name,$street_cache,86400);
        }
        return $street_cache;
    }
    /**
     * 获取法拍专员(带聊天图片)
     * @param mixed
     * @return array|mixed|\PDOStatement|string|\think\Collection
     * @author: al
     */
    public function user_fapai(){
        $user_fapai_arr = cache("user_yewu");
        if (!$user_fapai_arr){
        $user_fapai_arr = model('user')->alias('s')
            ->join([['user_info info','info.user_id = s.id']])
            ->field('s.id,s.nick_name,s.lxtel,info.history_complate,s.kflj')
            ->where([['s.kflj','neq',''],['model','=',4]])
            ->group('s.id')
            ->select();
            foreach ($user_fapai_arr as &$house){
                $house['avatar'] = getAvatar($house->id,90);
            }
            cache("user_yewu",$user_fapai_arr,3600);
        }
        return $user_fapai_arr;
    }
    /**
    *  拼接jdk参数
     */
    function seo_data($seo_title='',$title_area='',$type=''){
        if($type==45||$type==0){
            $seo['seo_title'] = $seo_title.'法拍房,法拍房源信息-金铂顺昌房拍网';
            $seo['seo_keys']  = $seo_title.'法拍房,法拍房源信息,金铂顺昌房拍网';
            $seo['seo_desc']  = '金铂顺昌房拍网为北京知名网络司法房产拍卖，法拍房辅拍机构，专业从事15年。网站汇聚'.$title_area.'网上司法拍卖房源信息，更多法拍房源信息就到金铂顺昌房拍网。';
        }elseif($type==46){
            $seo['seo_title'] = $seo_title.'法拍房,国有资产拍卖房源信息-金铂顺昌房拍网';
            $seo['seo_keys']  = $seo_title.'法拍房,国有资产拍卖信息,金铂顺昌房拍网';
            $seo['seo_desc']  = '金铂顺昌房拍网为北京知名网络司法房产拍卖，法拍房辅拍机构，专业从事15年。网站汇聚'.$title_area.'网上司法拍卖国有资产信息，更多法拍房源信息就到金铂顺昌房拍网。';
        }elseif($type==47){
            $seo['seo_title'] = $seo_title.'法拍房,涉诉房产拍卖房源信息-金铂顺昌房拍网';
            $seo['seo_keys']  = $seo_title.'法拍房,涉诉房产拍卖信息,金铂顺昌房拍网';
            $seo['seo_desc']  = '金铂顺昌房拍网为北京知名网络司法房产拍卖，法拍房辅拍机构，专业从事15年。网站汇聚'.$title_area.'网上司法拍卖涉诉房产信息，更多法拍房源信息就到金铂顺昌房拍网。';
        }elseif($type==48){
            $seo['seo_title'] = $seo_title.'法拍房,社会委托拍卖房源信息-金铂顺昌房拍网';
            $seo['seo_keys']  = $seo_title.'法拍房,社会委托拍卖信息,金铂顺昌房拍网';
            $seo['seo_desc']  = '金铂顺昌房拍网为北京知名网络司法房产拍卖，法拍房辅拍机构，专业从事15年。网站汇聚'.$title_area.'网上司法拍社会委托信息，更多法拍房源信息就到金铂顺昌房拍网。';
        }
        return $seo;
    }
    /**
     *    分解url路径
     */
    public function decompose($url,$area_id){
        $param['area'] = $area_id;
        $param['rading']        = 0;
        $param['tags']          = 0;
        $param['qipai']         = 0;
        $param['acreage']       = 0;//面积
        $param['room']          = 0;//户型
        $param['types']         = 0;//户型
        $param['jieduan']       = 0;//户型
        $param['fcstatus']      = 0;//状态
        $param['type']          = 0;//物业类型
        $param['renovation']    = 0;//装修情况
        $param['metro']         = 0;//地铁线
        $param['metro_station'] = 0;//地铁站点
        $param['sort']          = 0;//排序
        $param['is_free']       = 0;//自由购
        $param['orientations']  = 0;//朝向
        $param['user_type']     = 0;//1个人房源  2中介房源
        $param['search_type']   = 0;//查询方式 1按区域查询 2按地铁查询
        $param['time_frame']    = 0;//查询时间
        $param['end_time']      = 0;//查询时间
        $param['zprice1']       = 0;//查询价格
        $param['zprice2']       = 0;//查询价格
        $param['zmianji1']      = 0;//查询面积
        $param['zmianji2']      = 0;//查询面积
        $param['keyword']      = '';//查询面积
        $array=preg_split("/(?=[a-z])/",$url);
        unset($array[0]);
        $str = array();
        foreach($array as $k=>$v){
            $str[] = array('letter'=>substr($v,0,1),'keyword'=>substr($v,1));
        }
        foreach($str as $k=>$v){
            if($v['letter']=='a'){
                $param['area'] = $v['keyword'];
            }elseif($v['letter']=='b'){
                if($param['tags']==0){
                    $param['tags'] = $v['keyword'];
                }else{
                    $param['tags'] = $param['tags'].','.$v['keyword'];
                }
            }elseif($v['letter']=='c'){
                $param['qipai'] = $v['keyword'];
            }elseif($v['letter']=='d'){
                $param['acreage'] = $v['keyword'];
            }elseif($v['letter']=='e'){
                if($param['room']==0){
                    $param['room'] = $v['keyword'];
                }else{
                    $param['room'] = $param['room'].','.$v['keyword'];
                }
            }elseif($v['letter']=='f'){
                if($param['types']==0){
                    $param['types'] = $v['keyword'];
                }else{
                    $param['types'] = $param['types'].','.$v['keyword'];
                }
            }elseif($v['letter']=='g'){
                if($param['jieduan']==0){
                    $param['jieduan'] = $v['keyword'];
                }else{
                    $param['jieduan'] = $param['jieduan'].','.$v['keyword'];
                }
            }elseif($v['letter']=='h'){
                $param['fcstatus'] = $v['keyword'];
            }elseif($v['letter']=='i'){
                $param['type'] = $v['keyword'];
            }elseif($v['letter']=='j'){
                if($param['renovation']==0){
                    $param['renovation'] = $v['keyword'];
                }else{
                    $param['renovation'] = $param['renovation'].','.$v['keyword'];
                }
            }elseif($v['letter']=='k'){
                if($param['metro']==0){
                    $param['metro'] = $v['keyword'];
                }else{
                    $param['metro'] = $param['metro'].','.$v['keyword'];
                }
            }elseif($v['letter']=='l'){
                if($param['metro_station']==0){
                    $param['metro_station'] = $v['keyword'];
                }else{
                    $param['metro_station'] = $param['metro_station'].','.$v['keyword'];
                }
            }elseif($v['letter']=='m'){
                $param['sort'] = $v['keyword'];
            }elseif($v['letter']=='n'){
                if($param['is_free']==0){
                    $param['is_free'] = $v['keyword'];
                }else{
                    $param['is_free'] = $param['is_free'].','.$v['keyword'];
                }
            }elseif($v['letter']=='o'){
                if($param['orientations']==0){
                    $param['orientations'] = $v['keyword'];
                }else{
                    $param['orientations'] = $param['orientations'].','.$v['keyword'];
                }
            }elseif($v['letter']=='p'){
                if($param['user_type']==0){
                    $param['user_type'] = $v['keyword'];
                }else{
                    $param['user_type'] = $param['user_type'].','.$v['keyword'];
                }
            }elseif($v['letter']=='q'){
                if($param['search_type']==0){
                    $param['search_type'] = $v['keyword'];
                }else{
                    $param['search_type'] = $param['search_type'].','.$v['keyword'];
                }
            }elseif($v['letter']=='r'){
                $param['time_frame'] = $v['keyword'];
            }elseif($v['letter']=='s'){
                $param['end_time'] = $v['keyword'];
            }elseif($v['letter']=='t'){
                $param['zprice1'] = $v['keyword'];
            }elseif($v['letter']=='u'){
                $param['zprice2'] = $v['keyword'];
            }elseif($v['letter']=='v'){
                $param['zmianji1'] = $v['keyword'];
            }elseif($v['letter']=='w'){
                $param['zmianji2'] = $v['keyword'];
            }elseif($v['letter']=='x'){
                $param['keyword'] = $v['keyword'];
            }
        }
        return $param;
    }
    /**
     *    搜索参数转换为json串
     */
    function parameter_json($parameter,$keyword){
        $data = array();
        $status = 0;
        if($parameter!=''){
            $parameter=preg_split("/(?=[a-z])/",$parameter);
            unset($parameter[0]);
            foreach($parameter as $k=>$v){
                $data[] = array('id'=>substr($v,0,1),'number_str'=>substr($v,1));
                if(substr($v,0,1)=='x'){
                    $status = 1;
                }
            }
        }
        if($keyword!=''&&$status==0){
            $data[] = array('id'=>'x','number_str'=>$keyword);
        }
        return json_encode($data);
        /*if($parameter==''&&$keyword==''){
            return '';
        }elseif($parameter!=''&&$keyword==''){
            $parameter_arr = explode('|',$parameter);
            $data = array();
            foreach($parameter_arr as $k=>$v){
                $str = explode('.',$v);
                $data[] = array('id'=>$str[0],'number_str'=>$str[1]);
            }
            return json_encode($data);
        }elseif($parameter!=''&&$keyword!=''){
            $parameter_arr = explode('|',$parameter);
            $data = array();
            $status = 0;
            foreach($parameter_arr as $k=>$v){
                $str = explode('.',$v);
                $data[] = array('id'=>$str[0],'number_str'=>$str[1]);
                if($str[0]=='x'){
                    $status = 1;
                }
            }
            if($status==0){
                $data[] = array('id'=>'x','number_str'=>$keyword);
            }
            return json_encode($data);
        }elseif($parameter==''&&$keyword!=''){
            $data = array();
            $data[] = array('id'=>'x','number_str'=>$keyword);
            return json_encode($data);
        }*/
    }
    /**
     *    筛选参数
     */
    public function column($param){
        //形式
        $house_type = getLinkMenuCache(9);
        if($param['type']!=''){
            $param['type'] = explode(",",$param['type']);
            $param['type'] = array_unique($param['type']);
            foreach($house_type as $k=>$v){
                $house_type[$k]['status'] = 0;
                foreach($param['type'] as $ks=>$vs){
                    if($v['id']==$vs) {
                        $house_type[$k]['status'] = 1;
                    }
                }
            }
            array_unshift($house_type,Array('id'=>0,'pid'=>0,'name'=>'全部','alias'=>'quanbu','status'=>0));
        }else{
            foreach($house_type as $k=>$v){
                $house_type[$k]['status'] = 0;
            }
            array_unshift($house_type,Array('id'=>0,'pid'=>0,'name'=>'全部','alias'=>'quanbu','status'=>1));
        }
        //类型
        $types = getLinkMenuCache(26);
        //print_r($types);die;
        if($param['types']!=''){
            $param['types'] = explode(",",$param['types']);
            $param['types'] = array_unique($param['types']);
            foreach($types as $k=>$v){
                $types[$k]['status'] = 0;
                foreach($param['types'] as $ks=>$vs){
                    if($v['id']==$vs) {
                        $types[$k]['status'] = 1;
                    }
                }
            }
            array_unshift($types,Array('id'=>0,'pid'=>0,'name'=>'全部','alias'=>'quanbu','status'=>0));
        }else{
            foreach($types as $k=>$v){
                $types[$k]['status'] = 0;
            }
            array_unshift($types,Array('id'=>0,'pid'=>0,'name'=>'全部','alias'=>'quanbu','status'=>1));
        }
        //户型
        $getRoom = $this->roomSplicing($param['room']);
        //阶段
        $jieduan = $this->stageSplicing($param['jieduan']);
        return array('jieduan'=>$jieduan,'huxing'=>$getRoom,'types'=>$types,'house_type'=>$house_type);
    }

    /**
     * 获取户型
     * @param int $room
     * @param mixed
     * @return array
     * @author: al
     */
    public function roomSplicing($room=0){
        $getRooms = getRoom('','s.room');
        $getRoom = array();
        foreach($getRooms as $k=>$v){
            $getRoom[$k]['id'] = $k;
            $getRoom[$k]['name'] = $v;
            $getRoom[$k]['status'] = 0;
        }
        if($room !=''){
            $room = explode(",",$room);
            $room = array_unique($room);
            foreach($getRoom as $k=>$v){
                foreach($room as $ks=>$vs){
                    if($v['id']==$vs) {
                        $getRoom[$k]['status'] = 1;
                    }
                }
            }
            array_unshift($getRoom,Array('id'=>0,'pid'=>0,'name'=>'全部','alias'=>'quanbu','status'=>0));
        }else{
            array_unshift($getRoom,Array('id'=>0,'pid'=>0,'name'=>'全部','alias'=>'quanbu','status'=>1));
        }
        return $getRoom;
    }
    /**
     * 阶段
     * @param int $stage
     * @param mixed
     * @return string
     * @author: al
     */
    public function stageSplicing($stage= 0){
        $jieduan = getLinkMenuCache(25);
        if($stage !=''){
            $stage = explode(",",$stage);
            $stage = array_unique($stage);
            foreach($jieduan as $k=>$v){
                $jieduan[$k]['status'] = 0;
                foreach($stage as $ks=>$vs){
                    if($v['id']==$vs) {
                        $jieduan[$k]['status'] = 1;
                    }
                }
            }
            array_unshift($jieduan,Array('id'=>0,'pid'=>0,'name'=>'全部','alias'=>'quanbu','status'=>0));
        }else{
            foreach($jieduan as $k=>$v){
                $jieduan[$k]['status'] = 0;
            }
            array_unshift($jieduan,Array('id'=>0,'pid'=>0,'name'=>'全部','alias'=>'quanbu','status'=>1));
        }
        return $jieduan;
    }
    /**
     *    组合url路径
     */
    public function recombination($parameter,$keyword){

       /* $url = array();
        if($param['area']>0){       $url[] = 'a.'.$param['area'];}
        if($param['tags']>0){       $url[] = 'b.'.$param['tags'];}
        if($param['qipai']>0){      $url[] = 'c.'.$param['qipai'];}
        if($param['acreage']>0){    $url[] = 'd.'.$param['acreage'];}
        if($param['room']>0){       $url[] = 'e.'.$param['room'];}
        if($param['types']>0){      $url[] = 'f.'.$param['types'];}
        if($param['jieduan']>0){    $url[] = 'g.'.$param['jieduan'];}
        if($param['fcstatus']>0){   $url[] = 'h.'.$param['fcstatus'];}
        if($param['type']>0){       $url[] = 'i.'.$param['type'];}
        if($param['renovation']>0){ $url[] = 'j.'.$param['renovation'];}
        if($param['metro']>0){      $url[] = 'k.'.$param['metro'];}
        if($param['metro_station']>0){$url[] = 'l.'.$param['metro_station'];}
        if($param['sort']>0){       $url[] = 'm.'.$param['sort'];}
        if($param['is_free']>0){    $url[] = 'n.'.$param['is_free'];}
        if($param['orientations']>0){$url[] = 'o.'.$param['orientations'];}
        if($param['user_type']>0){  $url[] = 'p.'.$param['user_type'];}
        if($param['search_type']>0){$url[] = 'q.'.$param['search_type'];}
        if($param['time_frame']>0){ $url[] = 'r.'.$param['time_frame'];}
        if($param['end_time']>0){   $url[] = 's.'.$param['end_time'];}
        if($param['zprice1']>0){    $url[] = 't.'.$param['zprice1'];}
        if($param['zprice2']>0){    $url[] = 'u.'.$param['zprice2'];}
        if($param['zmianji1']>0){   $url[] = 'v.'.$param['zmianji1'];}
        if($param['zmianji2']>0){   $url[] = 'w.'.$param['zmianji2'];}
        if($param['keyword']!=''){  $url[] = 'x.'.$param['keyword'];}
        if(empty($url)){
            return '';
        }else{
            $index_url = implode('|',$url);
            return $index_url;
        }*/
    }
    /**
    *   搜索关键字
     *  拼接where条件
     *  已选中的搜索条件数组$seo_array
     */
    function search_keyword($keyword){
        return array('title'=>array('s.title|s.contacts','like','%'.$keyword.'%'),'seo'=>array('letter'=>'x','keyword'=>$keyword,'id'=>0));
    }
    /**
     *  对时间搜索进行逻辑判断
     *  已选中的搜索条件数组$seo_array
     */
    function search_time($time_frame,$end_time){
        if ($time_frame == 1){
            $start_time = date('Y-m-d');
            $end_time = date('Y-m-d',strtotime( '+1 day'));
            $seo_title = '最近1天';
            $seo_array = array('letter'=>'r','keyword'=>'最近1天','id'=>1);
        }elseif ($time_frame == 3){
            $start_time = date('Y-m-d');
            $end_time = date('Y-m-d',strtotime( '+2 day'));
            $seo_title = '最近3天';
            $seo_array = array('letter'=>'r','keyword'=>'最近3天','id'=>3);
        }elseif ($time_frame == 7){
            $start_time = date('Y-m-d');
            $end_time = date('Y-m-d',strtotime( '+6 day'));
            $seo_title = '最近7天';
            $seo_array = array('letter'=>'r','keyword'=>'最近7天','id'=>7);
        }elseif ($time_frame == 30){
            $start_time = date('Y-m-d');
            $end_time = date('Y-m-d',strtotime( '+29 day'));
            $seo_title = '最近30天';
            $seo_array = array('letter'=>'r','keyword'=>'最近30天','id'=>30);
        }elseif (!empty($time_frame)){
            $start_time = $time_frame;
            $seo_title = '';
            $seo_array = array('letter'=>'rs','keyword'=>$time_frame.'到'.$end_time,'id'=>0);
        }else{
            $start_time = '';
            $end_time = '';
            $seo_title = '';
            $seo_array = array();
        }
        return array('start_time'=>$start_time,'end_time'=>$end_time,'seo_title'=>$seo_title,'seo_array'=>$seo_array);
    }
    /**
     *  对面积搜索进行逻辑判断
     *  已选中的搜索条件数组$seo_array
     */
    function search_acreage($acreage,$zmianji1,$zmianji2){
        if(!empty($acreage)){
            $data = getAcreage($acreage,'s.acreage');
            $acreages = config('filter.acreage');
            isset($acreages[$acreage]) && $seo_title = $acreages[$acreage]['name'];
            $zmianji1 = 0;
            $zmianji2 = 0;
            $seo_array = array('letter'=>'d','keyword'=>$acreages[$acreage]['name'],'id'=>$acreage);
        }elseif($zmianji1!=''&&$zmianji2!=''){
            $data = ['s.acreage','between',[$zmianji1,$zmianji2]];
            $seo_title = $zmianji1.'_'.$zmianji2.'m²';
            $seo_array = array('letter'=>'vw','keyword'=>$zmianji1.'_'.$zmianji2.'m²','id'=>$zmianji1);
        }else{
            $data = array();
            $seo_title = '';
            $seo_array = array();
        }
        return array('data'=>$data,'seo_title'=>$seo_title,'seo_array'=>$seo_array,'zmianji1'=>$zmianji1,'zmianji2'=>$zmianji2);
    }


}
<?php

namespace app\home\service;



use app\home\controller\Poster;
use function GuzzleHttp\debug_resource;
use think\console\command\make\Model;
use think\facade\Cache;
use think\Log;

class SecondServer
{
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
    public function second_house_user_comment($second_house_id){
        $obj     = model('fydp')->alias('s');
        $fydp=$obj->join([['user m','m.id = s.user_id']])->join([['user_info info','info.user_id = s.user_id']])
            ->field('s.id,s.user_id,s.house_name,s.house_id,m.nick_name,m.lxtel,info.history_complate,m.kflj')
            ->where('s.house_id',$second_house_id)->where('s.model','second_house')
            ->group('s.user_id')->limit(2)
            ->cache('fydp_'.$second_house_id,'3600')
            ->select();
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
        return json_encode($arr);
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
        $citys=$city_id;
        $map = "estate_id=$estate_id or city=$citys";
        $res = model('second_house')
            ->where('status',1)
            ->where('fcstatus','eq',170)
            ->where('id','neq',$house_id)
            ->where($map)
            ->field('id,title,room,living_room,toilet,acreage,fcstatus,price,img,qipai')
            ->order('id desc')
            ->limit(4)
            ->select();
        return $res;
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
     * @param mixed
     * @return float|string
     * @author: al
     */
    public function estate_second_num($second_house_id,$estate_name){
        $estate_second_house_num ='estate_second_house_num_'.$second_house_id.'_'.$estate_name;
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

}
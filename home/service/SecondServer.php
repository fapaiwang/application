<?php

namespace app\home\service;



use app\home\controller\Poster;
use think\console\command\make\Model;
use think\facade\Cache;
use think\Log;

class SecondServer
{
    public function second_info(){

    }
    public function second_model($second_house_id){
        $where['h.id']     = $second_house_id;
        $where['h.status'] = 1;
        $second_house_field  ="h.title,h.bianhao,h.qipai,h.types,h.price,h.marketprice,h.baozheng,h.toilet,h.floor,h.total_floor,
            d.file,d.house_id,
            h.orientations,h.acreage,h.contacts,h.estate_id,h.id,h.broker_id,h.city,h.jieduan,h.bianetime,h.kptime,h.types,
            h.bmrs,h.weiguan,h.img,h.basic_info";
        $obj  = model('second_house');
        $join = [['second_house_data d','h.id=d.house_id']];
//            todo  缓存
//            ->cache('second_house_'.$second_house_id,3600)
        $info = $obj->alias('h')
            ->field($second_house_field)
            ->join($join)->where($where)->find();
        //单价
        $info['junjia']=sprintf("%.2f",intval($info['qipai'])/intval($info['acreage'])*10000);
        //差价
        $info['chajia']=intval($info['price'])-intval($info['qipai']);
        //类型
        $xsname =getLinkMenuCache(26)[$info['types']] ??"";
        $info['xsname']=$xsname['name'] ?? "";
        //起拍价格
        $info['qp_price'] = substr($info['price'],0,-10);
        //成交价格
        $info['cjprice']=sprintf("%.2f",$info['cjprice']);
        $info['file'] = json_decode($info['file'],true);
        if (!empty($info['basic_info'])){
            $info['basic_info']=explode('|',$info['basic_info']);
        }
        return $info;
    }
    public function estate($estate_id){
        $estate =model('estate')
            ->field('title,years,address,data')
            ->where('id',$estate_id)
//            ->cache('estate'.$estate_id,84000)
            ->find();
//        dd($estate);
        return $estate;
    }

    /**
     *  经纪人信息
     * @param $second_house_id 房源id
     * @param $broker_id 经纪人id
     * @param mixed
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function user($second_house_id,$broker_id){
        $user_info = model('user')->field('history_complate')->where('user_id',$broker_id)
            ->cache('user_info_common_'.$second_house_id,84000)->find();
        return $user_info;
    }
    public function user_info($second_house_id,$broker_id){
        $user = model('user_info')->field('lxtel_zhuan,kflj')->where('id',$broker_id)
            ->cache('user_common_'.$second_house_id)->find();
        return $user;
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
        $res[] = $this->area_one_arr(getCity()[39]);
        //获取街道
        if (!empty($area_id)){
            $res[] = $this->street_one_arr(getCity()[39],$area_id);
        }
        //获取类型
        $res[] =$this->get_linkmenu_one_arr(getLinkMenuCache(9),9);
        //总价
        $res[] =$this->get_linkmenu_one_arr(getSecondPrice(),"area");
        //面积
        $res[] =$this->get_linkmenu_one_arr(getAcreage(),"price");
        //户型
        $res[] =$this->get_linkmenu_one_arr(getLinkMenuCache(4),4);
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
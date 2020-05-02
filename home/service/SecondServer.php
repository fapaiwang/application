<?php

namespace app\home\service;



use app\home\controller\Poster;
use think\console\command\make\Model;
use think\facade\Cache;
use think\Log;

class SecondServer
{

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
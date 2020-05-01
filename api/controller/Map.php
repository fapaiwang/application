<?php
namespace app\api\controller;
class Map extends \think\Controller
{
    private $city = 0;
    public function initialize()
    {
        parent::initialize();
        $city    = input('get.city/d',0);
        $default = $this->getDefaultCity($city);
        $this->city = $default['id'];
        $city_arr = getCity();
        $area = isset($city_arr[$this->city]['_child'])?$city_arr[$this->city]['_child']:$city_arr;
        $this->assign('lng',$default['lng']);
        $this->assign('lat',$default['lat']);
        $this->assign('area',$area);
        $this->assign('action',request()->action());
    }

    /**
     * @return mixed
     * 新房地图
     */
    public function index()
    {
        $this->assign('special',getLinkMenuCache(3));//特色
        $this->assign('type',getLinkMenuCache(2));//类型
        $this->assign('status',getLinkMenuCache(1));//销售状态
        return $this->fetch();
    }

    /**
     * @return mixed
     * 二手房地图
     */
    public function second()
    {
        $this->assign('type',getLinkMenuCache(9));
        $this->assign('renovation',getLinkMenuCache(8));//装修情况
        return $this->fetch();
    }

    /**
     * @return mixed
     * 出租房地图
     */
    public function rental()
    {
        $this->assign('rental_type',getLinkMenuCache(10));
        $this->assign('type',getLinkMenuCache(9));
        $this->assign('renovation',getLinkMenuCache(8));//装修情况
        return $this->fetch();
    }
    /**
     * @return mixed
     * 写字楼出售地图
     */
    public function office()
    {
        $this->assign('type',getLinkMenuCache(15));//类型
        $this->assign('tags',getLinkMenuCache(16));//标签
        return $this->fetch();
    }
    /**
     * @return mixed
     * 写字楼出租地图
     */
    public function officeRental()
    {
        $this->assign('type',getLinkMenuCache(15));//类型
        $this->assign('tags',getLinkMenuCache(16));//标签
        return $this->fetch();
    }
    /**
     * @return mixed
     * 商铺出售地图
     */
    public function shops()
    {
        $this->assign('type',getLinkMenuCache(18));//类型
        $this->assign('tags',getLinkMenuCache(20));//标签
        return $this->fetch();
    }
    /**
     * @return mixed
     * 商铺出租地图
     */
    public function shopsRental()
    {
        $this->assign('type',getLinkMenuCache(18));//类型
        $this->assign('tags',getLinkMenuCache(20));//标签
        return $this->fetch();
    }


    public function estate()
    {
        $this->assign('house_type',getLinkMenuCache(9));//类型
        return $this->fetch();
    }
    /**
     * @return \think\response\Json
     * 异步获取楼盘列表
     */
    public function getHouseLists()
    {
        $service = controller('home/House','service');
        $zoom    = input('get.zoom',12);
        $result  = $service->lists($zoom,$this->city);
        return json($result);
    }
    public function getSecondLists()
    {
        $service = controller('home/SecondHouse','service');
        $zoom    = input('get.zoom',12);
        $result  = $service->lists($zoom,$this->city);
        return json($result);
    }
    public function getRentalLists()
    {
        $service = controller('home/Rental','service');
        $zoom    = input('get.zoom',12);
        $result  = $service->lists($zoom,$this->city);
        return json($result);
    }
    public function getEstateLists()
    {
        $service = controller('home/Estate','service');
        $zoom    = input('get.zoom',12);
        $result  = $service->lists($zoom,$this->city);
        return json($result);
    }
    /**
     * @return \think\response\Json
     * 异步获取写字楼出售列表
     */
    public function getOfficeLists()
    {
        $service = controller('home/Office','service');
        $zoom    = input('get.zoom',12);
        $result  = $service->lists($zoom,$this->city);
        return json($result);
    }

    /**
     * @return \think\response\Json
     * 异步获取写字楼出租列表
     */
    public function getOfficeRentalLists()
    {
        $service = controller('home/OfficeRental','service');
        $zoom    = input('get.zoom',12);
        $result  = $service->lists($zoom,$this->city);
        return json($result);
    }

    /**
     * @return \think\response\Json
     * 异步获取商铺出售列表
     */
    public function getShopsLists()
    {
        $service = controller('home/Shops','service');
        $zoom    = input('get.zoom',12);
        $result  = $service->lists($zoom,$this->city);
        return json($result);
    }

    /**
     * @return \think\response\Json
     * 异步获取商铺出租列表
     */
    public function getShopsRentalLists()
    {
        $service = controller('home/ShopsRental','service');
        $zoom    = input('get.zoom',12);
        $result  = $service->lists($zoom,$this->city);
        return json($result);
    }
    /**
     * @return \think\response\Json
     * 获取指定城市id下级区域
     */
    public function getCityChild()
    {
        $pid = input('get.pid/d',0);
        $return['code'] = 0;
        if($pid)
        {
            $where['pid']    = $pid;
            $where['status'] = 1;
            $city = model('city')->where($where)->field('id,name,lat,lng')->order(['ordid'=>'asc','id'=>'desc'])->select();
            if(!$city->isEmpty())
            {
                $return['code'] = 1;
                $return['data'] = $city;
            }
            $return['points'] = $this->getCityPoint($pid);
        }
        return json($return);
    }
    /**
     * @return \think\response\Json
     * 获取城市坐标
     */
    private function getCityPoint($city)
    {
        $data = [];
        if($city)
        {
            $city_arr = getCity('cate');
            if(isset($city_arr[$city]))
            {
                $lng  = $city_arr[$city]['lng'];
                $lat  = $city_arr[$city]['lat'];
                $name = $city_arr[$city]['name'];
                $return['code'] = 1;
                $data           = ['lng'=>$lng,'lat'=>$lat,'name'=>$name];
            }
        }
        return $data;
    }

    /**
     * @return array|null|\PDOStatement|string|\think\Model
     * 获取默认城市，排序在最前的为默认城市
     */
    private function getDefaultCity($id = 0)
    {
        if($id)
        {
            $where['id'] = $id;
        }else{
            $where['status'] = 1;
            $where['pid']    = 0;
        }
        $info =  model('city')->field('id,name,lat,lng')->where($where)->order(['ordid'=>'asc','id'=>'asc'])->find();
        return $info;
    }
}
<?php





namespace app\mobile\controller;

use app\common\controller\MobileBase;

class Map extends MobileBase

{

    public function initialize()

    {

        parent::initialize();

        $lat         = 0;

        $lng         = 0;

        if($location = $this->getCityLocationById())

        {

            $lat = $location['lat'];

            $lng = $location['lng'];

        }

        $this->assign('lng',$lng);

        $this->assign('lat',$lat);

        $this->assign('action',request()->action());

    }



    /**

     * @return mixed

     * 新房地图

     */

    public function index()

    {

        $this->setSeo(['title'=>'地图找房']);

        $this->assign('area',$this->getAreaByCityId());

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

        $this->setSeo(['title'=>'地图找二手房']);

        $this->assign('area',$this->getAreaByCityId());

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

        $this->setSeo(['title'=>'地图找出租房']);

        $this->assign('area',$this->getAreaByCityId());

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

        $this->setSeo(['title'=>'写字楼出售']);

        $this->assign('area',$this->getAreaByCityId());

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

        $this->setSeo(['title'=>'写字楼出租']);

        $this->assign('area',$this->getAreaByCityId());

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

        $this->setSeo(['title'=>'商铺出售']);

        $this->assign('area',$this->getAreaByCityId());

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

        $this->setSeo(['title'=>'商铺出租']);

        $this->assign('area',$this->getAreaByCityId());

        $this->assign('type',getLinkMenuCache(18));//类型

        $this->assign('tags',getLinkMenuCache(20));//标签

        return $this->fetch();

    }

    /**

     * @return mixed

     * 小区地图

     */

    public function estate()

    {

        $this->setSeo(['title'=>'地图找小区']);

        $this->assign('area',$this->getAreaByCityId());

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

        $result  = $service->lists($zoom,$this->cityInfo['id']);

        return json($result);

    }



    /**

     * @return \think\response\Json

     * 异步获取二手房列表

     */

    public function getSecondLists()

    {

        $service = controller('home/SecondHouse','service');

        $zoom    = input('get.zoom',50);

        // print_r($service);
        // print_r($zoom);

        $result  = $service->lists($zoom,$this->cityInfo['id']);

        return json($result);

    }




    /**

     * @return \think\response\Json

     * 异步获取出租房列表

     */

    public function getRentalLists()

    {

        $service = controller('home/Rental','service');

        $zoom    = input('get.zoom',12);

        $result  = $service->lists($zoom,$this->cityInfo['id']);

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

        $result  = $service->lists($zoom,$this->cityInfo['id']);

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

        $result  = $service->lists($zoom,$this->cityInfo['id']);

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

        $result  = $service->lists($zoom,$this->cityInfo['id']);

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

        $result  = $service->lists($zoom,$this->cityInfo['id']);

        return json($result);

    }



    /**

     * @return \think\response\Json

     * 异步获取小区列表

     */

    public function getEstateLists()

    {

        $service = controller('home/Estate','service');

        $zoom    = input('get.zoom',12);

        $result  = $service->lists($zoom,$this->cityInfo['id']);

        return json($result);

    }

    /**

     * @return array|null|\PDOStatement|string|\think\Model

     *根据城市id获取经纬度

     */

    private function getCityLocationById()

    {

        $where['id'] = $this->cityInfo['id'];

        $info = model('city')->where($where)->field('lat,lng')->find();

        return $info;

    }

}
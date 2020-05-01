<?php



namespace app\home\controller;

use app\common\controller\HomeBase;

class Map extends HomeBase

{

    public function initialize()

    {

        parent::initialize();

        $lat = 0;

        $lng = 0;

        if($location = $this->getCityLocationById())

        {

            $lat = $location['lat'];

            $lng = $location['lng'];

        }

        $this->assign('lng',$lng);

        $this->assign('lat',$lat);

    }



    /**

     * @return mixed

     * 新房地图

     */

    public function index()

    {

        $this->setSeo(['title'=>'地图找房']);

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

        $this->assign('renovation',getLinkMenuCache(8));//装修情况

        return $this->fetch();

    }

    public function estate()

    {

        $this->setSeo(['title'=>'地图找小区']);

        $this->assign('house_type',getLinkMenuCache(9));//类型

        return $this->fetch();

    }

    public function office()

    {

        $this->setSeo(['title'=>'写字楼出售']);

        $this->assign('house_type',getLinkMenuCache(15));//类型

        $this->assign('special',getLinkMenuCache(16));//特色

        return $this->fetch();

    }

    public function officeRental()

    {

        $this->setSeo(['title'=>'写字楼出租']);

        $this->assign('house_type',getLinkMenuCache(15));//类型

        $this->assign('special',getLinkMenuCache(16));//特色

        return $this->fetch();

    }

    public function Shops()

    {

        $this->setSeo(['title'=>'商铺出售']);

        $this->assign('house_type',getLinkMenuCache(18));//类型

        $this->assign('special',getLinkMenuCache(20));//特色

        return $this->fetch();

    }

    public function ShopsRental()

    {

        $this->setSeo(['title'=>'商铺出租']);

        $this->assign('house_type',getLinkMenuCache(18));//类型

        $this->assign('special',getLinkMenuCache(20));//特色

        return $this->fetch();

    }

    /**

     * @return \think\response\Json

     * 异步获取楼盘列表

     */

    public function getHouseLists()

    {

        $service = controller('House','service');

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

        $service = controller('SecondHouse','service');

        $zoom    = input('get.zoom',12);

        $result  = $service->lists($zoom,39);
// print_r($result);
// exit();

        return json($result);

    }



    /**

     * @return \think\response\Json

     * 异步获取出租房列表

     */

    public function getRentalLists()

    {

        $service = controller('Rental','service');

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

        $service = controller('Estate','service');

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

        $service = controller('Office','service');

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

        $service = controller('OfficeRental','service');

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

        $service = controller('ShopsRental','service');

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

        $service = controller('Shops','service');

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
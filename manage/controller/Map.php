<?php


namespace app\manage\controller;
class Map extends \think\Controller
{
    /**
     * @return \think\response\Json
     * 根据地址获取经纬度
     */
    public function getLocationByAddress()
    {
        $city = input('get.city/d',0);
        $city_name = getCityName($city);
        $address = input('get.address');
        $ak      = config('baidu_map_ak');
        $url = "http://api.map.baidu.com/geocoder/v2/?address={$address}&city={$city_name}&output=json&ak={$ak}";
        $result = file_get_contents($url);
        $result = json_decode($result,true);
        $return['code'] = 0;
        if($result['status'] == 0)
        {
            $return['code'] = 1;
            $map['lng'] = $result['result']['location']['lng'];
            $map['lat'] = $result['result']['location']['lat'];
            $map['map'] = $map['lng'].','.$map['lat'];
            $return['data'] = $map;
        }
        return json($return);
    }
    public function updateLocation()
    {
        $location = input('get.map');
        $city = input('get.city/d',0);
        $lng = 0;//'110.211023,';
        $lat = 0;//'20.007536';
        if($location && strpos($location,',')!==false)
        {
            $map = explode(',',$location);
            $lng = $map[0];
            $lat = isset($map[1]) ? $map[1] : 0;
        }
        $this->assign('lng',$lng);
        $this->assign('lat',$lat);
        $this->assign('ak',config('baidu_map_ak'));
        $this->assign('city_name',getCityName($city));
        $this->assign('city',$city);
        return $this->fetch('map_mark');
    }
}
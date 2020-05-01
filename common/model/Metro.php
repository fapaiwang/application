<?php


namespace app\common\model;


class Metro extends \think\Model
{
    /**
     * @param $city
     * @param $lat
     * @param $lng
     * @return mixed
     * 计算指定经纬度到地铁站的距离
     */
    public function getDistance($city,$lat,$lng)
    {
        $data = [];
        $spid = model('city')->where('id',$city)->value('spid');
        if($spid != 0)
        {
            $arr = explode('|',$spid);
            $city_id = $arr[0];
        }else{
            $city_id = $city;
        }
        $join       = [['metro_station s','m.id = s.metro_id','left']];
        $where['m.status'] = 1;
        $where['s.status'] = 1;
        $where['m.city']   = $city_id;
        $point      = "m.id,m.name,m.status,s.id as s_id,s.name as station_name,ROUND(6378.138*2*ASIN(SQRT(POW(SIN(({$lat}*PI()/180-s.lat*PI()/180)/2),2)+COS({$lat}*PI()/180)*COS(s.lat*PI()/180)*POW(SIN(({$lng}*PI()/180-s.lng*PI()/180)/2),2)))*1000) as distance";
        $bindsql    = $this->alias('m')->field($point)->join($join)->where($where)->buildSql();
        $fields_res = 'id,s_id,name,station_name,distance';
        $info       = $this->table($bindsql.' d')->field($fields_res)->order('distance asc')->find();
        if($info)
        {
            $minute = ceil($info['distance'] / 65);
            $data['minute']   = $minute;
            $data['distance'] = $info['distance'];
            $data['metro_id'] = $info['id'];
            $data['metro_station'] = $info['s_id'];
        }
        return $data;
    }
}
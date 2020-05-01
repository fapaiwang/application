<?php

namespace app\home\service;
class SecondHouse
{
    public function lists($zoom,$city = 0)
    {
        $return['code'] = 0;
        $sort   = input('get.sort/d',0);
        $where  = $this->search($city);
        $field  = 'id,title,city,estate_name,room,living_room,img,renovation,price,average_price,acreage,tags,lng,lat,estate_id';
        $obj    = model('second_house');
        $mod    = $obj->where($where)
            ->field($field)
            ->order($this->getSort($sort));
        if($zoom < 13)
        {
            $lists = $mod->select();
        }else{
            $lists = $mod->limit(100)->select();
        }
        $estate = $obj->where($where)->field('estate_id as id,estate_name as title,lat,lng,count(id) as price')->group('estate_id')->select();
        if($lists)
        {
            foreach($lists as &$v)
            {
                $v['url'] = url('Second/detail',['id'=>$v['id']]);
                $v['w_url'] = "/pages/second/detail/index?id=".$v['id'];
                $v['renovation'] = getLinkMenuName(8,$v['renovation']);
                $temp = array_filter(explode(',',$v['tags']));
                $tmp  = [];
                foreach($temp as $val)
                {
                    if(is_numeric($val))
                    {
                        $tmp[] = getLinkMenuName(14,$val);
                    }else{
                        $tmp[] = $val;
                    }
                }
                $v['tags']  = $tmp;
            }
            if($estate)
            {
                $estate = $estate->toArray();
                foreach($estate as &$val)
                {
                    $val['price'] = str_replace('<i>万</i>','',$val['price']);
                    $val['url'] = url('Second/index',['estate_id'=>$val['id']]);
                    $val['w_url'] = "/pages/second/index/index?estate_id=".$val['id'];
                }
            }
            $return['code']   = 1;
            $return['data']   = $lists;
            $return['estate'] = $estate;
            if($zoom < 13)
            {
                $return['countData'] = $this->countHouse($lists,$city);
            }
            $return['zoom'] = $zoom;
        }

        return $return;
    }
    /**
     * @param $lists
     * @return array
     * 统计楼盘均价及数量
     */
    private function countHouse($lists,$city_id)
    {
        $cityChild = $this->getCityChildArr($city_id);
        $city_arr      = getCity('cate');
        $temp = [];
        if($lists)
        {
            $data = [];
            foreach($lists as $v)
            {
                $city = $city_arr[$v['city']]['pid'] != $city_id ? $city_arr[$v['city']]['pid'] : $v['city'];
                isset($cityChild[$city]) && $data[$city][] = $v;
            }
            foreach($data as $key=>$val)
            {
                $sum       = array_sum(array_column($val, 'average_price'));
                $count     = count($val);
                $city_name = $city_arr[$key]['name'];
                $lat    = $city_arr[$key]['lat'];
                $lng    = $city_arr[$key]['lng'];
                $temp[] = [
                    'price' => ceil($sum / $count).config('filter.second_price_unit'),
                    'city_name' => $city_name,
                    'total'     => $count.'套房源',
                    'lat'       => $lat,
                    'lng'       => $lng
                ];
            }
        }
        return $temp;
    }

    /**
     * @return array
     * 得到城市下级区域 械为 ['2'=>1,3=>1,4=>1],key为下级区域 值 为城市id
     */
    private function getCityChildArr($city_id)
    {
        $city_arr = getCity('tree');
        $temp = [];
        $city = isset($city_arr[$city_id]['_child'])?$city_arr[$city_id]['_child']:[];
        foreach($city as $v)
        {
            $temp[$v['id']] = $v['pid'] ==0 ? $v['id'] : $v['pid'];
        }
        return $temp;
    }
    /**
     * @return array
     * 搜索条件
     */
    private function search($city)
    {
        $param['area']       = input('param.area',0);
        $param['price']      = input('param.price',0);
        $param['acreage']    = input('param.acreage',0);
        $param['room']       = input('param.room',0);//户型
        $param['renovation'] = input('param.renovation',0);//楼盘状态
        $param['type']       = input('param.type/d',0);
        $param['sort']       = input('param.sort/d',0);//排序
        $bssw_lat            = input('get.bssw_lat');//地图可视区域左下角纬度
        $bssw_lng            = input('get.bssw_lng');//地图可视区域左上角纬度
        $bsne_lat            = input('get.bsne_lat');//地图可视区域右下角纬度
        $bsne_lng            = input('get.bsne_lng');//地图可视区域右上角纬度
        $param['area']       == 0 && $param['area'] = $city;
        $data['status']      = 1;
        $param['keyword']    = input('get.keyword');
        $data[] = ['timeout','gt',time()];
        if($param['keyword']){
            $data[] = ['title|estate_name','like','%'.$param['keyword'].'%'];
        }
        if(!empty($param['area'])){
            $city_ids = model('city')->get_child_ids($param['area'],true);
            $data[] = ['city','in',$city_ids];
        }
        if(!empty($param['price'])){
            $data[] = getSecondPrice($param['price']);
        }
        if(!empty($param['acreage'])){
            $data[] = getAcreage($param['acreage']);
        }
        if(!empty($param['type']))
        {
            $data['house_type'] = $param['type'];
        }
        if(!empty($param['room'])){
            $data[] = getRoom($param['room']);
        }
        if(!empty($param['renovation'])){
            $data['renovation'] = $param['renovation'];
        }

        if($bsne_lat && $bssw_lat && $bssw_lng && $bsne_lng)
        {
            $data[] = ['lat','between',[min($bssw_lat,$bsne_lat),max($bssw_lat,$bsne_lat)]];
            $data[] = ['lng','between',[min($bssw_lng,$bsne_lng),max($bssw_lng,$bsne_lng)]];
        }

        return $data;
    }

    /**
     * @param $sort
     * @return array
     * 排序
     */
    private function getSort($sort)
    {
        switch($sort)
        {
            case 0:
                $order = ['ordid'=>'asc','id'=>'desc'];
                break;
            case 1:
                $order = ['price'=>'asc'];
                break;
            case 2:
                $order = ['price'=>'desc'];
                break;
            case 3:
                $order = ['acreage'=>'asc'];
                break;
            case 4:
                $order = ['acreage'=>'desc'];
                break;
            default:
                $order = ['ordid'=>'asc','id'=>'desc'];
                break;
        }
        return $order;
    }
}
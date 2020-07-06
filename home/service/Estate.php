<?php


namespace app\home\service;
class Estate
{
    public function lists($zoom,$city = 0)
    {
        $return['code'] = 0;
        $sort   = input('get.sort/d',0);
        $where  = $this->search($city);
        $field   = 'id,title,city,lat,lng,img,house_type,years,address,price';
        //统计二手房和出租房数量 where条件里需要替换estate_id为estate表每条记录的id, 不能用字符(会被转成 0)所以用9999代替替换
        $second_sql = model('second_house')->where('estate_id','9999')->where('status',1)->where('timeout','gt',time())->field('count(id) as second_total')->buildSql();
        $rental_sql = model('rental')->where('estate_id','9999')->where('status',1)->where('timeout','gt',time())->field('count(id) as rental_total')->buildSql();
        $field .= ','.$second_sql.' as second_total,'.$rental_sql.' as rental_total';
        $field  = str_replace('9999','e.id',$field);
        $obj    = model('estate');
        $mod    = $obj->alias('e')
            ->where($where)
            ->field($field)
            ->order($this->getSort($sort));
        if($zoom < 13)
        {
            $lists      = $mod->select();
        }else{
            $lists      = $mod->limit(100)->select();
        }
        if($lists)
        {
            foreach($lists as &$v)
            {
                $v['url'] = url('Estate/detail',['id'=>$v['id']]);
                $v['w_url'] = "/pages/estate/detail/index?id=".$v['id'];
                $v['unit'] = config('filter.second_price_unit');
            }
            $return['code']   = 1;
            $return['data']   = $lists;
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
                $sum       = array_sum(array_column($val, 'price'));
                $count     = count($val);
                $city_name = $city_arr[$key]['name'];
                $lat    = $city_arr[$key]['lat'];
                $lng    = $city_arr[$key]['lng'];
                $temp[] = [
                    'price' => ceil($sum / $count).config('filter.second_price_unit'),
                    'city_name' => $city_name,
                    'total'     => $count.'个小区',
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
        $param['years']      = input('param.years',0);
        $param['type']       = input('param.type',0);//户型
        $param['sort']       = input('param.sort/d',0);//排序
        $bssw_lat            = input('get.bssw_lat');//地图可视区域左下角纬度
        $bssw_lng            = input('get.bssw_lng');//地图可视区域左上角纬度
        $bsne_lat            = input('get.bsne_lat');//地图可视区域右下角纬度
        $bsne_lng            = input('get.bsne_lng');//地图可视区域右上角纬度
        $param['area']       == 0 && $param['area'] = $city;
        $data['status']      = 1;
        $param['keyword']    = input('get.keyword');
        if($param['keyword']){
            $data[] = ['title','like','%'.$param['keyword'].'%'];
        }
        if(!empty($param['area'])){
            $city_ids = model('city')->get_child_ids($param['area'],true);
            $data[] = ['city','in',$city_ids];
        }
        if(!empty($param['price'])){
            $data[] = getEstatePrice($param['price']);
        }
        if(!empty($param['years'])){
            $data[] = getYears($param['years']);
        }
        if(!empty($param['type'])){
            $data['house_type'] = $param['type'];
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

            default:
                $order = ['ordid'=>'asc','id'=>'desc'];
                break;
        }
        return $order;
    }
    public function detail($id){
        $where['id'] = $id;
        $where['status'] = 1;
        $field="title,area_name,img,file,address,lng,lat,years,data,price";
        $info = model('estate')->field($field)->where($where)->find();
        if ($info) {
//            $info['price']  = $info['price'].config('filter.house_price_unit');
//            $info['tags']   = array_filter(explode(',',$info['tags']));
//            $info['house_type'] = getLinkMenuName(9,$info['house_type']);
            updateHits($info['id'], 'estate');//更新点击量
        }
        return $info;
    }
    public function getSecondByEstateId(){

    }


}
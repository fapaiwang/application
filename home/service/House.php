<?php


namespace app\home\service;
class House
{
    public function lists($zoom,$city = 0)
    {
        $return['code'] = 0;
        $where  = $this->search($city);
        $field  = "h.id,h.title,h.img,h.sale_status,h.city,h.address,h.tags_id,h.price,h.lat,h.lng,";
        $field .="h.unit,(case when s.min_type is null then 0 else s.min_type end) as min_type,";
        $field .="(case when s.max_type is null then 0 else s.max_type end) as max_type,";
        $field .="(case when s.min_acreage is null then 0 else s.min_acreage end) as min_acreage,";
        $field .="(case when s.max_acreage is null then 0 else s.max_acreage end) as max_acreage";
        $sort   = input('param.sort/d',0);
        $join = [
            ['house_search s','h.id = s.house_id','left']
        ];
        $obj   = model('house');
        $mod   = $obj->alias('h')
            ->where($where)
            ->field($field)
            ->join($join)
            ->order($this->getSort($sort))
            ->group('h.id');
        if($zoom < 13)
        {
            $lists = $mod->select();
        }else{
            $lists = $mod->limit(100)->select();
        }
        if($lists)
        {
            foreach($lists as &$v)
            {
                $v['url'] = url('House/detail',['id'=>$v['id']]);
                $v['w_url'] = "/pages/house/detail/index?id=".$v['id'];
                $v['sale_status_name'] = getLinkMenuName(1,$v['sale_status']);
                $temp = array_filter(explode(',',$v['tags_id']));
                $data = [];
                foreach($temp as $val)
                {
                    $data[] = getLinkMenuName(3,$val);
                }
                $v['tags']  = $data;
            }
            $return['code'] = 1;
            $return['data'] = $lists;
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
            try{
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
                        'price' => ceil($sum / $count).config('filter.house_price_unit'),
                        'city_name' => $city_name,
                        'total'     => $count.'个楼盘',
                        'lat'       => $lat,
                        'lng'       => $lng
                    ];
                }
            }catch(\Exception $e){
                \think\facade\Log::write($e->getMessage().$e->getFile().$e->getLine(),'error');
            }
        }
        return $temp;
    }

    /**
     * @return array
     * 得到城市下级区域 格式为 ['2'=>1,3=>1,4=>1],key为下级区域 值 为城市id
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
        $param['tags']       = input('param.tags',0);
        $param['type']       = input('param.type',0);//楼盘类型
        $param['status']     = input('param.status',0);//楼盘状态
        $param['sort']       = input('param.sort/d',0);//排序
        $bssw_lat            = input('get.bssw_lat');//地图可视区域左下角经度
        $bssw_lng            = input('get.bssw_lng');//地图可视区域左上角纬度
        $bsne_lat            = input('get.bsne_lat');//地图可视区域右下角经度
        $bsne_lng            = input('get.bsne_lng');//地图可视区域右上角纬度
        $param['area']       == 0 && $param['area'] = $city;
        $data['h.status']    = 1;
        $param['keyword']    = input('get.keyword');
        if($param['keyword']){
            $data[] = ['h.title','like','%'.$param['keyword'].'%'];
        }
        if(!empty($param['area'])){
            $city_ids = model('city')->get_child_ids($param['area'],true);
            $data[] = ['h.city','in',$city_ids];
        }
        if(!empty($param['price'])){
            $data[] = getHousePrice($param['price']);
        }
        if(!empty($param['tags'])){
            $data[] = ['','exp',\think\Db::raw("find_in_set({$param['tags']},h.tags_id)")];
        }
        if(!empty($param['type'])){
            $data[] = ['','exp',\think\Db::raw("find_in_set({$param['type']},h.type_id)")];
        }
        if(!empty($param['status'])){
            $data['h.sale_status'] = $param['status'];
        }
        if($bsne_lat && $bssw_lat && $bssw_lng && $bsne_lng)
        {
            $data[] = ['h.lat','between',[min($bssw_lat,$bsne_lat),max($bssw_lat,$bsne_lat)]];
            $data[] = ['h.lng','between',[min($bssw_lng,$bsne_lng),max($bssw_lng,$bsne_lng)]];
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
                $order = ['opening_time'=>'asc'];
                break;
            case 4:
                $order = ['opening_time'=>'desc'];
                break;
            default:
                $order = ['ordid'=>'asc','id'=>'desc'];
                break;
        }
        return $order;
    }
}
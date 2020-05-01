<?php


namespace app\home\controller;
use app\common\controller\HomeBase;
use app\common\service\Metro;
class Rental extends HomeBase
{
    /**
     * @return mixed
     * b出租房列表
     */
    public function index()
    {
        $result = $this->getLists();
        $lists  = $result['lists'];
        $this->assign('metro',Metro::index($this->cityInfo['id']));//地铁线
        $this->assign('area',$this->getAreaByCityId());
        $this->assign('house_type',getLinkMenuCache(9));//类型
        $this->assign('rental_type',getLinkMenuCache(10));//租赁方式
        $this->assign('renovation',getLinkMenuCache(8));//装修情况
        $this->assign('position',$this->getPositionHouse(6,4));//推荐房源
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('top_lists',$result['top']);
        return $this->fetch();
    }

    /**
     * @return mixed
     * 出租房详细
     */
    public function detail()
    {
        $id = input('param.id/d',0);
        if($id) {
            $where['h.id']     = $id;
            $where['h.status'] = 1;
            $obj  = model('rental');
            $join = [['rental_data d', 'h.id=d.house_id']];
            $info = $obj->alias('h')->join($join)->where($where)->find();
            if(!$info)
            {
                return $this->fetch('public/404');
            }
            $info['file'] = json_decode($info['file'], true);

            $estate = model('estate')->where('id',$info['estate_id'])->find();
            //统计出租房数量
            $info['total']        = $obj->where('estate_id',$info['estate_id'])->where('status',1)->count();
            //统计二手房数量
            $info['second_total'] = model('second_house')->where('estate_id',$info['estate_id'])->where('status',1)->count();
            updateHits($info['id'],'rental');
            $this->setSeo($info);
            $this->assign('info',$info);
            $this->assign('estate',$estate);
            $this->assign('near_by_house',$this->getNearByHouse($info['lat'],$info['lng'],$info['city']));
            $this->assign('same_price_house',$this->samePriceHouse($info->getData('price')));
        }else{
            return $this->fetch('public/404');
        }
            return $this->fetch();
    }

    /**
     * @return array
     * 获取出租房列表
     */
    private function getLists()
    {
        $time    = time();
        $where   = $this->search();
        $sort    = input('param.sort/d',0);
        $keyword = input('get.keyword');
        $field   = "r.id,r.title,r.estate_name,r.img,r.pano_url,r.room,r.living_room,r.toilet,r.price,r.rent_type,r.tags,r.address,r.acreage,r.orientations,r.renovation,r.user_type,r.contacts,r.update_time";
        $obj     = model('rental')->alias('r');
        if(isset($where['m.metro_id']) || isset($where['m.station_id']))
        {
            //查询地铁关联表
            $field .= ',m.metro_name,m.station_name,m.distance';
            $join  = [['metro_relation m','m.house_id = r.id']];
            $lists = $obj->join($join)->where($where)->where('m.model','rental')->where('r.top_time','lt',$time)->field($field)->group('r.id')->order($this->getSort($sort))->paginate(10,false,['query'=>['keyword'=>$keyword]]);
        }else{
            $lists   = $obj->where($where)->where('r.top_time','lt',$time)->field($field)->order($this->getSort($sort))->paginate(10,false,['query'=>['keyword'=>$keyword]]);
        }
        if($lists->currentPage() == 1)
        {
            $obj = $obj->removeOption()->alias('r');
            //关联地铁表
            if(isset($where['m.metro_id']) || isset($where['m.station_id']))
            {
                $field .= ',m.metro_name,m.station_name,m.distance';
                $join  = [['metro_relation m','m.house_id = r.id']];
                $obj->join($join)->where('m.model','rental')->group('r.id');
            }
            $top = $obj->where($where)->where('r.top_time','gt',$time)->field($field)->order(['top_time'=>'desc','id'=>'desc'])->select();
        }else{
            $top = false;
        }
        return ['lists'=>$lists,'top'=>$top];
    }
    /**
     * @param $price
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 价格相似房源
     */
    private function samePriceHouse($price,$num = 4)
    {
        $min_price = $price - 500;
        $max_price = $price + 500;
        $lists = model('rental')
            ->where('status',1)
            ->where('price','between',[$min_price,$max_price])
            ->where('city','in',$this->getCityChild())
            ->where('timeout','gt',time())
            ->field('id,title,img,room,living_room,acreage,price')
            ->order('create_time desc')
            ->limit($num)
            ->select();
        return $lists;
    }
    /**
     * @param $lat
     * @param $lng
     * @param int $city
     * @return array|\PDOStatement|string|\think\Collection
     * 附近房源
     */
    private function getNearByHouse($lat,$lng,$city = 0)
    {
        $obj = model('rental');
        if($lat && $lng){
            $point      = "*,ROUND(6378.138*2*ASIN(SQRT(POW(SIN(({$lat}*PI()/180-lat*PI()/180)/2),2)+COS({$lat}*PI()/180)*COS(lat*PI()/180)*POW(SIN(({$lng}*PI()/180-lng*PI()/180)/2),2)))*1000) as distance";
            $bindsql    = $obj->field($point)->buildSql();
            $fields_res = 'id,title,price,room,living_room,acreage,img,distance';
            $lists      = $obj->table($bindsql.' d')->field($fields_res)->where('status',1)->where('distance','<',2000)->where('timeout','gt',time())->limit(3)->select();
        }else{
            $where['status'] = 1;
            $city && $where['city'] = $city;
            $where[] = ['timeout','gt',time()];
            $lists = $obj->where($where)->field('id,title,price,room,living_room,acreage,img')->limit(3)->select();
        }
        return $lists;
    }
    /**
     * @param $pos_id @推荐位id
     * @param int $num @读取数量
     * @return array|\PDOStatement|string|\think\Collection
     * 获取推荐位楼盘
     */
    private function getPositionHouse($pos_id,$num = 6)
    {
        $service = controller('common/Position','service');
        $service->field   = 'h.id,h.img,h.title,h.price,h.estate_name,h.room,h.living_room,h.acreage,h.city';
        $service->city    = $this->getCityChild();
        $service->cate_id = $pos_id;
        $service->model   = 'rental';
        $service->num     = $num;
        $lists = $service->lists();

        return $lists;
    }
    /**
     * @return array
     * 搜索条件
     */
    private function search()
    {
        $estate_id     = input('param.estate_id/d',0);//小区id
        $param['area'] = input('param.area/d', $this->cityInfo['id']);
        $param['rading']     = 0;
        $param['price']      = input('param.price',0);
        $param['acreage']    = input('param.acreage',0);//面积
        $param['room']       = input('param.room',0);//户型
        $param['type']       = input('param.type',0);//物业类型
        $param['renovation'] = input('param.renovation',0);//装修情况
        $param['metro']      = input('param.metro/d',0);//地铁线
        $param['metro_station'] = input('param.metro_station/d',0);//地铁站点
        $param['sort']          = input('param.sort/d',0);//排序
        $param['rental_type']   = input('param.rental_type/d',0);//出租方式
        $param['user_type']     = input('param.user_type/d',0);//1个人房源  2中介房源
        $param['search_type']   = input('param.search_type/d',1);//查询方式 1按区域查询 2按地铁查询
        $param['area'] == 0 && $param['area'] = $this->cityInfo['id'];
        $data['r.status']    = 1;
        $keyword = input('get.keyword');
        $seo_title = '';
        if($estate_id)
        {
            $data['r.estate_id'] = $estate_id;
            $estate_name = model('estate')->where('id',$estate_id)->value('title');
            $seo_title .= '_'.$estate_name.'出租房';
        }
        if(!empty($param['type']))
        {
            $data['r.house_type'] = $param['type'];
            $seo_title .= '_'.getLinkMenuName(9,$param['type']);
        }
        if(!empty($param['user_type']))
        {
            $data['r.user_type'] = $param['user_type'];
        }
        if(!empty($param['rental_type']))
        {
            $data['r.rent_type'] = $param['rental_type'];
            $seo_title .= '_'.getLinkMenuName(10,$param['rental_type']);
        }
        if($param['renovation'])
        {
            $data['r.renovation'] = $param['renovation'];
            $seo_title .= '_'.getLinkMenuName(8,$param['renovation']);
        }
        if($keyword)
        {
            $param['keyword'] = $keyword;
            $data[] = ['r.title|r.estate_name','like','%'.$keyword.'%'];
            $seo_title .= '_'.$keyword;
        }
        if($param['search_type'] == 2)
        {
            if(!empty($param['metro']))
            {
                $data['m.metro_id'] = $param['metro'];
                $seo_title .= '_地铁'.Metro::getMetroName($param['metro']);
                $this->assign('metro_station',Metro::metroStation($param['metro']));
            }else{
                $data[] = ['r.city','in',$this->getCityChild()];
            }
            if(!empty($param['metro_station']))
            {
                $data['m.station_id'] = $param['metro_station'];
                $seo_title .= '_'.Metro::getStationName($param['metro_station']);
            }
        }else{
            if(!empty($param['area']))
            {
                $data[] = ['r.city','in',$this->getCityChild($param['area'])];
                $rading = $this->getRadingByAreaId($param['area']);
                //读取商圈
                $param['rading'] = 0;
                if($rading && array_key_exists($param['area'],$rading))
                {
                    $param['rading']  = $param['area'];
                    $param['area']    = $rading[$param['area']]['pid'];
                }
                $param['area']!=$this->cityInfo['id'] && $seo_title .= '_'.getCityName($param['area'],'').'出租房';
                $this->assign('rading',$rading);
            }
        }
        if(!empty($param['price']))
        {
            $data[] = getRentalPrice($param['price'],'r.price');
            $price  = config('filter.rental_price');
            isset($price[$param['price']]) && $seo_title .= '_'.$price[$param['price']]['name'];
        }
        if(!empty($param['room']))
        {
            $data[] = getRoom($param['room'],'r.room');
            $room   = config('filter.room');
            isset($room[$param['room']]) && $seo_title .= '_'.$room[$param['room']];
        }
        if(!empty($param['acreage']))
        {
            $data[] = getAcreage($param['acreage'],'r.acreage');
            $acreage = config('filter.acreage');
            isset($acreage[$param['acreage']]) && $seo_title .= '_'.$acreage[$param['acreage']]['name'];
        }

        $data[] = ['r.timeout','gt',time()];
        $search = $param;
        $seo_title  = trim($seo_title,'_');
        $seo_title && $this->setSeo(['seo_title'=>$seo_title,'seo_keys'=>str_replace('_',',',$seo_title)]);
        $data = array_filter($data);
        unset($param['rading']);
        $this->assign('search',$search);
        $this->assign('param',$param);
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
                $order = ['price'=>'asc','id'=>'desc'];
                break;
            case 2:
                $order = ['price'=>'desc','id'=>'desc'];
                break;
            case 3:
                $order = ['acreage'=>'asc','id'=>'desc'];
                break;
            case 4:
                $order = ['acreage'=>'desc','id'=>'desc'];
                break;
            case 5:
                $order = ['create_time'=>'desc','id'=>'desc'];
                break;
            default:
                $order = ['ordid'=>'asc','id'=>'desc'];
                break;
        }
        return $order;
    }
}
<?php


namespace app\home\controller;
use app\common\controller\HomeBase;
use app\common\service\Metro;
class OfficeRental extends HomeBase
{
    private $mod = 'office_rental';
    public function initialize()
    {
        $this->cur_url = 'OfficeRental';
        parent::initialize();
    }
    /**
     * @return mixed
     * 写字楼出租列表
     */
    public function index()
    {
        $result = $this->getLists();
        $lists  = $result['lists'];
        $this->assign('area',$this->getAreaByCityId());//区域
        $this->assign('type',getLinkMenuCache(15));//写字楼类型
        $this->assign('tags',getLinkMenuCache(16));//写字楼特色
        $this->assign('metro',Metro::index($this->cityInfo['id']));//获取城市地铁线
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('hot',$this->getHotOffice());
        $this->assign('top_lists',$result['top']);
        return $this->fetch();
    }
    public function detail()
    {
        $id = input('param.id/d',0);
        if($id)
        {
            $where['h.id']     = $id;
            $where['h.status'] = 1;
            $obj  = model($this->mod);
            $join = [[$this->mod.'_data d','h.id=d.house_id']];
            $info = $obj->alias('h')->join($join)->where($where)->find();
            if($info)
            {
                $info['file'] = json_decode($info['file'],true);
                $this->setSeo($info);
                updateHits($info['id'],$this->mod);
                $estate = model('estate')->where('id',$info['estate_id'])->find();
                $info['total']        = $obj->where('estate_id',$info['estate_id'])->where('status',1)->count();
                $this->assign('info',$info);
                $this->assign('estate',$estate);
                $this->assign('near_by_house',$this->getNearByHouse($info['lat'],$info['lng'],$info['city']));
                $this->assign('love',$this->samePriceHouse($info->getData('price')));
            }else{
                return $this->fetch('public/404');
            }
        }else{
            return $this->fetch('public/404');
        }
        return $this->fetch();
    }

    /**
     * @return array
     * 获取写字楼出租列表
     */
    private function getLists()
    {
        $time    = time();
        $where   = $this->search();
        $sort    = input('param.sort/d',0);
        $keyword = input('get.keyword');
        $field   = "o.id,o.title,o.estate_name,o.contact_name,o.user_type,o.update_time,o.img,o.price,o.average_price,o.tags,o.renovation,o.address,o.acreage,o.grade,o.floor,o.total_floor";
        $obj     = model($this->mod)->alias('o');
        if(isset($where['m.metro_id']) || isset($where['m.station_id']))
        {
            $field .= ',m.metro_name,m.station_name,m.distance';
            $join   = [['metro_relation m','m.house_id = o.id']];
            $lists   = $obj->join($join)->where($where)->where('m.model','office_rental')->where('o.top_time','lt',$time)->field($field)->group('o.id')->order($this->getSort($sort))->paginate(10,false,['query'=>['keyword'=>$keyword]]);
        }else{
            $lists   = $obj->where($where)->where('o.top_time','lt',$time)->field($field)->order($this->getSort($sort))->paginate(10,false,['query'=>['keyword'=>$keyword]]);
        }
        if($lists->currentPage() == 1)
        {
            $obj = $obj->removeOption()->alias('o');
            //查询地铁关联表
            if(isset($where['m.metro_id']) || isset($where['m.station_id']))
            {
                $field .= ',m.metro_name,m.station_name,m.distance';
                $join  = [['metro_relation m','m.house_id = o.id']];
                $obj->join($join)->where('m.model','office_rental')->group('o.id');
            }
            $top   = $obj->where($where)->where('o.top_time','gt',$time)->field($field)->order(['top_time'=>'desc','id'=>'desc'])->select();
        }else{
            $top   = false;
        }
        return ['lists'=>$lists,'top'=>$top];
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
        $obj = model($this->mod);
        if($lat && $lng){
            $point      = "*,ROUND(6378.138*2*ASIN(SQRT(POW(SIN(({$lat}*PI()/180-lat*PI()/180)/2),2)+COS({$lat}*PI()/180)*COS(lat*PI()/180)*POW(SIN(({$lng}*PI()/180-lng*PI()/180)/2),2)))*1000) as distance2";
            $bindsql    = $obj->field($point)->buildSql();
            $fields_res = 'id,title,estate_name,price,acreage,img,distance2';
            $lists      = $obj->table($bindsql.' d')->field($fields_res)->where('status',1)->where('distance2','<',2000)->where('timeout','gt',time())->limit(3)->select();
        }else{
            $where['status'] = 1;
            $city && $where['city'] = $city;
            $where[] = ['timeout','gt',time()];
            $lists = $obj->where($where)->field('id,title,estate_name,price,acreage,img')->limit(3)->select();
        }
        return $lists;
    }
    /**
     * @param $price
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 价格相似房源
     */
    private function samePriceHouse($price,$num = 4)
    {
        $min_price = $price - 100;
        $max_price = $price + 100;
        $where[] = ['status','eq',1];
        $where[] = ['price','between',[$min_price,$max_price]];
        $where[] = ['timeout','gt',time()];
        $city  = $this->getCityChild();
        $city && $where[] = ['city','in',$city];
        $lists = model($this->mod)
            ->where($where)
            ->field('id,title,img,acreage,price')
            ->order('create_time desc')
            ->limit($num)
            ->select();
        return $lists;
    }
    /**
     * @return array
     * 搜索条件
     */
    private function search()
    {
        $param['area']       = input('param.area/d', $this->cityInfo['id']);
        $param['rading']     = 0;
        $param['tags']       = input('param.tags/d',0);
        $param['price']      = input('param.price/d',0);
        $param['acreage']    = input('param.acreage/d',0);//面积
        $param['type']       = input('param.type/d',0);//类型
        $param['sort']       = input('param.sort/d',0);//排序
        $param['user_type']  = input('param.user_type/d',0);//1个人房源  2中介房源
        $param['metro']      = input('param.metro/d',0);//地铁线
        $param['metro_station'] = input('param.metro_station/d',0);//地铁站点
        $param['area'] == 0 && $param['area'] = $this->cityInfo['id'];
        $data['o.status']    = 1;
        $param['search_type']         = input('param.search_type/d',1);//查询方式 1按区域查询 2按地铁查询
        $keyword = input('get.keyword');
        $seo_title = '';
        if(!empty($param['type']))
        {
            $data['o.type'] = $param['type'];
            $seo_title .= '_'.getLinkMenuName(15,$param['type']);
        }
        if(!empty($param['user_type']))
        {
            $data['o.user_type'] = $param['user_type'];
        }
        if($param['search_type'] == 2)
        {
            if(!empty($param['metro']))
            {
                $data['m.metro_id'] = $param['metro'];
                $seo_title .= '_地铁'.Metro::getMetroName($param['metro']);
                $this->assign('metro_station',Metro::metroStation($param['metro']));
            }else{
                $data[] = ['o.city','in',$this->getCityChild()];
            }
            if(!empty($param['metro_station']))
            {
                $data['m.station_id'] = $param['metro_station'];
                $seo_title .= '_'.Metro::getStationName($param['metro_station']);
            }
        }else{
            if(!empty($param['area']))
            {
                $data[] = ['city','in',$this->getCityChild($param['area'])];
                $rading = $this->getRadingByAreaId($param['area']);
                //读取商圈
                $param['rading'] = 0;
                if($rading && array_key_exists($param['area'],$rading))
                {
                    $param['rading']  = $param['area'];
                    $param['area']    = $rading[$param['area']]['pid'];
                }
                $param['area']!=$this->cityInfo['id'] && $seo_title .= '_'.getCityName($param['area'],'').'写字楼出租';
                $this->assign('rading',$rading);
            }
        }
        if($keyword)
        {
            $param['keyword'] = $keyword;
            $data[] = ['o.title|o.estate_name','like','%'.$keyword.'%'];
            $seo_title .= $keyword;
        }
        if(!empty($param['price']))
        {
            $data[] = getBussinessCondition('office_rental_price','o.average_price',$param['price']);
            $price  = config('filter.office_rental_price');
            isset($price[$param['price']]) && $seo_title .= '_'.$price[$param['price']]['name'];
        }
        if(!empty($param['acreage']))
        {
            $data[] = getBussinessCondition('office_acreage','o.acreage',$param['acreage']);
            $acreage = config('filter.office_acreage');
            isset($acreage[$param['acreage']]) && $seo_title .= '_'.$acreage[$param['acreage']]['name'];
        }
        if(!empty($param['tags'])){
            $data[] = ['','exp',\think\Db::raw("find_in_set({$param['tags']},o.tags)")];
            $seo_title .= '_'.getLinkMenuName(16,$param['tags']);
        }
        $data[] = ['o.timeout','gt',time()];
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
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 热门房源
     */
    private function getHotOffice($num = 5)
    {
        $where['status'] = 1;
        $where[] = ['timeout','gt',time()];
        $city = $this->getCityChild();
        $city && $where[] = ['city','in',$city];
        $lists = model($this->mod)->field('id,title,img,price,estate_name,acreage')->where($where)->order('hits desc,id desc')->limit($num)->select();
        return $lists;
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
                $order = ['average_price'=>'asc','id'=>'desc'];
                break;
            case 4:
                $order = ['average_price'=>'desc','id'=>'desc'];
                break;
            case 5:
                $order = ['acreage'=>'asc','id'=>'desc'];
                break;
            case 6:
                $order = ['acreage'=>'desc','id'=>'desc'];
                break;
            default:
                $order = ['ordid'=>'asc','id'=>'desc'];
                break;
        }
        return $order;
    }
}
<?php


namespace app\mobile\controller;
use app\common\controller\MobileBase;
class Rental extends MobileBase
{
    private $pageSize = 10;
    public function index()
    {
        $result = $this->getLists();
        $lists  = $result['lists'];
        $this->assign('area',$this->getAreaByCityId());
        $this->assign('type',getLinkMenuCache(9));//类型
        $this->assign('rental_type',getLinkMenuCache(10));//租赁方式
        $this->assign('renovation',getLinkMenuCache(8));//装修情况
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('total_page',$lists->lastPage());
        $this->assign('top_lists',$result['top']);//置顶房源列表
        return $this->fetch();
    }

    /**
     * @return \think\response\Json
     * 异步获取房源列表
     */
    public function getRentalLists()
    {
        $page    = input('get.page/d',1);
        $data    = $this->getLists($page);
        $lists   = $data['lists'];
        if($lists)
        {
            foreach($lists as &$v)
            {
                $v['url']  = url('Rental/detail',['id'=>$v['id']]);
                $v['city'] = getCityName($v['city']);
                $v['img']  = thumb($v['img'],200,150);
                $v['renovation'] = getLinkMenuName(8,$v['renovation']);
                $v['acreage']    = $v['acreage'].config('filter.acreage_unit');
                $v['unit']       = config('filter.rental_price_unit');
                $v['update_time'] = getTime($v['update_time'],'mohu');
            }
        }
        $return['code'] = 1;
        $return['data'] = $lists;
        $return['total_page'] = $data['total_page'];
        return json($return);
    }
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
            updateHits($info['id'],'rental');
            $share_title = $info['estate_name'].$info['room'].'室'.$info['living_room'].'厅'.$info['acreage'].config('filter.acreage_unit').$info['price'].config('filter.rental_price_unit');
            $this->setSeo($info);
            $this->assign('info',$info);
            $this->assign('estate',$estate);
            $this->assign('near_by_house',$this->getNearByHouse($info['lat'],$info['lng'],$info['city']));
            $this->assign('same_price_house',$this->samePriceHouse($info->getData('price')));
            $this->assign('share_title',$share_title);
        }else{
            return $this->fetch('public/404');
        }
        return $this->fetch();
    }
    /**
     * @param int $page
     * @return array|\PDOStatement|string|\think\Collection|\think\Paginator
     * 获取房源列表
     */
    private function getLists($page = 0)
    {
        $time    = time();
        $where   = $this->search();
        $sort    = input('param.sort/d',0);
        $field = "id,title,estate_name,city,img,room,living_room,toilet,price,rent_type,tags,address,acreage,orientations,renovation,update_time";
        $obj   = model('rental');
        $obj = $obj->where($where)->field($field)->order($this->getSort($sort));
        if($page)
        {
            $lists = $obj->where('top_time','lt',$time)->page($page)->limit($this->pageSize)->select();
            $obj->removeOption();
            $count      = $obj->where($where)->where('top_time','lt',$time)->count();
            $total_page = ceil($count/$this->pageSize);
            $lists      = ['lists'=>$lists,'total_page'=>$total_page];
        }else{
            $result = $obj->where('top_time','lt',$time)->paginate($this->pageSize);
            $top    = $obj->removeOption()->where($where)->where('top_time','gt',$time)->order(['top_time'=>'desc','id'=>'desc'])->select();
            $lists  = ['lists'=>$result,'top'=>$top];
        }
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
        $param['price']      = input('param.price',0);
        $param['acreage']    = input('param.acreage',0);//面积
        $param['room']       = input('param.room',0);//户型
        $param['type']       = input('param.type',0);//物业类型
        $param['renovation'] = input('param.renovation',0);//装修情况
        $param['sort']       = input('param.sort/d',0);//排序
        $param['rental_type']   = input('param.rental_type/d',0);//出租方式
        $param['area'] == 0 && $param['area'] = $this->cityInfo['id'];
        $data['status']         = 1;
        $keyword = input('get.keyword');

        if($estate_id)
        {
            $data['estate_id'] = $estate_id;
        }
        if(!empty($param['type']))
        {
            $data['house_type'] = $param['type'];
        }
        if($keyword)
        {
            $param['keyword'] = $keyword;
            $data[] = ['title|estate_name','like','%'.$keyword.'%'];
        }
        if(!empty($param['rental_type']))
        {
            $data['rent_type'] = $param['rental_type'];
        }
        if($param['renovation'])
        {
            $data['renovation'] = $param['renovation'];
        }
        if(!empty($param['area']))
        {
            $data[] = ['city','in',$this->getCityChild($param['area'])];
        }
        if(!empty($param['price']))
        {
            $data[] = getRentalPrice($param['price']);
        }
        if(!empty($param['room']))
        {
            $data[] = getRoom($param['room']);
        }
        if(!empty($param['acreage']))
        {
            $data[] = getAcreage($param['acreage']);
        }
        $data[] = ['timeout','gt',time()];
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
    /**
     * @param $price
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 价格相似房源
     */
    private function samePriceHouse($price,$num = 4)
    {
        $min_price = $price>500?$price - 500:$price;
        $max_price = $price + 500;
        $lists = model('rental')
            ->where('status',1)
            ->where('price','between',[$min_price,$max_price])
            ->where('city','in',$this->getCityChild())
            ->where('timeout','gt',time())
            ->field('id,title,img,estate_name,orientations,city,tags,room,living_room,acreage,price')
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
            $fields_res = 'id,title,price,room,estate_name,orientations,city,tags,living_room,acreage,img,distance';
            $lists      = $obj->table($bindsql.' d')->field($fields_res)->where('status',1)->where('timeout','gt',time())->where('distance','<',2000)->limit(4)->select();
        }else{
            $where['status'] = 1;
            $city && $where['city'] = $city;
            $where[] = ['timeout','gt',time()];
            $lists = $obj->where($where)->field('id,title,estate_name,orientations,city,tags,price,room,living_room,acreage,img')->limit(4)->select();
        }
        return $lists;
    }
}
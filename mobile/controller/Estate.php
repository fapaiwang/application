<?php


namespace app\mobile\controller;
use app\common\controller\MobileBase;
class Estate extends MobileBase
{
    private $pageSize = 10;
    public function index()
    {
        $lists = $this->getLists();
        $this->assign('area',$this->getAreaByCityId());
        $this->assign('house_type',getLinkMenuCache(9));//类型
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('total_page',$lists->lastPage());
        return $this->fetch();
    }
    /**
     * @return mixed
     * 小区详细
     */
    public function detail()
    {
        $id = input('param.id/d',0);
        if($id)
        {
            $where['id'] = $id;
            $where['status'] = 1;
            $info = model('estate')->where($where)->find();
            if(!$info)
            {
                return $this->fetch('public/404');
            }
            $info['second_total']        = model('second_house')->where('estate_id',$info['id'])->where('status',1)->count();
            $info['rental_total'] = model('rental')->where('estate_id',$info['id'])->where('status',1)->count();

            $this->setSeo($info);
            updateHits($info['id'],'estate');
            $this->assign('record',$this->getComplateRecord($info['id']));
            $this->assign('info',$info);
            $this->assign('near_by_estate',$this->getNearByHouse($info['lat'],$info['lng'],$info['city']));
        }else{
            return $this->fetch('public/404');
        }
        return $this->fetch();
    }
    /**
     * @return \think\response\Json
     * 异步获取小区列表
     */
    public function getEstateLists()
    {
        $page   = input('get.page/d',1);
        $data   = $this->getLists($page);
        $lists  = $data['lists'];
        if($lists)
        {
            foreach($lists as &$v)
            {
                $v['url'] = url('Estate/detail',['id'=>$v['id']]);
                $v['city'] = getCityName($v['city'],'-');
                $v['img']  = thumb($v['img'],200,150);
                $v['unit'] = config('filter.second_price_unit');
            }
        }
        $return['code'] = 1;
        $return['data'] = $lists;
        $return['total_page'] = $data['total_page'];
        return json($return);
    }

    /**
     * @param int $page
     * @return array|\PDOStatement|string|\think\Collection|\think\Paginator
     * 根据条件获取小区列表
     */
    private function getLists($page = 0)
    {
        $where   = $this->search();
        $sort    = input('param.sort/d',0);
        $field   = 'id,city,title,img,house_type,years,address,price,complate_num';
        //统计二手房和出租房数量 where条件里需要替换estate_id为estate表每条记录的id, 不能用字符(会被转成 0)所以用9999代替替换
        $second_sql = model('second_house')->where('estate_id','9999')->where('status',1)->field('count(id) as second_total')->buildSql();
        $rental_sql = model('rental')->where('estate_id','9999')->where('status',1)->field('count(id) as second_total')->buildSql();
        $field .= ','.$second_sql.' as second_total,'.$rental_sql.' as rental_total';
        $field  = str_replace('9999','e.id',$field);
        $obj    = model('estate');
        $obj    = $obj->alias('e')
            ->where($where)
            ->field($field)
            ->order($this->getSort($sort));
        if($page)
        {
            $lists = $obj->page($page)->limit($this->pageSize)->select();
            $obj->removeOption();
            $count      = $obj->alias('e')->where($where)->count();
            $total_page = ceil($count/$this->pageSize);
            $lists      = ['lists'=>$lists,'total_page'=>$total_page];
        }else{
            $lists = $obj->paginate($this->pageSize);
        }
        return $lists;
    }
    /**
     * @return array
     * 搜索条件
     */
    private function search()
    {
        $param['area']       = input('param.area/d', $this->cityInfo['id']);
        $param['price']      = input('param.price',0);
        $param['years']      = input('param.years',0);//房龄
        $param['type']       = input('param.type',0);//物业类型
        $param['sort']       = input('param.sort/d',0);//排序
        $param['area'] == 0 && $param['area'] = $this->cityInfo['id'];
        $data['status']      = 1;
        $keyword = input('get.keyword');
        if($keyword)
        {
            $param['keyword'] = $keyword;
            $data[] = ['title','like','%'.$keyword.'%'];
        }

        if(!empty($param['area']))
        {
            $data[] = ['city','in',$this->getCityChild($param['area'])];
        }
        if(!empty($param['price']))
        {
            $data[] = getEstatePrice($param['price']);
        }
        if(!empty($param['years']))
        {
            $data[] = getYears($param['years']);
        }

        if(!empty($param['type']))
        {
            $data['house_type'] = $param['type'];
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
                $order = ['price'=>'asc','id'=>'desc'];
                break;
            case 2:
                $order = ['price'=>'desc','id'=>'desc'];
                break;
            case 3:
                $order = ['hits'=>'desc','id'=>'desc'];
                break;
            case 4:
                $order = ['hits'=>'asc','id'=>'desc'];
                break;
            default:
                $order = ['ordid'=>'asc','id'=>'desc'];
                break;
        }
        return $order;
    }
    private function getComplateRecord($id)
    {
        $where['estate_id'] = $id;
        $lists = model('transaction_record')->where($where)->order('complate_time','desc')->limit(10)->select();
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
        $obj = model('estate');
        if($lat && $lng){
            $point      = "*,ROUND(6378.138*2*ASIN(SQRT(POW(SIN(({$lat}*PI()/180-lat*PI()/180)/2),2)+COS({$lat}*PI()/180)*COS(lat*PI()/180)*POW(SIN(({$lng}*PI()/180-lng*PI()/180)/2),2)))*1000) as distance";
            $bindsql    = $obj->field($point)->buildSql();
            $fields_res = 'id,title,price,city,years,img,distance';
            $lists      = $obj->table($bindsql.' d')->field($fields_res)->where('status',1)->where('distance','<',2000)->limit(4)->select();
        }else{
            $where['status'] = 1;
            $city && $where['city'] = $city;
            $lists = $obj->where($where)->field('id,title,city,years,price,img')->limit(4)->select();
        }
        return $lists;
    }
}
<?php

namespace app\home\controller;
use app\common\controller\HomeBase;
use app\server;
use think\Log;

class Estate extends HomeBase
{
    public function initialize()
    {
        $this->cur_url = 'Second';
        parent::initialize();
    }

    /**
     * @return mixed
     * 小区列表
     */
    public function index()
    {
        $where   = $this->search();
        $sort    = input('param.sort/d',0);
        $keyword = input('get.keyword');
        $field   = 'id,title,img,pano_url,house_type,years,address,price,complate_num';
        //统计二手房和出租房数量 where条件里需要替换estate_id为estate表每条记录的id, 不能用字符(会被转成 0)所以用9999代替替换
        $second_sql = model('second_house')->where('estate_id','9999')->where('status',1)->field('count(id) as second_total')->buildSql();
        $rental_sql = model('rental')->where('estate_id','9999')->where('status',1)->field('count(id) as second_total')->buildSql();
        $field .= ','.$second_sql.' as second_total,'.$rental_sql.' as rental_total';
        $field  = str_replace('9999','e.id',$field);
        $lists      = model('estate')
                      ->alias('e')
                      ->where($where)
                      ->field($field)
                      ->order($this->getSort($sort))
                      ->paginate(30,false,['query'=>['keyword'=>$keyword]]);
                      // print_r($sort);
        //问答
        $answer = model('article')->field('id,title,hits,create_time')->where('cate_id',10)->cache('answer',3600)->order('hits desc')->limit(5)->select();
        $hot_news = model('article')->field('id,title')->where('cate_id','neq',10)->cache('hot_news',3600)->order('hits desc')->limit(5)->select();
        
        $this->assign('area',$this->getAreaByCityId());
        $this->assign('house_type',getLinkMenuCache(9));//类型
        $this->assign('position',$this->getPositionHouse(4));
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('page_t',4);
        $this->assign('answer',$answer);
        $this->assign('hot_news',$hot_news);
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
            if(!$info){
                return $this->fetch('public/404');
            }
            $seo['title'] = $info['title'].'热门小区,北京法拍二手房源-金铂顺昌房拍网';
            $seo['keys']  = $info['title'].',热门小区,北京法拍二手房源,金铂顺昌房拍网';
            $seo['desc']  = '金铂顺昌房拍网为您提供北京【'.$info['title'].'】法拍房源信息详情：'.$info['title'].'房源、周边配套及附近小区详情等法拍二手房源信息，让您安心购房';
            $this->assign('seo',$seo);
            updateHits($info['id'],'estate');
            $this->assign('record',$this->getComplateRecord($info['id']));
            $this->assign('position',$this->getPositionSecondHouse(5,4));
            $this->assign('info',$info);
            $this->assign('near_by_estate',$this->getNearByHouse($info['lat'],$info['lng'],$info['city']));
        }
		else{
            return $this->fetch('public/404');
        }
        return $this->fetch();
    }
    private function getComplateRecord($id)
    {
        $where['estate_id'] = $id;
        $lists = model('transaction_record')->where($where)->order('complate_time','desc')->limit(10)->select();
        return $lists;
    }
    /**
     * @param $pos_id @推荐位id
     * @param int $num @读取数量
     * @return array|\PDOStatement|string|\think\Collection
     * 获取推荐位楼盘
     */
    private function getPositionSecondHouse($pos_id,$num = 6)
    {
        $service = controller('common/Position','service');
        $service->field   = 'h.id,h.img,h.title,h.price,h.estate_name,h.room,h.living_room,h.acreage,h.city';
        $service->city    = $this->getCityChild();
        $service->cate_id = $pos_id;
        $service->model   = 'second_house';
        $service->num     = $num;
        $lists = $service->lists();

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
        $service->field = 'h.id,h.img,h.title,h.price,h.unit,h.city';
        $service->city  = $this->getCityChild();
        $service->cate_id = $pos_id;
        $service->num     = $num;
        $lists = $service->lists();
        if($lists)
        {
            foreach($lists as &$v)
            {
                $v['unit'] = getUnitData($v['unit']);
            }
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
        $param['rading']     = 0;
        $param['price']      = input('param.price',0);
        $param['years']      = input('param.years',0);//房龄
        $param['type']       = input('param.type',0);//物业类型
        $param['sort']       = input('param.sort/d',0);//排序
        $data['status']      = 1;
        $param['area'] == 0 && $param['area'] = $this->cityInfo['id'];
        $keyword = input('get.keyword');
        if($keyword)
        {
            $param['keyword'] = $keyword;
            $data[] = ['title','like','%'.$keyword.'%'];
        }

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
            $this->assign('rading',$rading);
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

        $search = $param;
        unset($param['rading']);
        $this->assign('search',$search);
        $this->assign('param',$param);
        return $data;
    }
    /**
     * @param $lat
     * @param $lng
     * @param int $city
     * @return array|\PDOStatement|string|\think\Collection
     * 附近小区
     */
    private function getNearByHouse($lat,$lng,$city = 0)
    {
        $obj = model('estate');
        if($lat && $lng){
            $point      = "*,ROUND(6378.138*2*ASIN(SQRT(POW(SIN(({$lat}*PI()/180-lat*PI()/180)/2),2)+COS({$lat}*PI()/180)*COS(lat*PI()/180)*POW(SIN(({$lng}*PI()/180-lng*PI()/180)/2),2)))*1000) as distance";
            $bindsql    = $obj->field($point)->buildSql();
            $fields_res = 'id,title,price,img,distance';
            $lists      = $obj->table($bindsql.' d')->field($fields_res)->where('status',1)->where('distance','<',2000)->limit(4)->select();
        }else{
            $where['status'] = 1;
            $city && $where['city'] = $city;
            $lists = $obj->where($where)->field('id,title,price,img')->limit(4)->select();
        }
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
                $order = ['second_total'=>'desc','ordid'=>'asc','id'=>'desc'];
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
    
    /**
     * @description 获取房源信息（模态框接口）
     * @param $id
     * @return \think\response\Json
     * @auther xiaobin
     */
    public function houseDetail($id)
    {
        $house = new server();
        $houseRes = $house->second_model($id);
        $houseRes['toilet'] = getLinkMenuName(29,$houseRes['toilet']);
        $houseRes['acreage'] = $houseRes['acreage'].config('filter.acreage_unit');
        $houseRes['estate'] = $house->estate($houseRes['estate_id']);
        $houseRes['orientations'] = getLinkMenuName(4,$houseRes['orientations']);
        $houseRes['user'] = $house->user_info($id,$houseRes['broker_id']);
        $houseRes['user'] =  $houseRes['user'] && $houseRes['user']['history_complate'] ? $houseRes['user']['history_complate'] : 0;
        $houseRes['user_info'] = $house->user($id,$houseRes['broker_id']);
        $houseRes['user_info'] = $houseRes['user_info'] && $houseRes['user_info']['lxtel_zhuan'] ? $houseRes['user_info']['lxtel_zhuan'] : '';
        $houseRes['pinglun'] = model('user')->where('id',$houseRes['broker_id'])->find();//客服
        $user = login_user();
        $houseRes['is_guanzhu'] = $house->is_guanzhu($houseRes['id'],$user['id']);
        $houseRes['user_id'] = $user['id'];
        return json([
            "code" => 200,
            "data" => $houseRes
        ]);
    }
}
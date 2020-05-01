<?php

namespace app\api\controller;


use app\common\controller\ApiBase;

class Rental extends ApiBase
{
    private $pageSize = 10;
    public function index()
    {
        $page    = input('get.page/d',1);
        $return['code'] = 0;
        $where   = $this->search();
        $sort    = input('param.sort/d',0);
        $field = "id,title,estate_name,city,img,room,living_room,price,rent_type,tags,address,acreage,renovation";
        $obj   = model('rental');
        $lists = $obj->where($where)->field($field)->order($this->getSort($sort))->page($page)->limit($this->pageSize)->select();
        $count = $obj->removeOption()->where($where)->count();
        $total_page = ceil($count/$this->pageSize);
        if(!$lists->isEmpty())
        {
            $lists = $lists->toArray();
            foreach($lists as &$v)
            {
                $v['price']   = str_replace(['<i>','</i>'],['',''],$v['price']);
                $v['city'] = getCityName($v['city']);
                $v['renovation'] = getLinkMenuName(8,$v['renovation']);
                $v['acreage']    = $v['acreage'].config('filter.acreage_unit');
                $v['img']        = $this->getImgUrl(thumb($v['img'],120,80));
            }
            $return['code'] = 200;
        }
        $return['page']       = $page;
        $return['total_page'] = $total_page;
        $return['data']       = $lists;
        return json($return);
    }

    /**
     * @return \think\response\Json
     * 筛选条件
     */
    public function rentalAttr()
    {
        $city               = getCity();
        $data['city']       = isset($city[$this->city]['_child']) ? $city[$this->city]['_child'] : $city;
        $data['price']      = getRentalPrice();
        $data['type']       = getLinkMenuCache(9);
        $data['renovation'] = getLinkMenuCache(8);
        $data['rent_type']  = getLinkMenuCache(10);
        $data['acreage']    = getAcreage();
        $data['sort']       = [
            0 => ['id'=>0,'name'=>'默认'],
            1 => ['id'=>1,'name'=>'租金从低到高'],
            2 => ['id'=>2,'name'=>'租金从高到低'],
            3 => ['id'=>3,'name'=>'面积从低到高'],
            4 => ['id'=>4,'name'=>'面积从高到低']
        ];
        $room = getRoom();
        $room_arr = [];
        foreach($room as $key=>$v)
        {
            $room_arr[$key] = [
                'id' => $key,
                'name' => $v
            ];
        }
        $data['room']   = $room_arr;
        $return['code'] = 1;
        $return['data'] = $data;
        return json($return);
    }
    public function read($id)
    {
        $return['code'] = 0;
        if($id)
        {
            $where['h.id']     = $id;
            $where['h.status'] = 1;
            $obj  = model('rental');
            $join = [['rental_data d', 'h.id=d.house_id']];
            $field = "h.id,h.title,h.estate_name,h.img,h.room,h.living_room,h.address,h.contacts,d.file,h.price,h.user_type,h.pano_url,h.renovation,h.floor,h.total_floor,h.orientations,FROM_UNIXTIME(h.create_time,'%Y-%m-%d') as create_time,h.acreage,d.supporting,d.info";
            $info = $obj->alias('h')->join($join)->field($field)->where($where)->find();
            if($info)
            {
                $info = objToArray($info);
                $file = $info['file']?json_decode($info['file'], true):[];
                $info['file']       = $this->turnFileUrl($file);
                $info['img']        = $this->getImgUrl($info['img']);
                $info['renovation'] = getLinkMenuName(8,$info['renovation']);
                $info['floor']      = getLinkMenuName(7,$info['floor']);
                $info['orientations'] = getLinkMenuName(4,$info['orientations']);
                $info['price']        = str_replace(['<i>','</i>'],['',''],$info['price']);
                $info['acreage']      = $info['acreage'].config('filter.acreage_unit');
                $info['pano_url']    = base64_encode($info['pano_url']);
                $supporting           = explode(',',$info['supporting']);
                $info['supporting']   = $this->getTags(12,$supporting,30);
                $info['relation']     = $this->samePriceHouse($info['price']);
                $info['info']         = $this->filterContent($info['info']);
                updateHits($info['id'],'rental');
                $return['code'] = 200;
            }
            $return['data'] = $info;
        }else{
            $return['msg']  = '参数错误';
        }
        return json($return);
    }
    private function search()
    {
        $estate_id     = input('get.estate_id/d',0);//小区id
        $param['city'] = input('get.city/d', $this->city);
        $param['price']      = input('get.price',0);
        $param['acreage']    = input('get.acreage',0);//面积
        $param['room']       = input('get.room',0);//户型
        $param['type']       = input('get.type',0);//物业类型
        $param['renovation'] = input('get.renovation',0);//装修情况
        $param['sort']       = input('get.sort/d',0);//排序
        $param['rental_type']   = input('get.rent_type/d',0);//出租方式
        $data['status']         = 1;
        $keyword = input('get.keyword');
        if($keyword)
        {
            $param['keyword'] = $keyword;
            $data[] = ['title|estate_name','like','%'.$keyword.'%'];
        }
        if($estate_id)
        {
            $data['estate_id'] = $estate_id;
        }
        if(!empty($param['city']))
        {
            $data[] = ['city','in',$this->getCityChild($param['city'])];
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
        if(!empty($param['type']))
        {
            $data['house_type'] = $param['type'];
        }
        if(!empty($param['rental_type']))
        {
            $data['rent_type'] = $param['rental_type'];
        }
        if($param['renovation'])
        {
            $data['renovation'] = $param['renovation'];
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
        $where[] = ['status','eq',1];
        $where[] = ['price','between',[$min_price,$max_price]];
        $this->city && $where[] = ['city','in',$this->getCityChild()];
        $lists = model('rental')
            ->where($where)
            ->field('id,title,img,estate_name,room,address,living_room,acreage,price')
            ->order('create_time desc')
            ->limit($num)
            ->select();
        if(!$lists->isEmpty())
        {
            $lists = $lists->toArray();
            foreach($lists as &$v)
            {
                $v['price']   = str_replace(['<i>','</i>'],['',''],$v['price']);
                $v['img'] = $this->getImgUrl($v['img']);
                $v['acreage'] = $v['acreage'].config('acreage_unit');
            }
        }
        return $lists;
    }
}
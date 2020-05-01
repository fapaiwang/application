<?php

namespace app\api\controller;


use app\common\controller\ApiBase;
class Estate extends ApiBase
{
    private $pageSize = 10;

    /**
     * @return \think\response\Json
     * 小区列表
     */
    public function index()
    {
        $page    = input('get.page/d',1);
        $where   = $this->search();
        $field   = 'id,city,title,img,house_type,years,address,price,complate_num';
        //统计二手房和出租房数量 where条件里需要替换estate_id为estate表每条记录的id, 不能用字符(会被转成 0)所以用9999代替替换
        $second_sql = model('second_house')->where('estate_id','9999')->where('status',1)->field('count(id) as second_total')->buildSql();
        $rental_sql = model('rental')->where('estate_id','9999')->where('status',1)->field('count(id) as second_total')->buildSql();
        $field .= ','.$second_sql.' as second_total,'.$rental_sql.' as rental_total';
        $field  = str_replace('9999','e.id',$field);
        $obj    = model('estate');
        $lists  = $obj->alias('e')
            ->where($where)
            ->field($field)
            ->order(['ordid'=>'asc','id'=>'desc'])->page($page)->limit($this->pageSize)->select();
        $count      = $obj->removeOption()->alias('e')->where($where)->count();
        $total_page = ceil($count/$this->pageSize);
        if(!$lists->isEmpty())
        {
            foreach($lists as &$v)
            {
                $v['city']   = getCityName($v['city']);
                $v['price']  = $v['price'].config('filter.second_price_unit');
                $v['img']    = $this->getImgUrl(thumb($v['img'],120,80));
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
    public function estateAttr()
    {
        $city               = getCity();
        $data['city']       = isset($city[$this->city]['_child']) ? $city[$this->city]['_child'] : $city;
        $data['price']      = getEstatePrice();
        $data['type']       = getLinkMenuCache(9);
        $data['years']      = getYears();
        $return['code'] = 1;
        $return['data'] = $data;
        return json($return);
    }

    /**
     * @param $id
     * @return \think\response\Json
     * 小区详情
     */
    public function read($id)
    {
        $return['code'] = 0;
        if($id) {
            $where['id'] = $id;
            $where['status'] = 1;
            $info = model('estate')->where($where)->find();
            if ($info) {
                $info['second'] = $this->getSecondByEstateId($info['id']);
                $info['rental'] = $this->getRentByEstateId($info['id']);
                $info['info']   = $this->filterContent($info['info']);
                $info['price']  = $info['price'].config('filter.house_price_unit');
                $info['tags']   = array_filter(explode(',',$info['tags']));
                $info['file']       = $this->turnFileUrl($info['file']);
                $info['house_type'] = getLinkMenuName(9,$info['house_type']);
                $info['img']        = $this->getImgUrl($info['img']);
                updateHits($info['id'], 'estate');
                $return['code'] = 200;
            }
            $return['data'] = $info;
        }else{
            $return['msg'] = '参数错误';
        }
        return json($return);
    }
    private function search()
    {
        $param['city']       = input('get.city/d', $this->city);
        $param['price']      = input('get.price',0);
        $param['years']      = input('get.years',0);//房龄
        $param['type']       = input('get.type',0);//物业类型
        $param['sort']       = input('get.sort/d',0);//排序
        $data['status']      = 1;
        $keyword = input('get.keyword');
        if($keyword)
        {
            $param['keyword'] = $keyword;
            $data[] = ['title','like','%'.$keyword.'%'];
        }

        if(!empty($param['city']))
        {
            $data[] = ['city','in',$this->getCityChild($param['city'])];
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
     * @param $id
     * @return array
     * 指定小区下的二手房
     */
    private function getSecondByEstateId($id)
    {
        $where['estate_id'] = $id;
        $where['status']    = 1;
        $field = 'id,title,city,img,price,room,living_room,acreage,address';
        $obj   = model('second_house');
        $lists = $obj->where($where)->field($field)->order('ordid asc,id desc')->limit(5)->select();
        $count = $obj->removeOption()->where($where)->count();
        if(!$lists->isEmpty())
        {
            $lists = $lists->toArray();
            foreach($lists as &$v)
            {
                $v['price']   = str_replace(['<i>','</i>'],['',''],$v['price']);
                $v['acreage'] = $v['acreage'].config('filter.acreage_unit');
                $v['img']     = $this->getImgUrl($v['img']);
                $v['city']    = getCityName($v['city'],'-');
            }
        }
        return ['data'=>$lists,'total'=>$count];
    }

    /**
     * @param $id
     * @return array
     * 指定小区下的出租房
     */
    private function getRentByEstateId($id)
    {
        $where['estate_id'] = $id;
        $where['status']    = 1;
        $field = 'id,title,city,img,price,address,room,living_room,acreage';
        $obj   = model('rental');
        $lists = $obj->where($where)->field($field)->order('ordid asc,id desc')->limit(5)->select();
        $count = $obj->removeOption()->where($where)->count();
        if(!$lists->isEmpty())
        {
            $lists = $lists->toArray();
            foreach($lists as &$v)
            {
                $v['price']   = str_replace(['<i>','</i>'],['',''],$v['price']);
                $v['acreage'] = $v['acreage'].config('filter.acreage_unit');
                $v['img']     = $this->getImgUrl($v['img']);
                $v['city']    = getCityName($v['city'],'-');
            }
        }
        return ['data'=>$lists,'total'=>$count];
    }
}
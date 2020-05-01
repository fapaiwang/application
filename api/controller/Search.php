<?php

namespace app\api\controller;


use app\common\controller\ApiBase;

class Search extends ApiBase
{
    /**
     * 新房搜索
     */
    public function index()
    {
        $keyword = input('get.keyword');
        $city    = input('get.city/d',0);
        $return['code']  = 0;
        $where['status'] = 1;
        if($keyword)
        {
            $where[] = ['title','like','%'.$keyword.'%'];
        }
        if($city)
        {
            $city_arr = $this->getCityChild($city);
            $where[] = ['city','in',$city_arr];
        }
        $field = 'id,title,price,unit,address';
        $lists = model('house')->where($where)->field($field)->order('id desc')->limit(10)->select();
        if(!$lists->isEmpty())
        {
            foreach($lists as &$v)
            {
                $v['price'] = $v->getData('price') > 0 ? $v['price'].$v['unit'] : $v['price'];
            }
            $return['code'] = 200;
        }
        $return['data'] = $lists;
        return json($return);
    }

    /**
     * @return \think\response\Json
     * 二手房搜索
     */
    public function second()
    {
        $keyword = input('get.keyword');
        $city    = input('get.city/d',0);
        $return['code']  = 0;
        $where['status'] = 1;
        if($keyword)
        {
            $where[] = ['title|estate_name','like','%'.$keyword.'%'];
        }
        if($city)
        {
            $city_arr = $this->getCityChild($city);
            $where[] = ['city','in',$city_arr];
        }
        $field = 'id,title,price,address';
        $lists = model('second_house')->where($where)->field($field)->order('id desc')->limit(10)->select();
        if(!$lists->isEmpty())
        {
            $lists = $lists->toArray();
            foreach($lists as &$v)
            {
                $v['price'] = str_replace(['<i>','</i>'],'',$v['price']);
            }
            $return['code'] = 200;
        }
        $return['data'] = $lists;
        return json($return);
    }
    /**
     * @return \think\response\Json
     * 出租房搜索
     */
    public function rental()
    {
        $keyword = input('get.keyword');
        $city    = input('get.city/d',0);
        $return['code']  = 0;
        $where['status'] = 1;
        if($keyword)
        {
            $where[] = ['title|estate_name','like','%'.$keyword.'%'];
        }
        if($city)
        {
            $city_arr = $this->getCityChild($city);
            $where[] = ['city','in',$city_arr];
        }
        $field = 'id,title,price,address';
        $lists = model('rental')->where($where)->field($field)->order('id desc')->limit(10)->select();
        if(!$lists->isEmpty())
        {
            $lists = $lists->toArray();
            foreach($lists as &$val)
            {
                $val['price'] = str_replace(['<i>','</i>'],'',$val['price']);
            }
            $return['code'] = 200;
        }
        $return['data'] = $lists;
        return json($return);
    }
    /**
     * @return \think\response\Json
     * 小区搜索
     */
    public function estate()
    {
        $keyword = input('get.keyword');
        $city    = input('get.city/d',0);
        $return['code']  = 0;
        $where['status'] = 1;
        if($keyword)
        {
            $where[] = ['title','like','%'.$keyword.'%'];
        }
        if($city)
        {
            $city_arr = $this->getCityChild($city);
            $where[] = ['city','in',$city_arr];
        }
        $field = 'id,title,price,address,lat,lng';
        $lists = model('estate')->where($where)->field($field)->order('id desc')->limit(10)->select();
        if(!$lists->isEmpty())
        {
            $lists = $lists->toArray();
            foreach($lists as &$v)
            {
                $v['price'] = $v['price'] > 0 ? $v['price'].config('filter.house_price_unit') : '未知';
            }
            $return['code'] = 200;
        }
        $return['data'] = $lists;
        return json($return);
    }
    /**
     * @return \think\response\Json
     * 写字楼出售搜索
     */
    public function office()
    {
        $keyword = input('get.keyword');
        $city    = input('get.city/d',0);
        $return['code']  = 0;
        $where['status'] = 1;
        if($keyword)
        {
            $where[] = ['title|estate_name','like','%'.$keyword.'%'];
        }
        if($city)
        {
            $city_arr = $this->getCityChild($city);
            $where[] = ['city','in',$city_arr];
        }
        $field = 'id,title,price,address';
        $lists = model('office')->where($where)->field($field)->order('id desc')->limit(10)->select();
        if(!$lists->isEmpty())
        {
            $lists = $lists->toArray();
            foreach($lists as &$val)
            {
                $val['price'] = str_replace(['<i>','</i>'],'',$val['price']);
            }
            $return['code'] = 200;
        }
        $return['data'] = $lists;
        return json($return);
    }
    /**
     * @return \think\response\Json
     * 写字楼出租搜索
     */
    public function officeRental()
    {
        $keyword = input('get.keyword');
        $city    = input('get.city/d',0);
        $return['code']  = 0;
        $where['status'] = 1;
        if($keyword)
        {
            $where[] = ['title|estate_name','like','%'.$keyword.'%'];
        }
        if($city)
        {
            $city_arr = $this->getCityChild($city);
            $where[] = ['city','in',$city_arr];
        }
        $field = 'id,title,price,address';
        $lists = model('office_rental')->where($where)->field($field)->order('id desc')->limit(10)->select();
        if(!$lists->isEmpty())
        {
            $lists = $lists->toArray();
            foreach($lists as &$val)
            {
                $val['price'] = str_replace(['<i>','</i>'],'',$val['price']);
            }
            $return['code'] = 200;
        }
        $return['data'] = $lists;
        return json($return);
    }
    /**
     * @return \think\response\Json
     * 商铺出售搜索
     */
    public function shops()
    {
        $keyword = input('get.keyword');
        $city    = input('get.city/d',0);
        $return['code']  = 0;
        $where['status'] = 1;
        if($keyword)
        {
            $where[] = ['title|estate_name','like','%'.$keyword.'%'];
        }
        if($city)
        {
            $city_arr = $this->getCityChild($city);
            $where[] = ['city','in',$city_arr];
        }
        $field = 'id,title,price,address';
        $lists = model('shops')->where($where)->field($field)->order('id desc')->limit(10)->select();
        if(!$lists->isEmpty())
        {
            $lists = $lists->toArray();
            foreach($lists as &$val)
            {
                $val['price'] = str_replace(['<i>','</i>'],'',$val['price']);
            }
            $return['code'] = 200;
        }
        $return['data'] = $lists;
        return json($return);
    }
    /**
     * @return \think\response\Json
     * 商铺出租搜索
     */
    public function shopsRental()
    {
        $keyword = input('get.keyword');
        $city    = input('get.city/d',0);
        $return['code']  = 0;
        $where['status'] = 1;
        if($keyword)
        {
            $where[] = ['title|estate_name','like','%'.$keyword.'%'];
        }
        if($city)
        {
            $city_arr = $this->getCityChild($city);
            $where[] = ['city','in',$city_arr];
        }
        $field = 'id,title,price,address';
        $lists = model('shops_rental')->where($where)->field($field)->order('id desc')->limit(10)->select();
        if(!$lists->isEmpty())
        {
            $lists = $lists->toArray();
            foreach($lists as &$val)
            {
                $val['price'] = str_replace(['<i>','</i>'],'',$val['price']);
            }
            $return['code'] = 200;
        }
        $return['data'] = $lists;
        return json($return);
    }
    /**
     * @return \think\response\Json
     * 添加二手房和出租房 属性选择
     */
    public function getHouseAttr()
    {
        $model = input('get.model/second_house');
        $return['code'] = 200;
        $data['city']   = $this->getCity();
        $data['renovation'] = getLinkMenuCache(8);
        $data['house_type'] = getLinkMenuCache(9);
        $data['floor']      = array_values(getLinkMenuCache(7));
        $data['supporting']    = getLinkMenuCache(12);
        $data['acreage_unit'] = config('filter.acreage_unit');
        $time = getHouseTimeOut();
        $time_arr = [];
        foreach($time as $key=>$v)
        {
            $time_arr[] = [
              'id' => $key,
              'value' => $v
            ];
        }
        $data['time_out'] = $time_arr;
        if($model == 'rental')
        {
            $data['rent_type']   = getLinkMenuCache(10);
            $data['pay_type']    = getLinkMenuCache(11);
            $data['rental_price_unit'] = config('filter.rental_price_unit');
        }
        $return['data'] = $data;
        return json($return);
    }
    private function getCity()
    {
        $city_tree = getCity();
        $city      = [];
        $area      = [];
        $temp      = [];
        $data = [];
        if($city_tree)
        {
            foreach($city_tree as $v)
            {
                $city[] = [
                  'id' => $v['id'],
                  'name' => $v['name']
                ];
                if(isset($v['_child']))
                {
                    foreach($v['_child'] as $val)
                    {
                        $temp[] = [
                            'id' => $val['id'],
                            'name' => $val['name'],
                            '_child' => isset($val['_child'])?$val['_child']:[]
                        ];
                    }
                }else{
                    $temp = [];
                }
                $area[] = $temp;
                $temp = [];
            }
            $data['city'] = $city;
            $data['area'] = $area;
        }
        return $data;
    }
}
<?php

namespace app\api\controller\user;


class Resources extends UserBase
{
    private $pageSize = 10;
    public function index()
    {
        return;
    }
    /**
     * @return \think\response\Json
     * 二手房列表
     */
    public function second()
    {
        $page               = input('get.page/d',1);
        $where['broker_id'] = $this->userInfo['id'];
        $return['code']     = 0;
        $field   = "id,title,img,room,status,living_room,price,address,acreage,FROM_UNIXTIME(create_time,'%Y-%m-%d') as create_time";
        $obj     = model('second_house');
        $lists   = $obj->where($where)->field($field)->order('id desc')->page($page)->limit($this->pageSize)->select();
        $count   = $obj->removeOption()->where($where)->count();
        $total_page = ceil($count/$this->pageSize);
        if(!$lists->isEmpty())
        {
            $lists = $lists->toArray();
            foreach($lists as &$v)
            {
                $v['price']   = str_replace(['<i>','</i>'],['',''],$v['price']);
                $v['img']     = $this->getImgUrl(thumb($v['img'],120,80));
                $v['acreage'] = $v['acreage'].config('filter.acreage_unit');
                $v['status_name']  = $v['status'] == 1 ? '发布' : '待审';
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
     * 出租房列表
     */
    public function rental()
    {
        $page               = input('get.page/d',1);
        $where['broker_id'] = $this->userInfo['id'];
        $return['code']     = 0;
        $field   = "id,title,img,room,status,living_room,price,address,acreage,FROM_UNIXTIME(create_time,'%Y-%m-%d') as create_time";
        $obj     = model('rental');
        $lists   = $obj->where($where)->field($field)->order('id desc')->page($page)->limit($this->pageSize)->select();
        $count   = $obj->removeOption()->where($where)->count();
        $total_page = ceil($count/$this->pageSize);
        if(!$lists->isEmpty())
        {
            $lists = $lists->toArray();
            foreach($lists as &$v)
            {
                $v['price']   = str_replace(['<i>','</i>'],['',''],$v['price']);
                $v['img']     = $this->getImgUrl(thumb($v['img'],120,80));
                $v['acreage'] = $v['acreage'].config('filter.acreage_unit');
                $v['status_name']  = $v['status'] == 1 ? '发布' : '待审';
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
     * 二手房详细
     */
    public function readSecond()
    {
        $id = input('get.id/d',0);
        $return['code'] = 0;
        if($id)
        {
            $where['h.id'] = $id;
            $where['h.broker_id'] = $this->userInfo['id'];
            $join = [['second_house_data d','d.house_id = h.id','left']];
            $field = "h.*,d.supporting,d.file,d.info";
            $info  = model('second_house')->alias('h')->where($where)->field($field)->join($join)->find();
            if($info)
            {
                $price = $info->getData('price');
                $info = $info->toArray();
                $info['info']   = strip_tags($info['info']);
                $info['file']   = json_decode($info['file']);
                $info['price_data'] = $price;
                $info['city_ids']   = $this->getCityParentId($info['city']);
                $info['is_timeout'] = $info['timeout'] < time() ? 1 : 0;//是否过期
                $info['timeout']    = date('Y-m-d',$info['timeout']);
                $return['code'] = 200;
                $return['data'] = $info;
            }else{
                $return['msg']  = '未找到相关数据';
            }
        }else{
            $return['msg']      = '参数错误';
        }
        return json($return);
    }

    /**
     * @return \think\response\Json
     * 出租房详细
     */
    public function readRental()
    {
        $id = input('get.id/d',0);
        $return['code'] = 0;
        if($id)
        {
            $where['h.id'] = $id;
            $where['h.broker_id'] = $this->userInfo['id'];
            $join = [['rental_data d','d.house_id = h.id','left']];
            $field = "h.*,d.supporting,d.file,d.info";
            $info  = model('rental')->alias('h')->where($where)->field($field)->join($join)->find();
            if($info)
            {
                $price = $info->getData('price');
                $info = $info->toArray();
                $info['info']   = strip_tags($info['info']);
                $info['file']   = json_decode($info['file']);
                $info['price_data'] = $price;
                $info['city_ids']   = $this->getCityParentId($info['city']);
                $info['is_timeout'] = $info['timeout'] < time() ? 1 : 0;//是否过期
                $info['timeout']    = date('Y-m-d',$info['timeout']);
                $return['code'] = 200;
                $return['data'] = $info;
            }else{
                $return['msg']  = '未找到相关数据';
            }
        }else{
            $return['msg']      = '参数错误';
        }
        return json($return);
    }
    /**
     * @param $id
     * @return array
     * 获取指定区域id的所有父id
     */
    private function getCityParentId($id)
    {
        $arr  = [];
        $default = [0,0,0];
        $spid = model('city')->where('id',$id)->value('spid');
        if($spid)
        {
            $arr   = array_filter(explode('|',$spid));
        }
        $arr[] = $id;
        $arr = $arr + $default;
        return $arr;
    }
}
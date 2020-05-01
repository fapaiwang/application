<?php

namespace app\api\controller;

use app\common\controller\ApiBase;
class Shops extends ApiBase
{
    private $pageSize = 15;

    /**
     * @return \think\response\Json
     * 列表
     */
    public function index()
    {
        $return['code'] = 0;
        $page           = input('get.page/d',1);
        $where   = $this->search();
        $sort    = input('get.sort/d',0);
        $field   = "id,title,city,img,price,tags,acreage,renovation,type,update_time";
        $obj     = model('shops');
        $lists   = $obj->where($where)->field($field)->order($this->getSort($sort))->page($page)->limit($this->pageSize)->select();
        $count   = $obj->removeOption()->where($where)->count();
        $total_page = ceil($count/$this->pageSize);
        if(!$lists->isEmpty())
        {
            $lists = $lists->toArray();
            foreach($lists as &$v)
            {
                $tags         = array_filter(explode(',',$v['tags']));
                $v['price']   = str_replace(['<i>','</i>'],['',''],$v['price']);
                $v['img']     = $this->getImgUrl(thumb($v['img'],400,300));
                $v['city']    = getCityName($v['city'],'-');
                $v['tags']    = $this->getTags(20,$tags);
                $v['acreage'] = $v['acreage'].config('filter.acreage_unit');
                $v['renovation']  = getLinkMenuName(8,$v['renovation']);
                $v['type']        = getLinkMenuName(18,$v['type']);
                $v['update_time'] = getTime($v['update_time'],'mohu').'更新';
            }
            $return['code'] = 200;
        }
        $return['page']       = $page;
        $return['total_page'] = $total_page;
        $return['data']       = $lists;
        return json($return);
    }

    /**
     * @param $id
     * @return \think\response\Json
     * 详情页
     */
    public function read($id)
    {
        $return['code'] = 0;
        if($id)
        {
            $where['h.id']     = $id;
            $where['h.status'] = 1;
            $obj  = model('shops');
            $join = [['shops_data d','h.id=d.house_id']];
            $field = "h.*,d.info,d.file,d.mating";
            $info = $obj->alias('h')->join($join)->field($field)->where($where)->find();
            if($info)
            {
                $info = objToArray($info);
                $file = $info['file']?json_decode($info['file'], true):[];
                $info['file']       = $this->turnFileUrl($file);
                $info['img']        = $this->getImgUrl($info['img']);
                $info['renovation'] = getLinkMenuName(8,$info['renovation']);
                $info['price']        = str_replace(['<i>','</i>'],['',''],$info['price']);
                $info['acreage']      = $info['acreage'].config('filter.acreage_unit');
                $info['tags']         = $this->getTags(20,explode(',',$info['tags']),30);
                $info['pano_url']     = base64_encode($info['pano_url']);
                $info['relation']     = $this->samePriceHouse($info['price']);
                $info['info']         = $this->filterContent($info['info']);
                $info['floor']        = getLinkMenuName(7,$info['floor']);
                $info['type']         = getLinkMenuName(18,$info['type']);
                $info['industry']         = $this->getTags(19,explode(',',$info['industry']),30);
                $info['mating']         = $this->getTags(21,explode(',',$info['mating']),30);
                $info['update_time']  = getTime($info['update_time'],'mohu').'更新';
                updateHits($info['id'],'shops');
                $return['code'] = 200;
            }
            $return['data'] = $info;
        }else{
            $return['msg']  = '参数错误';
        }
        return json($return);
    }
    /**
     * @return \think\response\Json
     * 筛选条件
     */
    public function shopsAttr()
    {
        $city               = getCity();
        $data['city']       = isset($city[$this->city]['_child']) ? $city[$this->city]['_child'] : $city;
        $data['price']      = getBussinessCondition('shops_price');
        $data['type']       = getLinkMenuCache(18);
        $data['tags']       = getLinkMenuCache(20);
        $data['renovation'] = getLinkMenuCache(8);
        $data['acreage']    = getBussinessCondition("shops_acreage");
        $data['sort']       = [
            0 => ['id'=>0,'name'=>'默认'],
            1 => ['id'=>1,'name'=>'总价从低到高'],
            2 => ['id'=>2,'name'=>'总价从高到低'],
            3 => ['id'=>3,'name'=>'均价从低到高'],
            4 => ['id'=>4,'name'=>'均价从高到低'],
            5 => ['id'=>5,'name'=>'面积从低到高'],
            6 => ['id'=>6,'name'=>'面积从高到低']
        ];
        $return['code'] = 1;
        $return['data'] = $data;
        return json($return);
    }
    /**
     * @return array
     * 搜索条件
     */
    private function search()
    {
        $estate_id     = input('get.estate_id/d',0);//小区id
        $param['city'] = input('get.city/d', $this->city);
        $param['price']      = input('get.price',0);
        $param['acreage']    = input('get.acreage',0);//面积
        $param['type']       = input('get.type',0);//物业类型
        $param['renovation'] = input('get.renovation',0);//装修情况
        $param['sort']       = input('get.sort/d',0);//排序
        $param['tags']       = input('get.tags/d',0);//标签
        $data['status']    = 1;
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
        if(!empty($param['type']))
        {
            $data['type'] = $param['type'];
        }
        if($param['renovation'])
        {
            $data['renovation'] = $param['renovation'];
        }
        $data[] = ['timeout','gt',time()];
        if($keyword)
        {
            $param['keyword'] = $keyword;
            $data[] = ['title|estate_name','like','%'.$keyword.'%'];
        }
        if(!empty($param['price']))
        {
            $data[] = getBussinessCondition('shops_price','price',$param['price']);
        }
        if(!empty($param['acreage']))
        {
            $data[] = getBussinessCondition('shops_acreage','acreage',$param['acreage']);
        }
        if(!empty($param['tags'])){
            $data[] = ['','exp',\think\Db::raw("find_in_set({$param['tags']},tags)")];
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
    /**
     * @param $price
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 价格相似房源
     */
    private function samePriceHouse($price,$num = 4)
    {
        $min_price = $price > 10 ? $price - 10:$price;
        $max_price = $price + 10;
        $lists = model('shops')
            ->where('status',1)
            ->where('price','between',[$min_price,$max_price])
            ->where('timeout','gt',time())
            ->where('city','in',$this->getCityChild())
            ->field('id,title,img,city,tags,type,renovation,acreage,price,update_time')
            ->order('create_time desc')
            ->limit($num)
            ->select();
        if(!$lists->isEmpty())
        {
            $lists = $lists->toArray();
            foreach($lists as &$v)
            {
                $tags         = array_filter(explode(',',$v['tags']));
                $v['price']   = str_replace(['<i>','</i>'],['',''],$v['price']);
                $v['img']     = $this->getImgUrl(thumb($v['img'],400,300));
                $v['city']    = getCityName($v['city'],'-');
                $v['tags']    = $this->getTags(16,$tags);
                $v['acreage'] = $v['acreage'].config('filter.acreage_unit');
                $v['renovation']  = getLinkMenuName(8,$v['renovation']);
                $v['type']        = getLinkMenuName(15,$v['type']);
                $v['update_time'] = getTime($v['update_time'],'mohu').'更新';
            }
        }
        return $lists;
    }
}
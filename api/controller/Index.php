<?php

namespace app\api\controller;

use app\common\controller\ApiBase;
use think\Request;

class Index extends ApiBase
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $return['code'] = 200;
        $return['data'] = [
            'slides' => $this->getSlides(5),
            'news' => $this->getTopNews(),
            'house' => $this->houseLists(1),
            'second' => $this->getNewSecond(),
            'group'  => $this->getGroupLists(),
            'cityInfo' => $this->cityInfo
        ];
        return json($return);
    }

    /**
     * @param $id
     * @return array|\PDOStatement|string|\think\Collection
     * 轮播图
     */
    private function getSlides($id)
    {
        $map['spaceid'] = $id;
        $map[]   = ['startdate','lt',time()];
        $map[]   = ['enddate','gt',time()];
        $map['status']    = 1;
        $this->city && $map['city_id'] = $this->city;
        $lists = model('poster')->where('status',1)->where($map)->field('setting')->order(['ordid'=>'asc','id'=>'desc'])->limit(5)->select();
        $data  = [];
        if(!$lists->isEmpty())
        {
            foreach($lists as $v)
            {
                $data[] = [
                    'linkurl' => $v['setting']['linkurl'],
                    'imgurl'  => $this->getImgUrl($v['setting']['fileurl'])
                ];
            }
        }
        return $data;
    }
    /**
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 资讯
     */
    private function getTopNews($num = 5)
    {
        $where['status'] = 1;
        $this->city && $where['city'] = $this->city;
        $lists = model('article')->where($where)->field('id,title')->order(['ordid'=>'asc','id'=>'desc'])->limit($num)->select();
        return $lists;
    }
    /**
     * @param $city
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 团购列表
     */
    private function getGroupLists($num = 5)
    {
        $time            = time();
        $where['status'] = 1;
        $where[]         = ['begin_time','lt',$time];
        $where[]         = ['end_time','gt',$time];
        $city_arr        = $this->getCityChild();
        $city_arr && $where[] = ['city','in',$city_arr];
        $lists = model('group')->where($where)->field('id,city,title,end_time,price,discount,img')->order(['ordid'=>'asc','id'=>'desc'])->limit($num)->select();
        if(!$lists->isEmpty())
        {
            foreach($lists as &$v)
            {
                $v['city'] = getCityName($v['city'],'-');
                $v['price'] = $v['price'] == 0 ? '待定':$v['price'].config('filter.house_price_unit');
                $v['img']   = $this->getImgUrl(thumb($v['img'],120,80));
            }
        }
        return $lists;
    }
        /**
         * @param $cate_id
         * @param int $num
         * @return array|\PDOStatement|string|\think\Collection
         * 推荐新房
         */
    private function houseLists($cate_id,$num=5)
    {
        $where['p.status']  = 1;
        $where['p.cate_id'] = $cate_id;
        $where['h.status']  = 1;
        $where['p.model']   = 'house';
        $city_arr        = $this->getCityChild();
        $city_arr && $where[] = ['city','in',$city_arr];
        $join  = [
            ['house h','p.house_id = h.id']
        ];
        $field = 'h.id,h.is_discount,h.discount,h.img,h.title,h.tags_id,h.price,h.unit,h.city';
        $lists = model('position')->alias('p')
            ->join($join)
            ->where($where)
            ->field($field)
            ->order( ['h.ordid'=>'asc','h.id'=>'desc'])
            ->limit($num)
            ->select();
        if(!$lists->isEmpty())
        {
            foreach($lists as &$v)
            {
                $v['img']     = $this->getImgUrl(thumb($v['img'],120,80));
                $v['city']    = getCityName($v['city'],'-');
                $v['price']   = $v['price'] == 0 ? '待定':$v['price'].getUnitData($v['unit']);
                $tags = array_filter(explode(',',$v['tags_id']));
                $v['tags'] = $this->getTags(3,$tags);
            }
        }
        return $lists;
    }

    /**
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 二手房
     */
    private function getNewSecond($num = 5)
    {
        $where['status'] = 1;
        $city_arr        = $this->getCityChild();
        $city_arr && $where[] = ['city','in',$city_arr];
        $field = 'id,title,estate_name,img,room,living_room,city,price,acreage,tags';
        $lists = model('second_house')->where($where)->field($field)->order(['ordid'=>'asc','id'=>'desc'])->limit($num)->select();
        if(!$lists->isEmpty())
        {
            $lists = $lists->toArray();
            foreach($lists as &$v)
            {
                $v['price']   = str_replace('<i>万</i>','万',$v['price']);
                $v['img']     = $this->getImgUrl($v['img']);
                $v['city']    = getCityName($v['city'],'-');
                $v['acreage'] = $v['acreage'].config('filter.acreage_unit');
                $v['tags']    = array_filter(explode(',',$v['tags']));
            }
        }
        return $lists;
    }
}

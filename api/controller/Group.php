<?php
namespace app\api\controller;
use app\common\controller\ApiBase;
class Group extends ApiBase
{
    private $pageSize = 10;

    /**
     * @return \think\response\Json
     * 团购列表
     */
    public function index()
    {
        $time            = time();
        $page            = input('get.page/d',1);
        $where['status'] = 1;
        $where[]  = ['begin_time','lt',$time];
        $where[]  = ['end_time','gt',$time];
        $city_arr = $this->getCityChild();
        $city_arr && $where[] = ['city','in',$city_arr];
        $field   = "id,title,city,img,end_time,house_title,discount,price";
        $obj     = model('group');
        $lists   = $obj->where($where)->field($field)->order(['ordid'=>'asc','id'=>'desc'])->page($page)->limit($this->pageSize)->select();
        $count   = $obj->removeOption()->where($where)->count();
        $total_page = ceil($count/$this->pageSize);
        if(!$lists->isEmpty())
        {
            foreach($lists as &$v)
            {
                $v['city']   = getCityName($v['city']);
                $v['price']  = $v['price'].config('filter.house_price_unit');
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
     * @param $id
     * 团购详细页
     */
    public function read($id)
    {
        $return['code'] = 0;
        if($id)
        {
            $where['g.id']     = $id;
            $where['g.status'] = 1;
            $field = 'g.*,h.address,h.sale_phone,h.title as house_title';
            $join  = [['house h','h.id = g.house_id']];
            $info  = model('group')->alias('g')->join($join)->field($field)->where($where)->find();
            if($info) {
                model('group')->where('id', $id)->setInc('hits');
                $info['sale_phone'] = json_decode($info['sale_phone'], true);
                $info['file']       = $this->turnFileUrl($info['file']);
                $info['img']        = $this->getImgUrl($info['img']);
                $info['city']       = getCityName($info['city']);
                $info['price_unit'] = config('filter.house_price_unit');
                $info['info']       = $this->filterContent($info['info']);
                $return['code']     = 200;
            }
            $return['data'] = $info;
        }else{
            $return['msg']  = '参数错误';
        }
        return json($return);
    }
}
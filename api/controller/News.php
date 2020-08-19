<?php
namespace app\api\controller;
use app\common\controller\ApiBase;
class News extends ApiBase
{
    private $pageSize = 10;

    /**
     * @return \think\response\Json
     * 新闻资讯列表
     */
    public function index()
    {
        $cate_id = input('get.cate/d',0);
        $page    = input('get.page/d',1);
        $return['code']  = 0;
        $where['status'] = 1;
        $this->city && $where['city'] = $this->city;
        $cateObj         = model('article_cate');
        if($cate_id)
        {
            $cate_ids   = $cateObj->get_child_ids($cate_id,true);
            $where[]    = ['cate_id','in',$cate_ids];
        }
        $obj   = model('article');
        $obj   = $obj->where($where)->field('id,title,img,hits,come_from,description,create_time')->order('ordid asc,id desc');
        $lists = $obj->page($page)->limit($this->pageSize)->select();
        if(!$lists->isEmpty())
        {
            foreach($lists as &$v)
            {
                $v['img'] = $this->getImgUrl(thumb($v['img'],120,80));
                $v['create_time_date'] = getTime($v['create_time']);
                $v['come_from']   = empty($v['come_from'])?getSettingCache('site','title'):$v['come_from'];
            }
            $return['code'] = 200;
        }
        $obj->removeOption();
        $count = $obj->where($where)->count();
        $total_page = ceil($count/$this->pageSize);
        $return['page']       = $page;
        $return['total_page'] = $total_page;
        $return['data']       = $lists;
        return json($return);
    }

    /**
     * 新闻资讯分类
     */
    public function cate()
    {
        $where['status'] = 1;
        $where['pid']    = 0;
        $return['code']  = 0;
        $lists = model('article_cate')->field('id,name')->where($where)->order(['ordid'=>'asc','id'=>'asc'])->select();
        $list_append = ["id"=>0,"name"=>"全部"];
        if(!$lists->isEmpty())
        {
            $lists = $lists->toArray();
            array_unshift($lists,$list_append);
            $return['code'] = 200;
            $return['data'] = $lists;
        }
        return json($return);
    }

    /**
     * @param $id
     * @return \think\response\Json
     * 新闻资讯详细页
     */
    public function read($id)
    {
        $return['code'] = 0;
        if($id)
        {
            $where['id']     = $id;
            $where['status'] = 1;
            $info = model('article')->where($where)->field("id,title,hits,FROM_UNIXTIME(create_time,'%Y-%m-%d') as create_time,info,house_id")->find();
            if($info)
            {
                updateHits($id,'article');
                $info['info']   = $this->filterContent($info['info']);
                $info['house_info'] = $this->getHouseInfo($info['house_id']);
                $return['code'] = 200;
                $return['data'] = $info;
            }
        }
        return json($return);
    }

    /**
     * @param $id
     * @return array|null|\PDOStatement|string|\think\Model
     * 关联楼盘信息
     */
    private function getHouseInfo($id)
    {
        $where['id']     = $id;
        $where['status'] = 1;
        $info = model('house')->where($where)->field('id,img,title,price,city,unit,sale_phone')->find();
        if($info)
        {
            $info['img']   = $this->getImgUrl(thumb($info['img'],120,80));
            $info['price'] = $info['price'].$info['unit'];
            $info['city']  = getCityName($info['city'],'-');
        }
        return $info;
    }
}
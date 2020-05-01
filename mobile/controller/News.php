<?php


namespace app\mobile\controller;
use app\common\controller\MobileBase;
class News extends MobileBase
{
    private $pageSize = 10;
    public function index()
    {
        $data = $this->getLists(1);
        $cate = getCate('articleCate','tree');
        $this->assign('lists',$data['lists']);
        $this->assign('total_page',$data['total_page']);
        $this->assign('cate',$cate);
        $this->assign('title','新闻资讯');
        return $this->fetch();
    }
    /**
     * @return mixed
     * 新闻详细
     */
    public function detail()
    {
        $id = input('param.id/d',0);
        if($id)
        {
            $where['status'] = 1;
            $where['id']     = $id;
            $info = model('article')->where($where)->find();
            if(!$info)
            {
                return $this->fetch('public/404');
            }
            if($info['house_id'])
            {
                //获取楼盘信息
                $house_info = model('house')->where('id',$info['house_id'])->where('status',1)->field('id,img,title,unit,price,sale_phone,address,city')->find();
                if($house_info)
                {
                    $city_spid = model('city')->get_spid($house_info['city']);
                    $city_arr  = explode('|',$city_spid);
                    $city_pid  = $city_arr[0];
                    $house_info['url'] = url("House/detail",['id'=>$house_info['id']]).'?area='.$city_pid;
                }
                $this->assign('house_info',$house_info);
            }
            $this->setSeo($info);
            updateHits($info['id'],'article');
            $this->assign('info',$info);
            $this->assign('relation',$this->relationArticle($info['cate_id']));
            $this->assign('title',$info['title']);
        }else{
            return  $this->fetch('public/404');
        }
        return $this->fetch();
    }
    public function getNewsLists()
    {
        $page    = input('get.page/d',1);
        $data    = $this->getLists($page);
        $lists   = $data['lists'];
        $return['code'] = 1;
        $return['data'] = $lists;
        $return['total_page'] = $data['total_page'];
        return json($return);
    }
    /**
     * @param int $page
     * @return array|\PDOStatement|string|\think\Collection
     * 获取新闻列表
     */
    private function getLists($page = 1)
    {
        $cate_id = input('param.cate/d',0);
        $where['status'] = 1;
        $cateObj         = model('article_cate');
        if($cate_id)
        {
            $cate_ids   = $cateObj->get_child_ids($cate_id,true);
            $where[]    = ['cate_id','in',$cate_ids];
        }
        $city  = $this->cityInfo['id'];
        $city && $where[] = ['city','eq',$city];
        $obj   = model('article');
        $obj   = $obj->where($where)->field('id,title,img,info,hits,come_from,description,create_time')->order('ordid asc,id desc');
        $lists = $obj->page($page)->limit($this->pageSize)->select();
        if($lists)
        {
            foreach($lists as &$v)
            {
                $img = $this->getInfoImg($v['info']);
                $v['img'] = empty($img)?$v['img']:$img;
                $v['url'] = url('News/detail',['id'=>$v['id']]);
                $v['create_time_date'] = getTime($v['create_time']);
                $v['come_from']   = empty($v['come_from'])?getSettingCache('site','title'):$v['come_from'];
            }
        }
        $obj->removeOption();
        $count = $obj->where($where)->count();
        $total_page = ceil($count/$this->pageSize);
        $lists      = ['lists'=>$lists,'total_page'=>$total_page];
        $this->assign('cate_id',$cate_id);
        return $lists;
    }
    private function getInfoImg($info){
        $ext = 'gif|jpg|jpeg|bmp|png';
        if(preg_match_all("/(href|src)=([\"|']?)([^ \"'>]+\.($ext))\\2/i", $info, $matches)){
            return $matches[3];
        }
        return [];
    }
    private function relationArticle($cate_id,$num = 5)
    {
        $where['status']  = 1;
        $where['cate_id'] = $cate_id;
        $lists = model('article')->where($where)->field('id,title,img,come_from,create_time')->order('create_time desc')->limit($num)->select();
        return $lists;
    }
}
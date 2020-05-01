<?php


namespace app\home\controller;
use app\common\controller\HomeBase;
class News extends HomeBase
{
    /**
     * @return mixed
     * 新闻列表
     */
    public function index()
    {
        $cate    = getCate('articleCate','tree');
        $cate_id = input('param.cate/d',0);
        $where['status'] = 1;
        $cateObj         = model('article_cate');
        if($cate_id)
        {
            $info = $cateObj->where(['id'=>$cate_id])->field('name,seo_title,seo_keys,seo_desc')->find();
            $cate_ids         = $cateObj->get_child_ids($cate_id,true);
            $where[] = ['cate_id','in',$cate_ids];
            $this->setSeo($info,'name');
        }
        $this->cityInfo['id'] && $where[] = ['city','eq',$this->cityInfo['id']];
        $lists = model('article')->where($where)->field('id,title,img,hits,description,create_time')->order('ordid asc,id desc')->paginate(10);
        $this->assign('cate_id',$cate_id);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('cate',$cate);
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
            $cate    = getCate('articleCate','tree');
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
                   $city      = getCity('cate');
                   $domain    = isset($city[$city_pid])?$city[$city_pid]['domain']:'www';
                   $house_info['url'] = $this->site['city_domain'] == 1 ? $url = url("House/detail@".$domain,['id'=>$house_info['id']]):url("House/detail",['id'=>$house_info['id']]).'?area='.$city_pid;
               }
                $this->assign('house_info',$house_info);
            }
            $this->setSeo($info);
            updateHits($info['id'],'article');
            $this->assign('cate',$cate);
            $this->assign('info',$info);
            $this->assign('relation',$this->relationArticle($info['cate_id']));
        }else{
            return  $this->fetch('public/404');
        }
        return $this->fetch();
    }
    private function relationArticle($cate_id,$num = 5)
    {
        $where['status']  = 1;
        $where['cate_id'] = $cate_id;
        $this->cityInfo['id'] && $where['city'] = $this->cityInfo['id'];
        $lists = model('article')->where($where)->field('id,title,create_time')->order('create_time desc')->limit($num)->select();
        return $lists;
    }
}
<?php


namespace app\home\controller;
use app\common\controller\HomeBase;
use app\home\service\IndexServer;
use app\home\service\NewsService;
use http\Message;
use think\facade\Log;
use think\facade\Request;

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
        $hits = input('hits',"");
        $keyword = input('keyword',"");
        $where['status'] = 1;
        $cateObj = model('article_cate');
        if($cate_id)
        {
            $info = $cateObj->where(['id'=>$cate_id])->field('name,seo_title,seo_keys,seo_desc')->find();
            $cate_ids         = $cateObj->get_child_ids($cate_id,true);
            $where[] = ['cate_id','in',$cate_ids];
            $this->setSeo($info,'name');
        }
        $news = new NewsService();
        $this->cityInfo['id'] && $where[] = ['city','eq',$this->cityInfo['id']];
        if ($keyword != "") {
            $where[] = ['title','like','%' . $keyword . '%'];
        }
        $order = "ordid asc,id desc";
        if ($hits != "" && $hits == 'hot') {
            $order = "hits desc";
        }

        $lists = model('article')->where($where)->field('id,title,img,hits,description,create_time')->order($order)->paginate(6);
        $this->assign("newPic",$news->get_banner(16));
        $this->assign('cate_id',$cate_id);
        $this->assign('lists',$lists);
        $this->assign('keyword',$keyword);
        $this->assign('pages',$lists->render());
        $this->assign('cate',$cate);
        $this->assign('hotArticle',$news->get_new_list(5));
        $this->assign('hotAns',$news->get_ans_list(5));
        $this->assign('smallPic',$news->get_banner(17));
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
    
            $news = new NewsService();
            $index = new IndexServer();
            $this->assign('cate',$cate);
            $this->assign('info',$info);
            $this->assign('hotArticle',$news->get_new_list(5));
            $this->assign('hotAns',$news->get_ans_list(5));
            $this->assign('relation',$this->relationArticle($info['cate_id']));
            $this->assign("page_t",5);
            $this->assign('smallPic',$news->get_banner(17));
            $this->assign('bottomPic',$index->get_home_banner(18,2));
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
    
    /***
     * @description 预约咨询
     * @auther xiaobin
     */
    public function reserve()
    {
        $news = new NewsService();
        $tel  = input('post.phone');
        $code = input('post.code');
        if (!$news->checkMobile($tel)) {
            return $news->Error("手机号码不正确");
        }
        if ($code == "" || $code != cache($tel)){
            return $news->Error("验证码不正确");
        }
        
        $data["user_name"] = "游客";
        $data['mobile'] = $tel;
        $msg = "预约的人太多了，请稍后尝试";
        
        if (model("tijiao")->save($data)) {
            $msg = "预约成功";
        }
        return json(array(
            "code" => 200,
            "msg" => $msg,
        ));
    }
}
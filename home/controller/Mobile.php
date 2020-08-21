<?php


namespace app\home\controller;
use http\Message;

class Mobile extends \think\Controller
{
    /**
     * @return mixed
     * 新闻详细
     */
    public function new_detail()
    {

        $id = input('param.id/d', 0);
        if ($id) {
            $where['status'] = 1;
            $where['id'] = $id;
            $info = model('article')->where($where)->find();
            if (!$info) {
                return $this->fetch('public/404');
            }
            if ($info['house_id']) {
                //获取楼盘信息
                $house_info = model('house')->where('id', $info['house_id'])->where('status', 1)->field('id,img,title,unit,price,sale_phone,address,city')->find();
                if ($house_info) {
                    $city_spid = model('city')->get_spid($house_info['city']);
                    $city_arr = explode('|', $city_spid);
                    $city_pid = $city_arr[0];
                    $house_info['url'] = url("House/detail", ['id' => $house_info['id']]) . '?area=' . $city_pid;
                }
                $this->assign('house_info', $house_info);
            }
            $article_cate = model("article_cate")->field("name")->where("id",$info["cate_id"])->find();
            $article_cate_type = $article_cate->name ?? "法拍百科";
            $seo['title'] = $info->title."-".$article_cate_type."-金铂顺昌房拍网";
            $seo['keys'] = $info->title.",".$article_cate_type.",金铂顺昌房拍网";
            $seo['desc'] =  $info->title.",更多法拍资讯、行业知识尽在金铂顺昌房拍网。";
            $this->assign('seo',$seo);
            updateHits($info['id'], 'article');
            $this->assign('info', $info);
            $this->assign('site',  getSettingCache('site'));
            $this->assign('relation', $this->relationArticle($info['cate_id']));
            $this->assign('title', $info['title']);
        } else {
            return $this->fetch('public/404');
        }
        return $this->fetch();
    }
    private function relationArticle($cate_id,$num = 5)
    {
        $where['status']  = 1;
        $where['cate_id'] = $cate_id;
        $lists = model('article')->where($where)->field('id,title,img,come_from,create_time')->order('create_time desc')->limit($num)->select();
        return $lists;
    }
}
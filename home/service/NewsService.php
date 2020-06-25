<?php

namespace app\home\service;

use think\facade\Cache;

class NewsService
{
    /**
     * @description 获取轮播图
     * @param int $space_id 父ID
     * @param int $limit 获取条数
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @auther xiaobin
     */
    public function get_banner($space_id=1,$limit=1)
    {
        $cache_name = 'poster_img'.$space_id;
        $banner = Cache::get($cache_name);
        if (!$banner){
            $cache_name = 'poster_img'.$space_id;
            $param = [
                ['spaceid', '=', $space_id],
                ['startdate', '<', time()],
                ['enddate', '>', time()]
            ];
            $poster_space = model('poster')->field('name,setting')->where($param)->limit($limit)->select();
            \think\facade\Cache::set($cache_name,$poster_space);
        }
        return $banner;
    }

    /**
     * @param string $cate_id 类型id
     * @param string $hits  点击次数
     * @param $keyword 搜索标题
     * @param mixed
     * @return array|\PDOStatement|string|\think\Collection
     * @author: al
     */
    public function article($cate_id="",$hits="",$keyword=""){
        $where[] = ["status",'=',1];
        if ($cate_id){//有类型
            $where[] = ["cate_id",'=',$cate_id];
        }
        //排序正序/id倒序  hits点击次数
        $order = "ordid asc,id desc";
        if ($hits != "" && $hits == 'hot') {
            $order = "hits desc";
        }
        if ($keyword != "") {
            $where[] = ['title','like','%' . $keyword . '%'];
        }
        $lists = model('article')->where($where)->field('id,title,img,hits,description,create_time')->order($order)->select();

        return $lists;
    }

    /**
     * @description 热门文章
     * @param int $limit 读取条数
     * @return \think\Paginator
     * @throws \think\exception\DbException
     * @auther xiaobin
     */
    public function get_new_list($limit = 5)
    {
        return model('article')->field('id,title')
            ->where('cate_id','neq',10)
            ->cache('hot_news',3600)
            ->order('hits desc')
            ->limit($limit)->select();
    }
    
    /**
     * @description 热门问答
     * @param int $limit 读取条数
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @auther xiaobin
     */
    public function get_ans_list($limit = 5)
    {
        return model('article')
            ->field('id,title,hits,create_time')
            ->where('cate_id',10)
            ->cache('answer',3600)
            ->order('hits desc')
            ->limit($limit)->select();
    }
    
    /**
     * @description 验证是否为合法手机号码
     * @param $mobile 手机号码
     * @return bool
     * @auther xiaobin
     */
    public function checkMobile($mobile)
    {
        if (strlen($mobile) < 12 && preg_match("/^1{1}\d{10}$/", $mobile)) {
            return true;
        }
        return false;
    }
    
    /**
     * @description 返回正确内容
     * @param $msg
     * @return \think\response\Json
     * @auther xiaobin
     */
    public function Success($msg)
    {
        return json(array(
            "code" => 200,
            "msg" => $msg,
        ));
    }
    
    /**
     * @description 返回错误内容
     * @param $msg
     * @return \think\response\Json
     * @auther xiaobin
     */
    public function Error($msg)
    {
        return json(array(
            "code" => 500,
            "msg" => $msg,
        ));
    }
}
<?php

namespace app\home\service;

use app\api\service\BasicsService;
use app\tools\ApiResult;
use think\facade\Cache;

class NewsService
{
    use ApiResult;
    private $pageSize = 10;
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
    public function article($cate_id="",$hits="",$keyword="",$page=1){
        $where[] = ["status",'=',1];
        $return['code']="20000";
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

        $obj   = model('article');
        $obj   = $obj->where($where)->field('id,title,img,hits,come_from,description,create_time')->order('ordid asc,id desc');
        $lists = $obj->page($page)->limit($this->pageSize)->select();

        if(!$lists->isEmpty())
        {
            foreach($lists as &$v)
            {
                $v['create_time_date'] = getTime($v['create_time']);
                $v['come_from']   = empty($v['come_from'])?getSettingCache('site','title'):$v['come_from'];
            }
            $return['code'] = 10000;
        }
        $obj->removeOption();
        $count = $obj->where($where)->count();
        $total_page = ceil($count/$this->pageSize);
        $return['page']       = $page;
        $return['total_page'] = $total_page;
        $return['data']       = $lists;
        return $return;
    }

    /**
     *
     * @param $article_id 资讯id
     * @param mixed
     * @return array|null|\PDOStatement|string|\think\Model
     * @author: al
     */
    public function details($article_id){
        $where['id']     =$article_id;
        $basics_service = new BasicsService();
        $where['status'] = 1;
        $info = model('article')->where($where)->field("id,title,hits,FROM_UNIXTIME(create_time,'%Y-%m-%d') as create_time,info,house_id")->find();
        if($info) {
            updateHits($article_id,'article');
            $res  = $basics_service->filterContent($info['info']);
            return $this->success_o($res);
        }else{
            return $this->error_o("未找到资讯");
        }
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
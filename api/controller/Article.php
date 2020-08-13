<?php

namespace app\api\controller;


use app\common\controller\ApiBase;
use app\home\service\IndexServer;
use app\home\service\NewsService;
use app\tools\ApiResult;
use app\tools\Constant;
use think\Controller;
use think\facade\Request;
use think\Log;

class Article extends Controller
{
    use ApiResult;
    protected $news_service;
    public function __construct(NewsService $news_service)
    {
        $this->news_service = $news_service;
    }

    /**
     * cate 分类id
     * page 页码
     * hits 点击次数
     * $keyword 搜索的字段
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function index(){
        $cate_id = input('get.cate/d',0);
        $page    = input('get.page/d',1);
        $hits    = input('get.hits',1);
        $keyword    = input('get.keyword',1);
        $res =$this->news_service->article($cate_id,$hits,$keyword,$page);
        return $this->success_o($res);
    }
    /**
     * 资讯分类
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function type(){
        $cate    = getCate('articleCate','tree');
        if (!empty($cate)){
            return $this->success_o($cate);
        }else{
            return $this->error_o("未找到资讯分类");
        }
    }
    public function details(){
        $article_id = input('get.article_id/d',0);
        if (empty($article_id)){
            return $this->error_o("资讯详情id不能为空");
        }
        $res =$this->news_service->details($article_id);
        return $res;
    }
    public function dealStory(){
        $limit = input('param.limit');
        $info = $this->news_service->dealStorydealStory($limit);
        return $this->success_o($info);
    }


}
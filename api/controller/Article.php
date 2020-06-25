<?php

namespace app\api\controller;


use app\common\controller\ApiBase;
use app\home\service\IndexServer;
use app\home\service\NewsService;
use app\tools\ApiResult;
use app\tools\Constant;
use think\Controller;

class Article extends Controller
{
    use ApiResult;
    protected $news_service;
    public function __construct(NewsService $news_service)
    {
        $this->news_service = $news_service;
    }

    /**
     * 资讯分类
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function article_cate(){
        $cate    = getCate('articleCate','tree');
        if (!empty($cate)){
            return $this->success_o($cate);
        }else{
            return $this->error_o("未找到资讯分类");
        }
    }

    /**
     * cate_id 分类id
     *
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function article(){
        $cate_id = input('get.cate_id');
        $res =$this->news_service->article($cate_id);
        return $this->success_o($res);
    }


}
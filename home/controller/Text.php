<?php


namespace app\home\controller;
use app\api\controller\Banner;
use app\home\service\IndexServer;
use think\Controller;

class Text extends Controller
{
    public function index(){
        $this->index_service = new IndexServer();
        $banner_1 =  $this->index_service->get_home_banner(1);
        $banner_2 =  $this->index_service->get_home_banner(4);
        dd();
        return \Qiniu\json_decode($banner_1,true);

        $a = new Banner();
        dd($a->home_second_search());
    }
}
<?php

namespace app\home\service;



use app\home\controller\Poster;
use app\tools\ApiResult;
use function GuzzleHttp\debug_resource;
use think\console\command\make\Model;
use think\Db;
use think\facade\Cache;
use think\Log;

class EstateServer
{
    use ApiResult;

    public function transaction_record($estate_id){
        $show ="";
        $estate = model("estate")->field("title")->where('id',$estate_id)->find();
        if ($estate && !empty($estate->title)){
            $show =  Db::connect('db2')->name('show')
                ->where([['district',"=",$estate->title],["status","=",7]])
                ->field("title,house_type,face,floor,tot_floor,tot_area,price,f_time")
                ->order("f_time desc")
                ->select();
            foreach ($show as $k=>$v){
                $show[$k]["f_time"] = date("Y-m-d",$v["f_time"]);
                $show[$k]["house_type"]=fa_option_type($show[$k]["house_type"]);
                $show[$k]["face"]=fa_option_type($show[$k]["face"]);
            }
        }
        return $show;

    }

}
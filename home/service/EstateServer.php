<?php

namespace app\home\service;

use app\tools\ApiResult;
use function GuzzleHttp\debug_resource;
use think\Db;
use think\Log;

class EstateServer
{
    use ApiResult;

    /**
     * 小区详情
     * @param $estate_id
     * @param mixed
     * @return string
     * @author: al
     */
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

    /**
     * 本小区房源
     * @param $estate_id
     * @param mixed
     * @return array|\PDOStatement|string|\think\Collection
     * @author: al
     */
    public function estate_house($estate_id){
        $estate = model("second_house")->field('id,title,img,city,room,living_room,acreage,price')
            ->where('estate_id',$estate_id)
            ->whereIn("fcstatus","169,170")
            ->select();
        return $estate;
    }
    public function neighborhood_estate($estate_id){
        $model = model('estate')->field("lng,lat")->where("id",$estate_id)->find();
        if (empty($model)){
            return $this->error_o("未找到当前小区");
        }
        $res = Db::connect('www_fangpaiwang')->query("SELECT title,data,(2 * 6378.137 * ASIN(	SQRT(POW( SIN( PI( ) * ( " . $model->lng. "- fang_estate.lng ) / 360 ), 2 ) + COS( PI( ) * " .$model->lat. " / 180 ) * COS(  fang_estate.lat * PI( ) / 180 ) * POW( SIN( PI( ) * ( " . $model->lat . "- fang_estate.lat ) / 360 ), 2 )))) AS distance FROM `fang_estate`
         where id != ".$estate_id." ORDER BY distance ASC LIMIT 2");
        return $this->success_o($res);
    }




}
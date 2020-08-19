<?php

namespace app\home\service;

use app\home\controller\Poster;
use function GuzzleHttp\debug_resource;
use think\console\command\make\Model;
use think\facade\Cache;
use think\Log;

class ToolsServer
{

    /**
     * 契税计算
     * 税费是跟根据面积(90下1上1.5),买房持有套数(2套统一3%)
     * @param $house_price 房屋价格
     * @param $house_area 房屋面积
     * @param int $house_num 持有房产数
     * @param mixed
     * @return float|int
     * @author: al
     */
    public function deen_tax($house_price,$house_area,$house_num=1){
        if ($house_num == 501){
            $price = $house_price * 0.03;
        }else{
            if ($house_area > 90){
                $price =$house_price * 0.015;
            }else{
                $price =$house_price * 0.01;
            }
        }
        return $price;
    }
    /**
     * 土地出让金计算
     * @param $house_type 房屋类型(公房,商品房,一类/二类 经济使用房)
     * @param $house_price 房屋价格
     * @param int $house_area 面积
     * @param string $is_dis_count 是否优惠
     * @param mixed
     * @return float|int
     * @author: al
     */
    public function land_transfer_fee($house_type,$house_price,$house_area=0,$is_dis_count=""){
        $price =0;
        if($house_type == "103"){//公房
            if($is_dis_count == "900"){ //成本价(土地出让金额=建筑面积×1560×1%)
                $price = $house_area * 15.6;
            }elseif($is_dis_count == "901"){ //优惠价(建筑面积×1560×6%+建筑面积×1560×1%)
                $price =($house_area * 1560 *6) + ($house_area * 1560 * 0.01);
            }
        }elseif($house_type == "102"){//二类经济适用房
            $price = $house_price * 0.03;
        }
        return $price;
    }
    /**
     * 综合地价款
     * @param $house_type 房屋类型
     * @param $house_price 房屋价格
     * @param $house_original_price 房屋原值
     * @param mixed
     * @return float|int
     * @author: al
     */
    public function land_comprehensive($house_type,$house_price,$house_original_price,$buy_time){
        $price =0;
        if($house_type == "101" && $buy_time == "1101"){//一类经济适用房(差额* 70%)
            $house_diff_price =$house_price-$house_original_price;
            $price = $house_diff_price * 0.7;
        }elseif ($house_type == "101" && $buy_time == "1100"){
            $price = $house_price *0.1;
        }
        return $price;
    }
    /**
     * 增值税及附加计算
     * @param $residence_type 住宅类型(普通住宅,非普通住宅)
     * @param $house_price 房屋价格
     * @param int $house_original_price 房屋原值
     * @param int $year 持有年限
     * @param mixed
     * @return float|int
     * @author: al
     */
    public function added_tax($residence_type,$house_price,$house_original_price=0,$location,$year=5){
        $price = 0;
        if ($residence_type == "201"){ //非普住宅
            if (!empty($house_original_price)){ //提供原值
                $house_diff_price =$house_price-$house_original_price;
                if ($location == 1000){
                    $price = $house_diff_price / 1.05 *0.053;
                }elseif ($location == 1001){
                    $price = $house_diff_price / 1.05 *0.0525;
                }

            }else{ //没有原值
                $price = $house_price / 1.05 *0.053;
            }
        }
        return intval($price);
    }
    public function Tdk($type = ""){
        switch ($type)
        {
            case 'index': //首页
                return $this->tdkNav("home/Index");
                break;
            default:
                return "";
        }
    }

    /**
     * nav 表的tdk
     * @param mixed
     * @return array|\PDOStatement|string|\think\Collection
     * @author: al
     */
    public function tdkNav($model_action ="home/Index"){
        $footer_nav = model('nav')->field('title,url,action,seo_title,seo_keys,seo_desc')
            ->where(['status'=>1,'model_action'=>$model_action])
            ->cache('86400')
            ->select();
        return $footer_nav;
    }


}
<?php

namespace app\manage\service;
use QL\QueryList;
use think\Db;
use think\facade\Log;

class SecondHouseService
{
    /**
     * 计算抓到的数据
     * @param $estate_name
     * @param mixed
     * @return mixed
     * @author: al
     */
    public function get_estate_record($estate_name){
        $res =[];
        $data = $this->collect_lianjia_estate_prine($estate_name);
        $rand_min = rand(1,3);
        $res['unit_price'] = 0;
        $res['month'] = 0;
        if (isset($data[0])){
            $price= $sum = 0;
            $count = count($data);
            foreach($data as $item){
                $num = mb_substr($item['num'],'-3','-1');
                if (!empty($num)){
                    $res['num'] = $this->get_string_num($num);
                }
                if (!empty($item['estate_info'])){
                    $res['month'] = round(mb_substr($item['estate_info'],'-17','-15'),2);
                }
                if (!empty($item['price'])){
//                    $price += (int) $item['price'];
                    $price += (int) $item['price'];
                }
                break ;
            }
            $res['num'] +=$rand_min;

//            $res['unit_price'] = round($price / $count,2);
            $res['unit_price'] = $price + ($price*$rand_min/100);
            if (empty($res['unit_price'])){
                $res['num'] =0;
            }
        }
        return $res;
    }

    /**
 * 根据小区名称抓取小区的价格/月份
 * 链家
 * @param $estate_name
 * @param mixed
 * @return \Tightenco\Collect\Support\Collection
 * @author: al
 */
    public function collect_lianjia_estate_prine($estate_name){
//        $res['unit_price'] = 0;
//        $res['month'] = 0;
//        if (!empty($data)){
//            $price= $sum = 0;
//            $rand = rand(1,3);
//            $count = count($data);
//            foreach($data as $item){
//                $num = mb_substr($item['num'],'-3','-1');
//                if (!empty($num)){
//                    $res['num'] = $this->get_string_num($num);
//                }
//                if (!empty($item['estate_info'])){
//                    $res['month'] = round(mb_substr($item['estate_info'],'-17','-15'),2);
//                }
//                if (!empty($item['price'])){
////                    $price += (int) $item['price'];
//                    $price += (int) $item['price'];
//                }
//            }
//            $res['num'] += $rand;
////            $res['unit_price'] = round($price / $count,2);
//            $res['unit_price'] = $price + ($price*$rand/100);
//        }

        $url = 'https://bj.lianjia.com/xiaoqu/rs'.$estate_name.'/';

        $data = QueryList::get($url)
            // 设置采集规则
            ->rules([
                //金额
                'price'=>array('.totalPrice span','text'),
                'estate_info'=>array('.xiaoquListItemPrice','text'),
                'num'=>array('.houseInfo a','text'),
            ])
            ->query()->getData();
        return $data;
    }
    /**
     * 根据小区名称抓取小区的价格/月份
     * 我爱我家
     * @param $estate_name
     * @param mixed
     * @return \Tightenco\Collect\Support\Collection
     * @author: al
     */
    public function collect_wawj_estate_prine($estate_name){
        $url ='https://bj.5i5j.com/xiaoqu/_'.$estate_name.'?zn='.$estate_name;


        $data = QueryList::get($url)
            // 设置采集规则
            ->rules([
                //金额
                'price'=>array('.redC strong','text'),
                'num'=>array('.xqzs span a','text'),
            ])
            ->query()->getData();
        dd($data);
        return $data;
    }

    /**
     * 获取字符串中的数字
     * @param $str
     * @param mixed
     * @return string
     * @author: al
     */
    public function get_string_num($str){
        $result=0;
        for($i=0;$i<strlen($str);$i++){
            if(is_numeric($str[$i])){
                $result.=$str[$i];
            }
        }
        return $result;
    }
    /**
     * 根据房拍网房源信息 获取法拍网房源id
     * @param $id
     * @param string $title
     * @return al
     * @throws \think\Exception
     */
    public function get_fa_show_id($id,$title=""){
        $fa_show_new = Db::connect('db2')->field('id')->name('show')->where('fang_second_house_id',$id)->find();
        //判断是手动加的数据还是自动加的
        if(!empty($fa_show_new['id'])){ //自动
            $fa_show_id = $fa_show_new['id'];
        }else{ //手动
            $fa_show = Db::connect('db2')->field('id')->name('show')->where('title',$title)->find();
            $fa_show_id =$fa_show['id'];
        }
        return $fa_show_id;
    }

    /**
     * 计算房产捡漏指数
     * @param string $marketprices 房产价格
     * @param string $qipaiprice 起拍价格
     * @param mixed
     * @return int|string
     * @author: al
     */
    public function xingji($marketprices="",$qipaiprice=""){
        $marketprice =1;
        if (!empty($marketprices) && !empty($qipaiprice)){
            $jlzs=round($marketprices/$qipaiprice,1);
            //默认一星
            if($jlzs<'1.1'){
                $marketprice='0';
            }
            if(($jlzs>='1.1') && ($jlzs<='2')){
                $marketprice='1';
            }
            if(($jlzs>='1.3') && ($jlzs<='1.4')){
                $marketprice='2';
            }
            if(($jlzs>='1.5') && ($jlzs<='1.6')){
                $marketprice='3';
            }
            if(($jlzs>='1.7') && ($jlzs<='1.8')){
                $marketprice='4';
            }
            if($jlzs>'1.8'){
                $marketprice='5';
            }
        }
        return $marketprice;
    }
    /**
     * 处理房源基本信息
     * @param $info
     * @param mixed
     * @return string
     * @author: al
     */
    public function basic_info($info){
        $res =  "";
        $arr = [];
        foreach (explode('|',$info) as $k=>$value){
            $arr[] = mb_substr(trim($value),5);
        }
        $attribute = $arr[3] ?? "";
        $years = $arr[4] ?? "";
        $structure = $arr[8] ?? "";
        $renovation = $arr[10] ?? "";
        $heating = $arr[11] ?? "";
        $parking = $arr[12] ?? "";
        $property = $arr[13] ?? "";
        $matching = $arr[16] ?? "";
        $traffic = $arr[17] ?? "";
        $res = $attribute.'|'.$years.'|'.$structure.'|'.$renovation.'|'.$heating.'|'.$parking.'|'.$property.'|'.$matching.'|'.$traffic;
        return $res;
    }
}
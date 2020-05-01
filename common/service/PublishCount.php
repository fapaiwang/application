<?php

namespace app\common\service;


class PublishCount
{
    public static function count($user_id = 0)
    {
        $time = time();
        $where = [
          ['broker_id','eq',$user_id],
          ['timeout','gt',$time]
        ];
        $today_start = strtotime(date("Y-m-d 00:00:00"));
        $today_end   = strtotime(date("Y-m-d 23:59:59"));
        $field_second = "count(*) as second_total,count(case when create_time > {$today_start} and create_time<= {$today_end} then create_time end) as second_total_day,0 rental_total,0 rental_total_day,0 office_total,0 office_total_day,";
        $field_second .= "0 office_rental_total,0 office_rental_total_day,0 shops_total,0 shops_total_day,0 shops_rental_total,0 shops_rental_total_day";

        $field_rental = "0 second_total,0 second_total_day,count(*) as rental_total,count(case when create_time > {$today_start} and create_time<= {$today_end} then create_time end) as rental_total_day,0 office_total,0 office_total_day,";
        $field_rental .= "0 office_rental_total,0 office_rental_total_day,0 shops_total,0 shops_total_day,0 shops_rental_total,0 shops_rental_total_day";

        $field_office = "0 second_total,0 second_total_day,0 rental_total,0 rental_total_day,count(*) as office_total,count(case when create_time > {$today_start} and create_time<= {$today_end} then create_time end) as office_total_day,";
        $field_office .= "0 office_rental_total,0 office_rental_total_day,0 shops_total,0 shops_total_day,0 shops_rental_total,0 shops_rental_total_day";

        $field_office_rental = "0 second_total,0 second_total_day,0 rental_total,0 rental_total_day,0 office_total,0 office_total_day,";
        $field_office_rental .= "count(*) as office_rental_total,count(case when create_time > {$today_start} and create_time<= {$today_end} then create_time end) as office_rental_total_day,";
        $field_office_rental .= "0 shops_total,0 shops_total_day,0 shops_rental_total,0 shops_rental_total_day";

        $field_shops = "0 second_total,0 second_total_day,0 rental_total,0 rental_total_day,0 office_total,0 office_total_day,0 office_rental_total,0 office_rental_total_day,";
        $field_shops .= "count(*) as shops_total,count(case when create_time > {$today_start} and create_time<= {$today_end} then create_time end) as shops_total_day,0 shops_rental_total,0 shops_rental_total_day";

        $field_shops_rental = "0 second_total,0 second_total_day,0 rental_total,0 rental_total_day,0 office_total,0 office_total_day,0 office_rental_total,0 office_rental_total_day,0 shops_total,0 shops_total_day,";
        $field_shops_rental .= "count(*) as shops_rental_total,count(case when create_time > {$today_start} and create_time<= {$today_end} then create_time end) as shops_rental_total_day";

        $field ="sum(second_total) as second_total,sum(second_total_day) as second_total_day,sum(rental_total) as rental_total,sum(rental_total_day) as rental_total_day";
        $field .= ",sum(office_total) as office_total,sum(office_total_day) as office_total_day";
        $field .= ",sum(office_rental_total) as office_rental_total,sum(office_rental_total_day) as office_rental_total_day";
        $field .= ",sum(shops_total) as shops_total,sum(shops_total_day) as shops_total_day";
        $field .= ",sum(shops_rental_total) as shops_rental_total,sum(shops_rental_total_day) as shops_rental_total_day";
        //统计 二手房总发布量 和每日发布量
        $secondSql = model('second_house')->where($where)->field($field_second)->buildSql();
        //统计出租房每日发布量和总发布量
        $rentalSql = model('rental')->where($where)->field($field_rental)->buildSql();
        //统计写字楼出售每日发布量和总发布量
        $officeSql = model('office')->where($where)->field($field_office)->buildSql();
        //统计写字楼出租每日发布量和总发布量
        $officeRentalSql = model('office_rental')->where($where)->field($field_office_rental)->buildSql();
        //统计商铺出售每日发布量和总发布量
        $shopsSql        = model('shops')->where($where)->field($field_shops)->buildSql();
        //统计商铺出租每日发布量和总发布量
        $shopsRentSql = model('shops_rental')->where($where)->field($field_shops_rental)->buildSql();

        //分别统计所有发布量
        $lists = \think\Db::query("select {$field} from ({$secondSql} union {$rentalSql} union {$officeSql} union {$officeRentalSql} union {$shopsSql} union {$shopsRentSql}) t");
        $data = $lists[0];
        $data['total'] = $data['second_total']+$data['rental_total']+$data['office_total']+$data['office_rental_total']+$data['shops_total']+$data['shops_rental_total'];
        $data['total_day'] = $data['second_total_day']+$data['rental_total_day']+$data['office_total_day']+$data['office_rental_total_day']+$data['shops_total_day']+$data['shops_rental_total_day'];

        return $data;
    }

    /**
     * @param $user_id
     * @param $model
     * @return mixed
     * 验证余额是否充足
     */
    public static function check($user_id,$model)
    {
        $setting = getUserCate();
        $setting = $setting[$model]['data'];//用户设置
        $free_day   = $setting['free_day_send'];//每天可发布数量
        $free_total = $setting['free_all_send'];//共可以发布数据量
        $return['code'] = 0;
        //不限制条数 直接返回
        if($free_day == 0 && $free_total == 0)
        {
            $return['code'] = 1;
            return $return;
        }
        //统计发布量
        $count = self::count($user_id);
        if($free_day == 0 && $free_total > 0){//限制总发布量
            if($free_total <= $count['total'] ){
                $return['msg'] = '总发布量已超出限制!';
            }else{
                $return['code'] = 1;
            }
        }elseif($free_day > 0 && $free_total == 0){//限制每天发布量
            if($free_day <= $count['total_day']){
                $return['msg'] = '每天可发布量已超出限制!';
            }else{
                $return['code'] = 1;
            }
        }else{//都限制
            if($free_total <= $count['total'] ){
                $return['msg'] = '总发布量已超出限制!';
            }elseif($free_day <= $count['total_day']){
                $return['msg'] = '每天可发布量已超出限制!';
            }else{
                $return['code'] = 1;
            }
        }
        //如果超除限制 并且设置了购买金额则直接从账户中扣除
        if($return['code'] == 0)
        {
            $msg = $setting['price'] > 0 ? "超出按{$setting['price']}元/条收取" : '';
            $return['msg'] = $return['msg'].$msg;
            if($setting['price'] > 0)
            {
                $result = Account::optionMoney($user_id,['price'=>$setting['price'],'memo'=>'发布房源扣除金额'],-1);
                if($result['code'] == 0)
                {
                    $return['code'] = 0;
                    $return['msg'] = $result['msg'];
                }else{
                    $return['code'] = 1;
                }
            }elseif($setting['price'] == 0){
                $return['code'] = 1;
                $return['msg']  = '';
            }
        }
        return $return;
    }
}
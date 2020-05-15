<?php

namespace app;



use app\home\controller\Poster;
use function GuzzleHttp\debug_resource;
use think\console\command\make\Model;
use think\facade\Cache;
use think\Log;

class server
{
    /**
     * 房源详情
     * @param $second_house_id 房源id
     * @param mixed
     * @return array|null|\PDOStatement|string|\think\Model
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function second_model($second_house_id){
        $where['h.id']     = $second_house_id;
        $where['h.status'] = 1;
        $second_house_field  ="h.title,h.bianhao,h.qipai,h.types,h.price,h.marketprice,h.baozheng,h.toilet,h.floor,h.total_floor,
            d.file,d.house_id,d.info,
            h.orientations,h.acreage,h.contacts,h.estate_id,h.id,h.broker_id,h.city,h.jieduan,h.bianetime,h.kptime,h.types,
            h.bmrs,h.weiguan,h.img,h.basic_info,h.oneetime,h.twoetime,h.oneprice,h.twoprice,h.elevator,h.auction_attr,h.enforcement,
            h.land_purpose,h.land_certificate,h.property_no,h.house_purpse,h.management,h.lease,h.mortgage,h.sequestration,
            h.vacate,h.is_commission,h.is_school,h.is_metro,h.xiaci,h.qianfei,h.lng,h.lat,h.estate_name,h.rec_position,h.fcstatus
            ,h.fcstatus,h.is_free,h.house_type";
        $obj  = model('second_house');
        $join = [['second_house_data d','h.id=d.house_id']];
//            todo  缓存
//            ->cache('second_house_'.$second_house_id,3600)
        $info = $obj->alias('h')
            ->field($second_house_field)
            ->join($join)->where($where)->find();
        //单价
        $info['junjia']=sprintf("%.2f",intval($info['qipai'])/intval($info['acreage'])*10000);
        //差价
        $info['chajia']=intval($info['price'])-intval($info['qipai']);
        //类型
        $xsname =getLinkMenuCache(26)[$info['types']] ??"";
        $info['xsname']=$xsname['name'] ?? "";
        //起拍价格
        $info['qp_price'] = substr($info['price'],0,-10);
        //成交价格
        $info['cjprice']=sprintf("%.2f",$info['cjprice']);
        $info['file'] = json_decode($info['file'],true);
        //基本信息
        if (!empty($info['basic_info'])){
            $info['basic_info']=explode('|',$info['basic_info']);
        }
        //拍卖阶段
        $info['jieduan'] =getLinkMenuName(25, $info['jieduan']);
        $info['jieduan_num'] =$info['jieduan'];
        return $info;
    }

    /**
     * 房源是否关注
     * @param $estate_id
     * @param $user_id
     * @param mixed
     * @return float|string
     * @author: al
     */
    function is_guanzhu($estate_id,$user_id){
        $follow   = model('follow');
        $guanzhu = $follow->where('house_id',$estate_id)->where('user_id',$user_id)->where('model','estate')->count();
        return $guanzhu;
    }
    /**
     * 小区信息
     * @param $estate_id 小区id
     * @param mixed
     * @return array|null|\PDOStatement|string|\think\Model
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function estate($estate_id){
        $estate =model('estate')
            ->field('id,title,years,address,data,area_name,img')
            ->where('id',$estate_id)
            ->cache('estate_'.$estate_id,84001)
            ->find();
//        dd($estate);
        return $estate;
    }

    /**
     *  经纪人信息
     * @param $second_house_id 房源id
     * @param $broker_id 经纪人id
     * @param mixed
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function user($second_house_id,$broker_id){
        $user_info = model('user')->field('lxtel_zhuan,kflj')->where('id',$broker_id)
            ->cache('user_info_common_'.$second_house_id,84000)->find();
        return $user_info;
    }
    public function user_info($second_house_id,$broker_id){
        $user = model('user_info')->field('history_complate')->where('user_id',$broker_id)
            ->cache('user_common_'.$second_house_id,84000)->find();
        return $user;
    }

}
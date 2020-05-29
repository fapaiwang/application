<?php

namespace app\home\service;



use app\home\controller\Poster;
use think\console\command\make\Model;
use think\facade\Cache;
use think\Log;

class IndexServer
{
    /**
     * banner 换成对象
     * @param int $space_id
     * @param mixed
     * @return array
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_home_banner_arr($space_id=1,$limit=0){
        $banner_json= $this->get_home_banner($space_id);
        $arr =[];
        if ($banner_json){
            foreach ($banner_json as $k=>$v){
                if ($k < $limit){
                    $arr[] =$v['setting'];
                }
            }
        }
        return $arr;
    }
    /**
     * 获取首页 banner
     * @param int $space_id
     * @param mixed
     * @return json
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     */
    public function get_home_banner($space_id=1,$limit=0){
        $cache_name = 'poster_img'.$space_id;
        $banner = Cache::get($cache_name);
        if (!$banner){
            $cache_name = 'poster_img'.$space_id;
            $poster_space = model('poster')->field('name,setting')->where([['spaceid','=',$space_id],['startdate','<',time()],['enddate','>',time()]])->select();
            \think\facade\Cache::set($cache_name,$poster_space);
        }
        return $banner;
    }
    /**
     * 查询统计数据
     * @param mixed
     * @return array
     * @author: al
     */
    public function get_statistics_num(){
        $objs   = model('second_house');
        $res =[];
        //正在进行
        $res['underway_num'] = $objs->where(['fcstatus'=>169,'status'=>1])->cache(3600)->count();
        //即将开始
        $res['soon_num'] = $objs->where('fcstatus',170)->where('status',1)->cache(3600)->count();
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $start_time = date('Y-m-d H:i:s',$beginToday);
        $end_time = date('Y-m-d H:i:s',$endToday);
        //今日新增
        $res['add_num'] =$objs->where('fabutimes','between',[$beginToday,$endToday])->where('status',1)->cache(3600)->count();
        //今日成交
        $res['deal_num'] =$objs->where('endtime','between',[$start_time,$end_time])->where(['fcstatus'=>175,'status'=>1])->cache(3600)->count();
        return $res;
    }

    /**
     * 获取推荐房源(6套)
     * @param mixed
     * @return array|null|\PDOStatement|string|\think\Model
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_recommend_house($limit = 6){//
        $objs   = model('second_house');
        $second_house   = $objs->field('id,title,room,qipai,img,living_room,orientations,acreage,price,create_time,toilet,kptime')
            ->where([['status','=',1],['toilet','<>',0],['rec_position','=',1],['fcstatus','=',170]])
            ->order('rec_position desc')->limit($limit)
            ->cache("second_house_recommend_house".$limit,1800)
            ->select();
        return $second_house;
    }
    
    /**
     * @description 自由购
     * @param int $limit 需要查询几条，默认6条
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @auther xiaobin
     */
    public function get_restrict_house($limit = 6){
        $objs   = model('second_house');
        $second_house   = $objs->field('title,room,qipai,img,living_room,orientations,acreage,create_time,toilet')
            ->where([['status','=',1],['toilet','<>',0],['rec_position','=',1],['fcstatus','=',170],["is_free", "=", 1]])
            ->order('rec_position desc')->limit($limit)
            ->cache("second_house_restrict_house".$limit,3600)
            ->select();
        return $second_house;
    }
    
    /**
     * 获取小区房源前10的房源
     * @param mixed
     * @return array|\PDOStatement|string|\think\Collection
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_quality_estate($limit=10){
        $field   = 'id,title,img,price,area_name';
        //统计二手房和出租房数量 where条件里需要替换estate_id为estate表每条记录的id, 不能用字符(会被转成 0)所以用9999代替替换
        $second_sql = model('second_house')->where('estate_id','9999')->where('status',1)->field('count(id) as second_total')->buildSql();
        $field .= ','.$second_sql.' as second_total';
        $field  = str_replace('9999','e.id',$field);
        $lists      = model('estate')
            ->alias('e')
            ->field($field)
            ->cache("second_house_quality_estate_".$limit,'86400')
            ->order('second_total desc')
            ->limit($limit)->select();
        return $lists;
    }

    /**
     * 资讯展示图
     * @param mixed
     * @return mixed
     * @author: al
     * //todo 缓存关闭
     */
    public function article_show($cate_id,$limit){
        $cache_name = 'article_show'.$cate_id.$limit;
        $article =  model('article')
            ->field('id,title,img,create_time')
            ->where([['status','=',1],['cate_id','=',$cate_id]])
            ->order("ordid desc, create_time desc")
            ->limit($limit)
            ->cache($cache_name,3600);
        if ($limit == 1){
            return $article->find();
        }else{
            return $article->select();
        }
    }

    /**
     * 小区添加区域名称
     * @param mixed
     * @author: al
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function estate_add_area_name(){
       $estate = model('estate')->field('id,city')->where('area_name',null)->select();
       foreach ($estate as $v){
           $city = model('city')->field('id,spid')->where('id',$v->city)->find();
           if (!empty($city->spid)){

               $pid = substr($city->spid,3,2);
               if (!$pid){
                   $pid = $city->id;
               }
               $area = model('city')->field('id,name')->where('id',$pid)->find();
               $res =model('estate')->where('id',$v->id)->update(['area_name'=>$area->name]);
               print_r($res).'--';
               print_r($v->id).'\r\n';
           }
       }
    }
    /**
     * 获取城市区域
     * @param mixed
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function  get_city_info(){
        $arr = [];
        $city = model('city')->field('id,name')->where('pid',39)->select();
        foreach ($city as $v){
            $arr[$v->id] = $v->name;
        }
        cache('city_info',$arr);
    }


}
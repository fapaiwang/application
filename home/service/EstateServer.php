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
        $res = Db::connect('www_fangpaiwang')->query("SELECT id,title,price,img,(2 * 6378.137 * ASIN(	SQRT(POW( SIN( PI( ) * ( " . $model->lng. "- fang_estate.lng ) / 360 ), 2 ) + COS( PI( ) * " .$model->lat. " / 180 ) * COS(  fang_estate.lat * PI( ) / 180 ) * POW( SIN( PI( ) * ( " . $model->lat . "- fang_estate.lat ) / 360 ), 2 )))) AS distance FROM `fang_estate`
         where id != ".$estate_id." ORDER BY distance ASC LIMIT 2");
        if ($res){
            foreach ($res as $k=>$v){
                $res[$k]["num"] = $this->estate_second_num($v["title"]);
            }
        }
        return $this->success_o($res);
    }

    /**
     * 小区房源总套数
     * @param $estate
     * @param mixed
     * @return float|string
     * @author: al
     */
    public function estate_second_num($estate){
        $estate_house_num ='estate_house_num_'.$estate;
        $info = cache($estate_house_num);
        if(!$info){
            $info = model('second_house')->where('estate_name',$estate)->count();
            cache($estate_house_num,$info,3600);
        }
        return $info;
    }
    public function estate_list($area,$price,$sort,$keyword,$limit=30){
        $info = $this->search($area,$price,$sort);
        $where = $info["data"];
        $field   = 'id,title,img,pano_url,house_type,years,address,price,complate_num';
        //统计二手房和出租房数量 where条件里需要替换estate_id为estate表每条记录的id, 不能用字符(会被转成 0)所以用9999代替替换
        $second_sql = model('second_house')->where('estate_id','9999')->where('status',1)->field('count(id) as second_total')->buildSql();
        $field .= ','.$second_sql.' as second_total';
        $field  = str_replace('9999','e.id',$field);
        $lists      = model('estate')
            ->alias('e')
            ->where($where)
            ->field($field)
            ->order($this->getSort($sort))
            ->paginate($limit,false,['query'=>['keyword'=>$keyword]]);

        return ["search"=>$info["search"],"lists"=>$lists];

    }
    public function search($area,$price,$sort){
        $param['area']       = $area;
        $param['price']      = $price;
        $param['sort']       = $sort;//排序
        $param['rading']     = 0;
        $data['status']      = 1;
        $param['area'] == 0 && $param['area'] = 39;
        $keyword = input('get.keyword');
        if($keyword){
            $param['keyword'] = $keyword;
            $data[] = ['title','like','%'.$keyword.'%'];
        }
        if(!empty($param['area'])){
            $data[] = ['city','in',getCityChild($param['area'])];
            $rading = getRadingByAreaId($param['area']);
            //读取商圈
            $param['rading'] = 0;
            if($rading && array_key_exists($param['area'],$rading))
            {
                $param['rading']  = $param['area'];
                $param['area']    = $rading[$param['area']]['pid'];
            }
        }
        if(!empty($param['price'])){
            $data[] = getEstatePrice($param['price']);
        }
        if(!empty($param['type'])){
            $data['house_type'] = $param['type'];
        }
        $arr["search"] =$param;
        $arr["data"] =$data;
        return $arr;
    }

    /**
     * @param $sort
     * @return array
     * 排序
     */
    private function getSort($sort)
    {
        switch($sort)
        {
            case 0:
                $order = ['second_total'=>'desc','ordid'=>'asc','id'=>'desc'];
                break;
            case 1:
                $order = ['price'=>'asc','id'=>'desc'];
                break;
            case 2:
                $order = ['price'=>'desc','id'=>'desc'];
                break;
            case 3:
                $order = ['hits'=>'desc','id'=>'desc'];
                break;
            case 4:
                $order = ['hits'=>'asc','id'=>'desc'];
                break;
            default:
                $order = ['ordid'=>'asc','id'=>'desc'];
                break;
        }
        return $order;
    }

}
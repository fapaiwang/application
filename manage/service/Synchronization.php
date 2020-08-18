<?php

namespace app\manage\service;
use think\Db;
use think\facade\Log;

class Synchronization
{
    /**
     * 根据房拍网城市id 获取法拍网城市名称
     * @param string $model
     * @return false|\PDOStatement|string|\think\Collection 北京 东城 洋桥
     * author al
     */
    public function get_city($city="")
    {

        if ($city){
            $fang_street = db('city')->find($city);
            $fang_spid = explode('|',$fang_street['spid']);
            $fang_city = db('city')->find($fang_spid[0] ?? "");
            if ($fang_street['id'] > 57){
                $fang_area = db('city')->find($fang_spid[1] ?? "");
            }else{
                $fang_area = db('city')->find($fang_street['id'] ?? "");
            }

            if (empty($fang_area)){
                $fang_area = $fang_street;
            }
//            $fang_street['id']
            //用房拍网的地址 对比
            $fa_city=Db::connect('db2')->name('region')->where('name',$fang_city['name'])->find();
            $fa_area=Db::connect('db2')->name('region')->where('name',mb_substr($fang_area['name'],0,-1))->find();
            $fa_street=Db::connect('db2')->name('region')->where('name',$fang_street['name'])->find();

            $res =$fa_city['name'].' '.$fa_area['name'].' '.$fa_street['name'];
        }
        return $res;
    }

    /**
     * 根据房拍网小区名称或id 获取法拍网小区id
     * @param $data
     * @return mixed
     * @throws \think\Exception
     * @author al
     */
    public function get_community_id($data){
        $fa_community  = Db::connect('db2')->name('community')->field('id')->where('fang_estate_id',$data['id'])->find();
        if (empty($fa_community['id'])){
            $fa_community=Db::connect('db2')->name('community')->field('id')->where('title',$data['title'])->find();
        }
        return $fa_community['id'];
    }

    public function get_show_id($data){
        $fa_show  = Db::connect('db2')->name('show')->field('id')->where('fang_second_house_id',$data['id'])->find();
        if (empty($fa_show['id'])){
            $fa_show=Db::connect('db2')->name('show')->field('id')->where('title',$data['title'])->find();
        }
        return $fa_show['id'];
    }
    /**
     * 添加到法拍网community 拼接数据
     * @param $data
     * @return array
     * @author al
     */
    public function fa_estate_arr($data,$id=""){
        $arr = [
            'title'=>$data['title'],
            'simg'=>$data['img'],
            'str_3'=>$data['years'],
            'str_4'=>$data['data']['area_build'] ?? "",
            'str_5'=>$this->get_city($data['city']),
            'str_6'=>$data['data']['plan_number'] ??"",
            'str_7'=>$data['data']['volume_ratio'] ??"",
            'str_8'=>$data['data']['parking_space'] ?? "",
            'str_9'=>$data['data']['property_fee'] ?? "",
            'str_10'=>$data['data']['volume_ratio'] ?? "",
            'str_11'=>$data['address'] ?? "",
            'str_12'=>$data['data']['property_company'] ??"",
            'str_13'=>$data['data']['developer'] ?? "",
            'fang_estate_id'=>$id,
            'addtime'=>time(),
        ];
        //20200818改版，图片全部存储在七牛云，本地不在同步图片
        /*if ($data['img']){
            $this->fa_mv_img($data['img'],24);
        }*/
        return $arr;
    }

    /**
     * 图片移动 固定移动到法拍网
     * @param $img_url  路径 如:"/uploads/estate/20190419/a7d9e664e440e53ad2dbc2949d97621e.jpg"
     * @param $num 数字 如 10 只为截取真实地址 如 "/uploads/estate/20190419/"
     * @return al
     */

    public function fa_mv_img($img_url,$num){
        //检测是否有图片地址
        $res = "";
        $cp_img_url = substr($img_url,0,$num);
        $fa_img_url = '../../www.fapaiwang.cn'.$cp_img_url;
        $this->createDir($fa_img_url);
        $img_url =substr($img_url,1);
        if(file_exists($img_url)){
            $res  = copy($img_url,'../../www.fapaiwang.cn/'.$img_url);
        }
        return $res;
    }

    /**
     *  建立文件夹
     * @param $aimUrl
     * @return al
     */
    function createDir($aimUrl) {
        $aimUrl = str_replace('', '/', $aimUrl);
        $aimDir = '';
        $arr = explode('/', $aimUrl);
        $result = true;
        foreach ($arr as $str) {
            $aimDir .= $str . '/';
            if (!file_exists($aimDir)) {
                $result = mkdir($aimDir,0777,true);
            }
        }
        return $result;
    }
    function synchronization_estate_all($num){
        $estate= model('estate')->where([['id','>',$num],['id','<',$num+100]])->select();
        $num = 0;

        foreach ($estate as $k=>$v){
            $get_community_id = $this->get_community_id($v);

            if(empty($get_community_id)){ //判断房拍网是否有小区
                $num = $num +1;
                $arr =  $this->fa_estate_arr($v,$v['id']);
                Log::write('---同步的小区有---',$k.'--'.$v['title'].json_encode($arr));
                model('community')->insert($arr);
            }
        }
        return "本次共同步".$num.'次';
    }




}
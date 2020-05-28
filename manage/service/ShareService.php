<?php

namespace app\manage\service;
use think\Db;
use think\facade\Log;

class ShareService
{
    /**
     * 根据用户名称或id
     * 获取用户信息(名称/电话/二维码);
     * @param $data
     * @param mixed
     * @return mixed
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_user($data){
        $res['share_img']=  '/share/gongsi.png';
        $res['name'] ="颜总";
        $res['mobile']="";
        if (!empty($data['name'])){
            $user =  model('user')->field('share_img,user_name,mobile')->where([['model','=',4],['id','=',$data['name']]])->find();
            if(empty($user)){
                $user =  model('user')->field('share_img,user_name,mobile')->where([['model','=',4],['nick_name','=',$data['name']]])->find();
            }
            $res['name']= $user['user_name'] ?? "颜总";
            $res['share_img']= $user['share_img'] ?? '/share/gongsi.png';
            $res['mobile']= $user['mobile'] ?? '';
        }
        $res['date'] = date('Y-m-d');
        return $res;
    }
    /**
     * 拼接lists中的数据
     * @param $lists
     * @param mixed
     * @return mixed
     * @author: al
     */
    public function im_list($lists){
        foreach ($lists as $k=>$v){
            $lists[$k]['s_price'] = $v['price'];
            $lists[$k]['cprice'] =round( $lists[$k]['s_price'] - $lists[$k]['cjprice'],2);
            $lists[$k]['types'] = $this->get_housing_type($lists[$k]['types']);
        }
        return $lists;

    }

    /**
     * @param $lists
     * @param mixed
     * @return mixed
     * @author: al
     */
    public function im_business_list($lists){
        foreach ($lists as $k=>$v){
            $lists[$k]['s_price'] = substr($v['price'],0,-10);
            $lists[$k]['types'] = $this->get_housing_type($lists[$k]['types']);
            if (empty($v['tags'])){
                unset($lists[$k]);
            }
            if (!empty($lists[$k])){
                $business = explode(',',$lists[$k]['tags']);
                if ($business[0] != 184){
                    unset($lists[$k]);
                }
            }
        }
        return $lists;

    }
    //     $time_start = date('Y-m-d', strtotime('-2 monday', time())).' '.'00:00:00';
    //        $time_end = date('Y-m-d', strtotime('-1 sunday', time())).' '.'23:59:59';

    function get_housing_type($types){
        $res ="";
        switch ($types) {
            case 18331:
                $res ="别墅";
                break;
            case 18332:
                $res="平房.四合院";
                break;
            case 18333:
                $res="商办";
                break;
            case 18334:
                $res="住宅";
                break;
            case 18336:
                $res ="写字楼";
                break;
            case 18358:
                $res="厂房";
                break;
            case 18359:
                $res="土地";
                break;
            case 18361:
                $res="酒店";
                break;
            case 18363:
                $res="公寓";
                break;
        }
        return $res;
    }

}
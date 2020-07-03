<?php

namespace app\mobile\controller;
use app\common\controller\MobileBase;
use think\facade\Log;

class Jrcj extends MobileBase
{
    public function index()
    {
//        $lists = model('second_house')->field('id,title,kptime,bianetime,fcstatus,jieduan,house_type')
//            ->whereTime('kptime','>','-2 day' )
//            ->where('fcstatus','in','169,170,171')->select();
        $stime =date('Y-m-d',strtotime('-2 day'));
        $etime =date('Y-m-d',strtotime('+2 day'));
        $lists = model('second_house')->field('id,title,kptime,bianetime,fcstatus,jieduan,house_type')
            ->where('kptime','>',$stime)
            ->where('kptime','<',$etime)
            ->where('fcstatus','in','169,170')
            ->select();
        Log::write("开始进行-每日数据更新");
         foreach ($lists as $key => $value) {
            $lists[$key]['kptimes']=strtotime($lists[$key]['kptime']);
            $lists[$key]['bianetimes']=strtotime($lists[$key]['bianetime']); //变卖截止时间
            $sTime=time();
            // print_r($lists[$key]['fcstatus']);
            if($lists[$key]['fcstatus']==169 || $lists[$key]['fcstatus']==170 || $lists[$key]['fcstatus']==171){
                $ctimes=$sTime-$lists[$key]['kptimes'];
                // print_r($lists[$key]['jieduan']);
                if($lists[$key]['jieduan']==163){ //变卖
                    $ctimess=$sTime-$lists[$key]['bianetimes'];
                    if($ctimes>=0){ //当前时间超过开拍时间 当前时间-开拍时间
                        if($ctimess >= 0){ //当前时间超过变卖截止时间
                            model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>171]);//已结束171
                        // print_r($ctimes);echo "aaa";
                        }else{
                            if ($lists[$key]['fcstatus'] != 169){
                                model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>169]);//正在进行169
                            }
                        }
                    }else{
                        if ( $lists[$key]['fcstatus'] != 170){
                            model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>170]);//即将开始170
                        }
                    }
                
                }else{
                    if($ctimes >= 0 && $ctimes < 3600*24){ //小于当前时间 并且未超过1天
                            model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>169]);//正在进行
                            if($lists[$key]['house_type'] == 46){
                                model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>171]);//已结束171
                            }
                        }elseif($ctimes >= (3600*24)){//超过1天的还未已结束
                            Log::write("开始进行-过期数据");
                                if ( $lists[$key]['fcstatus'] != 171){
                                    $aa = model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>171]);//已结束171
                                    Log::write("已执行的id-".$lists[$key]['id']);
                                    Log::write("执行状态".$aa);
                                }
                        }else{
                            if ( $lists[$key]['fcstatus'] != 170){
                                model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>170]);//即将开始170
                            }
                        }
                }
            }
        }
        
        return $this->fetch();
    }








}
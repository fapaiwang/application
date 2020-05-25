<?php

namespace app\api\controller;

use app\home\service\IndexServer;
use app\home\service\SecondServer;
use app\server;
use app\tools\ApiResult;
use think\Controller;

class SecondHouse extends Controller
{
    use ApiResult;
    protected $Second_Server;

    public function __construct(SecondServer $Second_Server)
    {
        $this->Second_Server = $Second_Server;
    }

    /**
     * 拍卖成交记录
     * 重新极端单价
     * @param mixed
     * @author: al
     */
   public function transaction_record(){
       $mode = model('transaction_record')->select();
       $price = $acreage = 0;
       foreach ($mode as $v){
           if (!empty($v->acreage) && !empty($v->price)){
               $price = mb_substr($v->price,0,-1);
               if (is_numeric($price)){
                   $acreage =  $price * 10000 / $v->acreage;
                   model('transaction_record')->where('id', $v->id)->update(['cjprice' => intval($acreage)]);
               }

           }
           print_r($v->id);
           print_r("<pre>");
       }
   }
}
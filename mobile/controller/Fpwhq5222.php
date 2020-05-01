<?php





namespace app\mobile\controller;

use app\common\controller\MobileBase;

class Fpwhq extends MobileBase

{

    private $pageSize = 10;

    private $mod      = 'fpwhq';



    /**

     * @return mixed

     * 楼盘列表

     */
    public function index()

    {

$start = date('Y-m-01 00:00:00');
$end = date('Y-m-d H:i:s');

$aaa=strtotime($start);
$bbb=strtotime($end);
// print_r($aaa);
// print_r($bbb);
$yp[]=['create_time','egt',$aaa];
$yp[]=['create_time','elt',$bbb];
        //状态：正常 一拍
$yp[] = ['status','eq',1];
$yp[] = ['jieduan','eq',161];
$yipai = model('second_house')->where($yp)->count();

$ep[]=['create_time','egt',$aaa];
$ep[]=['create_time','elt',$bbb];
//状态：正常 二拍
$ep[] = ['status','eq',1];
$ep[] = ['jieduan','eq',162];
$erpai = model('second_house')->where($ep)->count();

$bm[]=['create_time','egt',$aaa];
$bm[]=['create_time','elt',$bbb];
//状态：正常 二拍变卖
$bm[] = ['status','eq',1];
$bm[] = ['jieduan','eq',163];
$bianmai = model('second_house')->where($bm)->count();

//今日新增

$rq = strtotime(date('Y-m-d 00:00:00'));
$jrxz[]=['create_time','egt',$rq];

$jrxz[] = ['status','eq',1];
$jrxz = model('second_house')->where($jrxz)->count();
//今日成交
$jrcj[]=['create_time','egt',$rq];

$jrcj[] = ['status','eq',1];
$jrcj[] = ['fcstatus','eq',175];
$jrcj = model('second_house')->where($jrcj)->count();


//新增
$xz[]=['create_time','egt',$aaa];
$xz[]=['create_time','elt',$bbb];

$xz[] = ['status','eq',1];
$xinzeng = model('second_house')->where($xz)->count();

//成交
$cj[]=['create_time','egt',$aaa];
$cj[]=['create_time','elt',$bbb];

$cj[] = ['status','eq',1];
$cj[] = ['fcstatus','eq',175];
$chengjiao = model('second_house')->where($cj)->count();



// $xinzeng = model('second_house')->where($xz)->count();
// $chengjiao = model('second_house')->where($xz)->count();





$cje = model('second_house')->where($xz)->count();
//月度数据
$this->assign('jrcj',$jrcj);
$this->assign('jrxz',$jrxz);
$this->assign('xinzeng',$xinzeng);
$this->assign('chengjiao',$chengjiao);
$this->assign('cje',$cje);
//月度拍卖阶段成交
$this->assign('yipai',$yipai);
$this->assign('erpai',$erpai);
$this->assign('bianmai',$bianmai);
        // $data = json_encode($data);
        return $this->fetch();
    }
    

}
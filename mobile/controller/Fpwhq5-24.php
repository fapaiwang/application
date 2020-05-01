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


//状态：正常 一拍
// $yp[]=['create_time','egt',$aaa];
// $yp[]=['create_time','elt',$bbb];
$yp[] = ['status','eq',1];
$yp[] = ['jieduan','eq',161];
$yipai = model('second_house')->where($yp)->count();

//状态：正常 二拍
// $ep[]=['create_time','egt',$aaa];
// $ep[]=['create_time','elt',$bbb];
$ep[] = ['status','eq',1];
$ep[] = ['jieduan','eq',162];
$erpai = model('second_house')->where($ep)->count();

//状态：正常 二拍变卖
// $bm[]=['create_time','egt',$aaa];
// $bm[]=['create_time','elt',$bbb];
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
//溢价率
//(当月所有房源成交价总和减起拍价总和)/起拍价总和*100%

$yj[] = ['status','eq',1];
$yj[] = ['fcstatus','eq',175];
// $yjl = model('second_house')->where($yj)->count();
//成交价
$cjjzh = db('second_house')->field("sum(cjprice) as cjprices")->where(['fcstatus'=>175,'status'=>1])->find();
//起拍价
$qpjzh = db('second_house')->field("sum(qipai) as qipais")->where(['fcstatus'=>175,'status'=>1])->find();

$yjl=($cjjzh['cjprices']-$qpjzh['qipais'])/$qpjzh['qipais']*100;
$yjls=round($yjl,2);
print_r($yjl);
// $count = db('second_house')->field("sum(qipai) as qipais")->where(['broker_id'=>$broker_id,'status'=>1])->find();175
print_r($yjls);



    

//新增
// $xz[]=['create_time','egt',$aaa];
// $xz[]=['create_time','elt',$bbb];
$xz[] = ['status','eq',1];
$xinzeng = model('second_house')->where($xz)->count();

//成交
// $cj[]=['create_time','egt',$aaa];
// $cj[]=['create_time','elt',$bbb];
$cj[] = ['status','eq',1];
$cj[] = ['fcstatus','eq',175];
$chengjiao = model('second_house')->where($cj)->count();


//成交额
$cje = model('second_house')->where($xz)->count();


//500万以下
$wb[] = ['status','eq',1];
$wb[] = ['qipai','elt',500];
$wbw = model('second_house')->where($wb)->count();
// print_r($wbw);

//500万-1000万
$wbyq[] = ['status','eq',1];
$wbyq[] = ['qipai','gt',500];
$wbyq[] = ['qipai','lt',1000];
$wbyqw = model('second_house')->where($wbyq)->count();

//1000万以上
$yq[] = ['status','eq',1];
$yq[] = ['qipai','egt',1000];
$yqw = model('second_house')->where($yq)->count();



//今日新增成交
$this->assign('jrcj',$jrcj);
$this->assign('jrxz',$jrxz);
$this->assign('yjls',$yjls);
//月度数据
$this->assign('xinzeng',$xinzeng);
$this->assign('chengjiao',$chengjiao);
$this->assign('cje',$cje);
//月度拍卖阶段成交
$this->assign('yipai',$yipai);
$this->assign('erpai',$erpai);
$this->assign('bianmai',$bianmai);
//新增房源价格区间
$this->assign('wbw',$wbw);
$this->assign('wbyqw',$wbyqw);
$this->assign('yqw',$yqw);

        return $this->fetch();
    }
    

}
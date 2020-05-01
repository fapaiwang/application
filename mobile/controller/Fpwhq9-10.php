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

// $t1[]=['create_time','egt',$aaa];
// $t1[]=['create_time','egt',$bbb];
$t1[]=['status','eq',1];
$t1[]=['fcstatus','eq',175];
//成交价
$cjjzh = db('second_house')->field("sum(cjprice) as cjprices")->where($t1)->find();

$y1=count($cjjzh['cjprices']);
if($y1==0){
$cjjzh['cjprices']=0;
}
$k1=$cjjzh['cjprices'];
//起拍价
$qpjzh = db('second_house')->field("sum(qipai) as qipais")->where($t1)->find();
$z1=count($qpjzh['qipais']);
if($z1==0){
$qpjzh['qipais']=0;
}
$x1=$qpjzh['qipais'];

if($x1==0){
    $yjls=0;
}else{
   $yjl=($k1-$x1)/$x1*100;
    $yjls=round($yjl,2); 
}


// //成交价
// $cjjzh = db('second_house')->field("sum(cjprice) as cjprices")->where(['fcstatus'=>175,'status'=>1])->find();
// //起拍价
// $qpjzh = db('second_house')->field("sum(qipai) as qipais")->where(['fcstatus'=>175,'status'=>1])->find();

// $yjl=($cjjzh['cjprices']-$qpjzh['qipais'])/$qpjzh['qipais']*100;
// $yjls=round($yjl,2);
// print_r($yjl);

// print_r($yjls);
//昨日成交
$zt=strtotime(date("Y-m-d 00:00:00",strtotime("-1 day")));

$zt1=date("Y-m-d",strtotime("-1 day"));
// print_r($zt);
// print_r($zt);
// $zrcj[]=['endtime','egt',$zt];
// $zrcj[]=['endtime','lt',$rq];
$zrcj[] = ['status','eq',1];
$zrcj[] = ['fcstatus','eq',175];
// $zrcj[] = ['endtime','like',$zt1];
$azrcj = model('second_house')->where($zrcj)->select();
$zrcjs=0;
foreach ($azrcj as $key => $value) {
    $value['endtime']=strtotime($value['endtime']);
    // print_r($value['endtime']);
    
    if($value['endtime']>$zt && $value['endtime']<$rq){
        $zrcjs=$zrcjs+1;

    }
    # code...
}

// $zrcjs = model('second_house')->where($zrcj)->count();
// print_r($zrcjs);
//即将拍卖
$jjpm[] = ['status','eq',1];
$jjpm[] = ['fcstatus','eq',170];
$jjpms = model('second_house')->where($jjpm)->count();
//正在拍卖
$zzpm[] = ['status','eq',1];
$zzpm[] = ['fcstatus','eq',169];
$zzpms = model('second_house')->where($zzpm)->count();

$begin_time = date('Y-m-01 00:00:00',strtotime('-1 month'));
$end_time = date('Y-m-01 00:00:00');

$begin_times=strtotime($begin_time);
$end_times=strtotime($end_time);


//新增
$xz[]=['create_time','egt',$begin_times];
$xz[]=['create_time','elt',$end_times];
$xz[] = ['status','eq',1];
$xinzeng = model('second_house')->where($xz)->count();

//成交
$cj[]=['create_time','egt',$begin_times];
$cj[]=['create_time','elt',$end_times];
$cj[] = ['status','eq',1];
$cj[] = ['fcstatus','eq',175];
$chengjiao = model('second_house')->where($cj)->count();




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

// $dq[]=['create_time','egt',$aaa];
// $dq[]=['create_time','elt',$bbb];
// $b1 = model('second_house')->where(['city'=>$a1['id']])->where($dq)->count();
//东城区
$a1 = model('city')->where(['name'=>'东城区'])->find();
$b1 = model('second_house')->where(['city'=>$a1['id']])->count();
$this->assign('b1',$b1);
//西城区
$a2 = model('city')->where(['name'=>'西城区'])->find();
$b2 = model('second_house')->where(['city'=>$a2['id']])->count();
$this->assign('b2',$b2);
//朝阳区
$a3 = model('city')->where(['name'=>'朝阳区'])->find();
$b3 = model('second_house')->where(['city'=>$a3['id']])->count();
$this->assign('b3',$b3);
//丰台区
$a4 = model('city')->where(['name'=>'丰台区'])->find();
$b4 = model('second_house')->where(['city'=>$a4['id']])->count();
$this->assign('b4',$b4);
//石景山区
$a5 = model('city')->where(['name'=>'石景山区'])->find();
$b5 = model('second_house')->where(['city'=>$a5['id']])->count();
$this->assign('b5',$b5);
//海淀区
$a6 = model('city')->where(['name'=>'海淀区'])->find();
$b6 = model('second_house')->where(['city'=>$a6['id']])->count();
$this->assign('b6',$b6);
//房山区
$a7 = model('city')->where(['name'=>'房山区'])->find();
$b7 = model('second_house')->where(['city'=>$a7['id']])->count();
$this->assign('b7',$b7);
//通州区
$a8 = model('city')->where(['name'=>'通州区'])->find();
$b8 = model('second_house')->where(['city'=>$a8['id']])->count();
$this->assign('b8',$b8);
//顺义区
$a9 = model('city')->where(['name'=>'顺义区'])->find();
$b9 = model('second_house')->where(['city'=>$a9['id']])->count();
$this->assign('b9',$b9);
//昌平区
$a10 = model('city')->where(['name'=>'昌平区'])->find();
$b10 = model('second_house')->where(['city'=>$a10['id']])->count();
$this->assign('b10',$b10);
//大兴区
$a11 = model('city')->where(['name'=>'大兴区'])->find();
$b11 = model('second_house')->where(['city'=>$a11['id']])->count();
$this->assign('b11',$b11);
//怀柔区
// $a12 = model('city')->where(['name'=>'怀柔区'])->find();
// $b12 = model('second_house')->where(['city'=>$a12['id']])->count();
// $this->assign('b12',$b12);
//平谷区
// $a13 = model('city')->where(['name'=>'平谷区'])->find();
// $b13 = model('second_house')->where(['city'=>$a13['id']])->count();
// $this->assign('b13',$b13);
//密云区
$a14 = model('city')->where(['name'=>'密云区'])->find();
$b14 = model('second_house')->where(['city'=>$a14['id']])->count();
$this->assign('b14',$b14);
//延庆区
// $a15 = model('city')->where(['name'=>'延庆区'])->find();
// $b15 = model('second_house')->where(['city'=>$a15['id']])->count();
// $this->assign('b15',$b15);
//门头沟区
// $a16 = model('city')->where(['name'=>'门头沟区'])->find();
// $b16 = model('second_house')->where(['city'=>$a16['id']])->count();
// $this->assign('b16',$b16);

$c1 = strtotime(date('Y-01-01 00:00:00'));
$c2 = strtotime(date('Y-02-01 00:00:00'));
$c3 = strtotime(date('Y-03-01 00:00:00'));
$c4 = strtotime(date('Y-04-01 00:00:00'));
$c5 = strtotime(date('Y-05-01 00:00:00'));
$c6 = strtotime(date('Y-06-01 00:00:00'));
$c7 = strtotime(date('Y-07-01 00:00:00'));
$c8 = strtotime(date('Y-08-01 00:00:00'));
$c9 = strtotime(date('Y-09-01 00:00:00'));
$c10 = strtotime(date('Y-10-01 00:00:00'));
$c11 = strtotime(date('Y-11-01 00:00:00'));
$c12 = strtotime(date('Y-12-01 00:00:00'));
$c13 = strtotime(date('Y-12-31 23:59:59'));

//一月
$d1[] = ['create_time','egt',$c1];
$d1[] = ['create_time','elt',$c2];
$d1[] = ['status','eq',1];
$d1[] = ['fcstatus','eq',175];
$s1 = db('second_house')->field("sum(cjprice) as cjprices")->where($d1)->find();
$w1=count($s1['cjprices']);
if($w1==0){
$s1['cjprices']=0;
}
$q1=$s1['cjprices'];
$this->assign('q1',$q1);
// print_r($q1);
// print_r($s1['cjprices'];);
//二月
$d2[] = ['create_time','egt',$c2];
$d2[] = ['create_time','elt',$c3];
$d2[] = ['status','eq',1];
$d2[] = ['fcstatus','eq',175];
$s2 = db('second_house')->field("sum(cjprice) as cjprices")->where($d2)->find();
$w2=count($s2['cjprices']);
if($w2==0){
$s2['cjprices']=0;
}
$q2=$s2['cjprices'];
// print_r($q2);
$this->assign('q2',$q2);
//三月
$d3[] = ['create_time','egt',$c3];
$d3[] = ['create_time','elt',$c4];
$d3[] = ['status','eq',1];
$d3[] = ['fcstatus','eq',175];
$s3 = db('second_house')->field("sum(cjprice) as cjprices")->where($d3)->find();
$w3=count($s3['cjprices']);
if($w3==0){
$s3['cjprices']=0;
}
$q3=$s3['cjprices'];
$this->assign('q3',$q3);
//四月
$d4[] = ['create_time','egt',$c4];
$d4[] = ['create_time','elt',$c5];
$d4[] = ['status','eq',1];
$d4[] = ['fcstatus','eq',175];
$s4 = db('second_house')->field("sum(cjprice) as cjprices")->where($d4)->find();
$w4=count($s4['cjprices']);
if($w4==0){
$s4['cjprices']=0;
}
$q4=$s4['cjprices'];
$this->assign('q4',$q4);
//五月
$d5[] = ['create_time','egt',$c5];
$d5[] = ['create_time','elt',$c6];
$d5[] = ['status','eq',1];
$d5[] = ['fcstatus','eq',175];
$s5 = db('second_house')->field("sum(cjprice) as cjprices")->where($d5)->find();
$w5=count($s5['cjprices']);
if($w5==0){
$s5['cjprices']=0;
}
$q5=$s5['cjprices'];
$this->assign('q5',$q5);
//六月
$d6[] = ['create_time','egt',$c6];
$d6[] = ['create_time','elt',$c7];
$d6[] = ['status','eq',1];
$d6[] = ['fcstatus','eq',175];
$s6 = db('second_house')->field("sum(cjprice) as cjprices")->where($d6)->find();
$w6=count($s6['cjprices']);
if($w6==0){
$s6['cjprices']=0;
}
$q6=$s6['cjprices'];
$this->assign('q6',$q6);
//七月
$d7[] = ['create_time','egt',$c7];
$d7[] = ['create_time','elt',$c8];
$d7[] = ['status','eq',1];
$d7[] = ['fcstatus','eq',175];
$s7 = db('second_house')->field("sum(cjprice) as cjprices")->where($d7)->find();
$w7=count($s7['cjprices']);
if($w7==0){
$s7['cjprices']=0;
}
$q7=$s7['cjprices'];
$this->assign('q7',$q7);
//八月
$d8[] = ['create_time','egt',$c8];
$d8[] = ['create_time','elt',$c9];
$d8[] = ['status','eq',1];
$d8[] = ['fcstatus','eq',175];
$s8 = db('second_house')->field("sum(cjprice) as cjprices")->where($d8)->find();
$w8=count($s8['cjprices']);
if($w8==0){
$s8['cjprices']=0;
}
$q8=$s8['cjprices'];
$this->assign('q8',$q8);
//九月
$d9[] = ['create_time','egt',$c9];
$d9[] = ['create_time','elt',$c10];
$d9[] = ['status','eq',1];
$d9[] = ['fcstatus','eq',175];
$s9 = db('second_house')->field("sum(cjprice) as cjprices")->where($d9)->find();
$w9=count($s9['cjprices']);
if($w9==0){
$s9['cjprices']=0;
}
$q9=$s9['cjprices'];
$this->assign('q9',$q9);
//十月
$d10[] = ['create_time','egt',$c10];
$d10[] = ['create_time','elt',$c11];
$d10[] = ['status','eq',1];
$d10[] = ['fcstatus','eq',175];
$s10 = db('second_house')->field("sum(cjprice) as cjprices")->where($d10)->find();
$w10=count($s10['cjprices']);
if($w10==0){
$s10['cjprices']=0;
}
$q10=$s10['cjprices'];
$this->assign('q10',$q10);
//十一月
$d11[] = ['create_time','egt',$c11];
$d11[] = ['create_time','elt',$c12];
$d11[] = ['status','eq',1];
$d11[] = ['fcstatus','eq',175];
$s11 = db('second_house')->field("sum(cjprice) as cjprices")->where($d11)->find();
$w11=count($s11['cjprices']);
if($w11==0){
$s11['cjprices']=0;
}
$q11=$s11['cjprices'];
$this->assign('q11',$q11);
//十二月
$d12[] = ['create_time','egt',$c12];
$d12[] = ['create_time','elt',$c13];
$d12[] = ['status','eq',1];
$d12[] = ['fcstatus','eq',175];
$s12 = db('second_house')->field("sum(cjprice) as cjprices")->where($d12)->find();
$w12=count($s12['cjprices']);
if($w12==0){
$s12['cjprices']=0;
}
$q12=$s12['cjprices'];
$this->assign('q12',$q12);

//今日新增、成交、溢价率
$this->assign('jrcj',$jrcj);
$this->assign('jrxz',$jrxz);
$this->assign('yjls',$yjls);
//昨日成交、即将拍卖、正在拍卖
$this->assign('zrcjs',$zrcjs);
$this->assign('jjpms',$jjpms);
$this->assign('zzpms',$zzpms);
//月度数据
$this->assign('xinzeng',$xinzeng);
$this->assign('chengjiao',$chengjiao);
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
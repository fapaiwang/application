<?php





namespace app\mobile\controller;

use app\common\controller\MobileBase;

class Quyu extends MobileBase

{

    private $pageSize = 10;

    private $mod      = 'quyu';



    /**

     * @return mixed

     * 楼盘列表

     */

    public function index()

    {
        $quyu = model('city')->where('pid',39)->select();
        foreach ($quyu as $key => $value) {
// $data[] = ['city','in',$this->getCityChild($value['id'])];
            // $aaa[$value['id']] = model('second_house')->where('city',$value['id'])->count('city');
            // $aaa[$value['id']]=$value['name'];
            // $aaa[$key]['shuliang'] = model('second_house')->where($data)->count();
// $bbb[] = model('second_house')->where($data)->select();
// foreach ($bbb as $key => $value) {
//     $aaa[$key]['shuliang']+=$value;
// }
//             print_r($aaa[$key]['shuliang']);
$bbb=$this->getCityChild($value['id']);
// $bbb=$this->getCityChild($value['id']);
// print_r($this->getCityChild($value['id']));
$ccc=array();
foreach ($bbb as $keys => $values) {
    // $ccc[]= model('second_house')->where(['status'=>1,'city'=>$values])->select();
    $ccc[]= model('second_house')->where(['status'=>1,'city'=>$values])->count();
    // $ccc+=$ccc;
}
// print_r($ccc);
// exit();
// $aaa[$key]['shuliang'] = model('second_house')->where('city',$value['id'])->count();
$ddd=0;
// print_r($ddd);
foreach ($ccc as $keyss => $valuess) {
    // print_r($valuess);
    $ddd+=$valuess;
}
// print_r($ddd);
$aaa[$key]['shuliang']=$ddd;
// print_r($ddd);
// exit();




            // $aaa[$key]['shuliang'] = model('second_house')->where('city',$value['id'])->count('city');
            $aaa[$key]['name']=$value['name'];
            $aaa[$key]['id']=$value['id'];





//成交价
// $aaa[$key]['cjjzh'] =db('second_house')->field("sum(cjprice) as cjprices")->where(['status'=>1,'fcstatus'=>175,'city'=>$value['id']])->find();
$cjjzh =db('second_house')->field("sum(cjprice) as cjprices")->where(['status'=>1,'fcstatus'=>175,'city'=>$value['id']])->find();
// $aaa[$key]['cjjzh']=$cjjzh['cjprices'];
$y1=count($cjjzh['cjprices']);
if($y1==0){
$cjjzh['cjprices']=0;
}

$qpjzh =db('second_house')->field("sum(qipai) as qipais")->where(['status'=>1,'fcstatus'=>175,'city'=>$value['id']])->find();
// $aaa[$key]['qpjzh']=$qpjzh['qipais'];
$z1=count($qpjzh['qipais']);
if($z1==0){
$qpjzh['qipais']=0;
}
if($qpjzh['qipais']==0){
    $aaa[$key]['yjl']=0;
}elseif($cjjzh['cjprices']==0){
$aaa[$key]['yjl']=0;
}
else{
$aaa[$key]['yjl']=round(($cjjzh['cjprices']-$qpjzh['qipais'])/$qpjzh['qipais']*100,2);
}
$aaa[$key]['cjprices']=$cjjzh['cjprices'];
$aaa[$key]['qipais']=$qpjzh['qipais'];








// $y1=count($cjjzh['cjprices']);
// if($y1==0){
// $cjjzh['cjprices']=0;
// }
// $k1=$cjjzh['cjprices'];
// //起拍价
// $qpjzh = db('second_house')->field("sum(qipai) as qipais")->where($t1)->find();
// $z1=count($qpjzh['qipais']);
// if($z1==0){
// $qpjzh['qipais']=0;
// }
// $x1=$qpjzh['qipais'];

// if($x1==0){
//     $yjls=0;
// }else{
//    $yjl=($k1-$x1)/$x1*100;
//     $yjls=round($yjl,2); 
// }






// 当月所有房源成交价总和减起拍价总和/起拍价总和


        }
// print_r($aaa);

$this->assign('gl',$aaa);
        return $this->fetch();

    }



}
<?php




namespace app\mobile\controller;

use app\common\controller\MobileBase;

class Tdistrict extends MobileBase

{




    /**

     * @return mixed

     * 楼盘列表

     */
    public function index()

    {
        if(!empty($_GET['id'])){
            $id=$_GET['id'];
            $objs   = model('second_house');
            $tong = $objs->where('estate_id',$id)->where('fcstatus',175)->where('status',1)->select();
            $tcou = $objs->where('estate_id',$id)->where('fcstatus',175)->where('status',1)->count();
    // print_r($tcou);exit();
            $this->assign('tong',$tong);
            $this->assign('tcou',$tcou);
        }
        else{
            $tcou=0;
    $tong=array();
    $this->assign('tong',$tong);
    $this->assign('tcou',$tcou);
    return $this->fetch('public/404');
}

        


        return $this->fetch();
    }
    public function second()

    {
if(!empty($_GET['id'])){
    $id=$_GET['id'];
        $objs   = model('transaction_record');
        $tong = $objs->where('estate_id',$id)->where('estate_id',$id)->select();
        $tcou = $objs->where('estate_id',$id)->where('estate_id',$id)->count();
// print_r($tcou);exit();
        $this->assign('tong',$tong);
        $this->assign('tcou',$tcou);

}
else{
    $tcou=0;
    $tong=array();
    $this->assign('tong',$tong);
    $this->assign('tcou',$tcou);
    return $this->fetch('public/404');
}
        

        return $this->fetch();
    }
    

}
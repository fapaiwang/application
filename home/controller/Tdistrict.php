<?php





namespace app\home\controller;

use app\common\controller\HomeBase;

use app\common\service\Metro;

class Tdistrict extends HomeBase

{

    private $pageSize = 10;

    private $mod      = 'fpwhq';



    /**

     * @return mixed

     * 楼盘列表

     */
    public function index()

    {

        $id=$_GET['id'];
        $objs   = model('second_house');
        $tong = $objs->where('estate_id',$id)->where('fcstatus',175)->where('status',1)->select();
        $tcou = $objs->where('estate_id',$id)->where('fcstatus',175)->where('status',1)->count();
// print_r($tcou);exit();
        $this->assign('tong',$tong);
        $this->assign('tcou',$tcou);


        return $this->fetch();
    }
    

}
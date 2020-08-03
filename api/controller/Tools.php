<?php

namespace app\api\controller;

use app\common\service\ImageServer;
use app\home\controller\Api;
use app\home\service\ToolsServer;
use app\tools\ApiResult;
use app\tools\Constant;
use think\Controller;
use think\facade\Request;
use think\Log;

class Tools extends Controller
{
    use ApiResult;
    protected $ToolsService;
    protected $Api;
    public function __construct(ToolsServer $ToolsService,Api $Api)
    {
        $this->ToolsService = $ToolsService;
        $this->Api = $Api;
    }

    /**
     * 房贷计算
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function house_loan(){
        $dai_nianxian    = input('dai_nianxian');//年限
        $dai_qipai    = input('dai_qipai');//起拍价
        $dai_lilv    = input('dai_lilv');//利率
        $dai_huankuan    = input('dai_huankuan');//还款方式
        $dai_bili    = input('dai_bili');//比例
        if (empty($dai_nianxian) || empty($dai_qipai) || empty($dai_lilv) || empty($dai_huankuan) || empty($dai_bili) ){
            return  $this->error_o("传参不能为空");
        }
        $dai_mianji    = input('dai_mianji',80);
        $res =$this->Api->house_loan_s($dai_nianxian,$dai_qipai,$dai_lilv,$dai_mianji,$dai_bili,$dai_huankuan);
        if ($res){
            $return['data']  = $res['res'];
            $return['info']  = $res['info'];
        }else{
            return  $this->error_o("操作失败");
        }
        return $this->success_o($return);
    }

    /**
     * 利率
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function house_loan_lilv(){
        $res["dai_lilv"] =[
            ["val"=>4.41,"describe"=>"基准利率9折(4.41%)"],
            ["val"=>4.655,"describe"=>"基准利率95折(4.655%)"],
            ["val"=>4.0,"describe"=>"基准利率(4.0%)"],
            ["val"=>5.145,"describe"=>"基准利率上浮5%(5.145%)"]
        ];
        return $this->success_o($res);
    }
    /**
     * //贷款年限
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function house_loan_years(){
        $start    = input('start',5);
        $end    = input('end',26);
        $arr = array_range($start,$end,1);
        return $this->success_o($arr);
    }

    /**
     * 获取贷款比例
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function house_loan_bili(){
        $start    = input('start',5);
        $end    = input('end',70);
        $step    = input('step',5);
        $arr = array_range($start,$end,$step);
        $arr[]=100;
        rsort($arr);
        return $this->success_o($arr);
    }
    function test()
    {
        $ImageServer = new ImageServer();
        $return = array();
        $url = input('get.url');
        $num = input('get.num');
        $mulu = scandir('../public/uploads/'.$url.'/');
        unset($mulu[0]);unset($mulu[1]);
        $accc = array_chunk($mulu, 10);
        foreach($accc[$num] as $k=>$v){
            $return[$v]['code'] = 1;
            $data = scandir('../public/uploads/'.$url.'/'.$v.'/');
            foreach($data as $ki=>$vi){
                $a=strpos($vi, '.png');
                $b=strpos($vi, '.jpg');
                if(!$a&&!$b){
                    unset($data[$ki]);
                }
            }
            if(!empty($data)){
                $return[$v]['code'] = 2;
                foreach($data as $ks=>$vs){
                    $ImageServer->ImageWater('../public/uploads/'.$url.'/'.$v.'/'.$vs,'../public/static/shuiyin/ppshuiyin.png',10);
                    $return[$v][$ks]['url'] = 'www.fangpaiwang.com/uploads/'.$url.'/'.$v.'/'.$vs;
                }
            }
        }
        return $this->success_o($return);
    }
    function test2(){
        //$num = input('get.num');
        //$url = input('get.url');//estate
       /* $data = scandir('../public/uploads/image/');
        return $this->success_o($data);*/
       // use app\common\service\ImageServer;
        /*$ImageServer = new ImageServer();
        $arr = $ImageServer->ImageWater('../public/1/4.jpg','../public/static/shuiyin/ppshuiyin.png',10);
        dd($arr);*/
    }


}
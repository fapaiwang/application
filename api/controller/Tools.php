<?php

namespace app\api\controller;


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
        /*$return = array();
        $num = input('get.num');//1
        $accc = '20190529,20190530,20190601,20190603,20190604,20190605,20190606,20190610,20190611,20190612,20190613,20190615,20190617,20190618,20190619,20190620,20190621,20190624,20190625,20190626,20190627,20190628,20190701,20190702,20190703,20190704,20190705,20190708,20190709,20190710,20190711,20190712,20190715,20190716,20190717,20190718,20190719,20190722,20190723,20190724,20190725,20190726,20190729,20190730,20190731,20190801,20190802,20190805,20190806,20190807,20190808,20190809,20190812,20190813,20190819,20190820,20190821,20190822,20190823,20190826,20190827,20190828,20190829,20190830,20190831,20190902,20190903,20190904,20190905,20190906,20190908,20190909,20190910,20190911,20190912,20190915,20190916,20190917,20190918,20190919,20190920,20190921,20190923,20190924,20190925,20190926,20190927,20190928,20190929,20190930,20191001,20191006,20191007,20191008,20191009,20191010,20191011,20191012,20191014,20191015,20191016,20191017,20191018,20191021,20191022,20191023,20191024,20191025,20191028,20191029,20191030,20191031,20191101,20191102,20191104,20191105,20191106,20191107,20191108,20191111,20191112,20191113,20191114,20191115,20191116,20191118,20191119,20191120,20191121,20191122,20191123,20191125,20191126,20191127,20191128,20191129,20191202,20191203,20191204,20191205,20191206,20191207,20191209,20191210,20191211,20191212,20191213,20191216,20191217,20191218,20191219,20191220,20191221,20191223,20191224,20191225,20191226,20191227,20191230,20191231,20200102,20200103,20200106,20200107,20200108,20200109,20200110,20200113,20200114,20200115,20200116,20200117,20200119,20200120,20200203,20200204,20200205,20200210,20200211,20200212,20200213,20200214,20200217,20200218,20200219,20200220,20200221,20200224,20200225,20200226,20200227,20200228,20200302,20200303,20200304,20200305,20200306,20200309,20200310,20200311,20200312,20200313,20200316,20200317,20200318,20200319,20200320,20200321,20200323,20200324,20200325,20200326,20200327,20200330,20200331,20200401,20200402,20200403,20200407,20200408,20200409,20200410,20200413,20200414,20200415,20200416,20200417,20200420,20200421,20200422,20200423,20200424,20200426,20200427,20200428,20200429,20200430,20200506,20200507,20200508,20200509,20200511,20200512,20200513,20200514,20200515,20200518,20200519,20200520,20200521,20200522,20200525,20200526,20200527,20200528,20200529,20200601,20200602,20200603,20200604,20200605,20200606,20200608,20200609,20200610,20200611,20200612,20200613,20200615,20200616,20200617,20200618,20200619,20200620,20200622,20200623,20200624,20200627,20200628,20200629,20200630,20200701,20200702,20200703,20200704,20200706,20200707,20200708,20200709,20200710,20200711,20200713,20200714,20200715,20200716,20200717,20200718,20200720,20200721,20200722,20200723,20200724,20200725,20200727,20200728,20200729,20200730';
        $accc = explode(',',$accc);
        $accc = array_chunk($accc, 10);
        foreach($accc[$num] as $k=>$v){
            $return[$v]['code'] = 1;
            $data = scandir('../public/uploads/secondhouse/'.$v.'/');
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
                    $image = \think\Image::open('../public/uploads/secondhouse/'.$v.'/'.$vs);
                    $image->water('../public/shuiyin2.png',\think\Image::WATER_CENTER,100)->save('../public/uploads/secondhouse/'.$v.'/'.$vs);
                    $return[$v][$ks]['url'] = 'www.fangpaiwang.com/uploads/secondhouse/'.$v.'/'.$vs;
                }
            }
        }
        return $this->success_o($return);*/
//https://www.fangpaiwang.com/api/test?num=30
    }
    function test2(){
        $url = input('get.url');//estate
        $data = scandir('../public/uploads/'.$url.'/');
        return $this->success_o($data);
    }

}
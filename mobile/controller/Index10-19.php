<?php



namespace app\mobile\controller;

use app\common\controller\MobileBase;

class Index extends MobileBase

{

    public function index()

    {

    
// $lists = model('second_house')->select();
        
    
//          foreach ($lists as $key => $value) {
             

//             $lists[$key]['kptimes']=strtotime($lists[$key]['kptime']);
//             $lists[$key]['bianetimes']=strtotime($lists[$key]['bianetime']);
//             $sTime=time();
//             // print_r($lists[$key]['fcstatus']);
//             if($lists[$key]['fcstatus']==169 || $lists[$key]['fcstatus']==170 || $lists[$key]['fcstatus']==171){

//                 $ctimes=$sTime-$lists[$key]['kptimes'];

//                 // print_r($lists[$key]['jieduan']);
//                 if($lists[$key]['jieduan']==163){
//                 $ctimess=$sTime-$lists[$key]['bianetimes'];
//                 if($ctimes>=0){
//                     //当前时间-开拍时间
//                     if($ctimess >= 0){
//                     model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>171]);//正在进行169
//                     // print_r($ctimes);echo "aaa";
//                     }else{
//                     model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>169]);//已结束171
//                      // print_r($ctimes);echo "aaa";
//                     }
//                     // else{
//                     // model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>170]);//即将开始170
//                     //  // print_r($ctimes);echo "aaa";
//                     // }

//                 }else{
//                     model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>170]);//即将开始170
//                 }
                
//                 }else{
//                 if($ctimes >= 0 && $ctimes < 3600*24){
//                     model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>169]);//正在进行
//                     // print_r($ctimes);echo "aaa";
//                     }elseif($ctimes >= (3600*24)){
//                     model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>171]);//已结束171
//                      // print_r($ctimes);echo "aaa";
//                     }else{
//                     model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>170]);//即将开始170
//                      // print_r($ctimes);echo "aaa";
//                     }
//                 }
//             }
//         }










        $full_screen = db('module')->field('id')->where('type',1)->where('status',1)->where('terminal',2)->order('ordid asc')->select();

        $this->assign('module',$full_screen);

        //正在进行
        $zzjx = model('second_house')->where('fcstatus',169)->where('status',1)->count();
        $this->assign('zzjx',$zzjx);
        //即将拍卖
        $jjpm = model('second_house')->where('fcstatus',170)->where('status',1)->count();
        $this->assign('jjpm',$jjpm);







        $this->assign('news',$this->getTopNews());

        $this->assign('group',$this->getNewGroup(5));

        $this->assign('house',$this->getReconnonHouse(1));

        $this->assign('time',time());

        $this->assign('city',$this->getCity());
		
		
	
		
		

        return $this->fetch();

    }



    /**

     * @param int $num

     * @return array|\PDOStatement|string|\think\Collection

     * 资讯

     */

    private function getTopNews($num = 5)

    {

        $where['status'] = 1;

        $where[] = ['city','eq',$this->cityInfo['id']];

        $lists = model('article')->where($where)->order(['ordid'=>'asc','id'=>'desc'])->limit($num)->select();

        return $lists;

    }



    /**

     * @param $cate_id

     * @param int $num

     * @return array|\PDOStatement|string|\think\Collection

     * 推荐新房

     */

    private function getReconnonHouse($cate_id,$num=5)

    {

        $where['p.status']  = 1;

        $where['p.cate_id'] = $cate_id;

        $where['h.status']  = 1;

        $where['p.model']   = 'house';

        $this->getCityChild() && $where[] = ['h.city','in',$this->getCityChild()];

        $join  = [

            ['house h','p.house_id = h.id'],

            ['house_search s','s.house_id = h.id','left']

        ];

        $field = 'h.id,h.is_discount,h.discount,h.img,h.title,h.tags_id,h.price,h.unit,h.city,s.min_type,s.max_type,s.min_acreage,s.max_acreage';

        $lists = model('position')->alias('p')

            ->join($join)

            ->where($where)

            ->field($field)

            ->order( ['h.ordid'=>'asc','h.id'=>'desc'])

            ->limit($num)

            ->select();

        return $lists;

    }



    /**

     * @param int $num

     * @return array|\PDOStatement|string|\think\Collection

     * 最新团购

     */

    private function getNewGroup($num = 4)

    {

        $time            = time();

        $where['status'] = 1;

        $where[]         = ['begin_time','lt',$time];

        $where[]         = ['end_time','gt',$time];

        $this->getCityChild() && $where[] = ['city','in',$this->getCityChild()];

        $lists = model('group')->where($where)->field('id,city,title,end_time,price,discount,img')->order(['ordid'=>'asc','id'=>'desc'])->limit($num)->select();

        return $lists;

    }

    /**

     * @return array

     * 按字母顺序排列的全部城市

     */

    private function getCity()

    {

        $city = getCity();

        $hot  = [];

        $city_arr = [];

        foreach($city as $v)

        {

            if($v['is_hot'] == 1)

            {

                $hot[] = $v;

            }

            $first = strtoupper(substr($v['alias'],0,1));

            $city_arr[$first][] = $v;

        }

        ksort($city_arr);

        return ['hot'=>$hot,'city'=>$city_arr];

    }

}
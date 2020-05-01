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

        $info = cookie('userInfo');

        $info = \org\Crypt::decrypt($info);
        if(!empty($info['id'])){
        $user_id[]=['user_id','eq',$info['id']];

        $yjdzs = db('yjdz')->where($user_id)->order('zonge','desc')->find();
        //一键定制
        if(!empty($yjdzs)){
            $zonges=$yjdzs['zonge'];


            $jiage[]=['qipai','elt',$zonges];
            $jiage[]=['fcstatus','elt',170];

            $lists = model('second_house')->where($jiage)->order('qipai','desc')->count();

            // print_r($yjdzs['shuliang']);
            // print_r($lists);
            if($yjdzs['shuliang']<$lists){
                $cha=$lists-$yjdzs['shuliang'];
              
            }

        }
        //关注小区
         $fields="distinct(house_id),id,user_id,model,create_time,shuliang";
        $gzuser_id[]=['user_id','eq',$info['id']];
        $gzuser_id[]=['model','eq','estate'];
        $gzxqs = db('follow')->field($fields)->where($gzuser_id)->order('id','asc')->select();
       // print_r($gzxqs);
       
        foreach ($gzxqs as $key => $value) {
            // print_r($value['house_id']);
            // $user_ids[]=['estate_id','eq',$value['house_id']];
            // print_r($user_ids[]);
            $gzxq[]= db('second_house')->where(['estate_id'=>$value['house_id']])->select();
            $gzxqss[]= db('second_house')->where(['estate_id'=>$value['house_id']])->count();
            // print_r($gzxqss);
            // echo "a";
            // $gzxqss+=$gzxqss;
            
            
            
        }
// print_r($gzxq);
// print_r($gzxqss);
$aaa=0;
foreach ($gzxqss as $keys => $values) {
    $aaa+=$values;
}
// print_r($aaa);
if($aaa>0){


        $gzsuser_id[]=['id','eq',$info['id']];

            $gzs = model('user')->where($gzsuser_id)->find();

            // print_r($gzfy['shuliang']);
            // print_r($gzfys);
            if($aaa>$gzs['gzxq']){
                $chagzxqs=$aaa-$gzs['gzxq'];
   
            }

        }

        //关注房源
        $user_idss[]=['user_id','eq',$info['id']];
        $user_idss[]=['model','eq','second_house'];
        $gzfy = db('follow')->where($user_idss)->order('id','asc')->find();
        // print_r($gzfy['shuliang']);
        if(!empty($gzfy)){


            $fields="distinct(house_id),id,shuliang";

            $gzfys = model('follow')->field($fields)->where($user_idss)->order('id','asc')->count();
            // print_r($gzfys);

            // print_r($gzfy['shuliang']);
            // print_r($gzfys);
            if($gzfy['shuliang']<$gzfys){
                $chagzfy=$gzfys-$gzfy['shuliang'];
              
            }

        }

        //人工推送
        $tuisongs[]=['tuisong','eq',1];
        $ts = db('second_house')->where($tuisongs)->count();
        // print_r($ts);
        if(!empty($ts)){


        $tsuser_id[]=['id','eq',$info['id']];

            $tss = model('user')->where($tsuser_id)->find();

            // print_r($gzfy['shuliang']);
            // print_r($gzfys);
            if($ts>$tss['rgts']){
                $chatss=$ts-$tss['rgts'];
               

            }


        }
        //推荐房源
        $tj[]=['rec_position','eq',1];
        $tjs = db('second_house')->where($tj)->count();
        // print_r($ts);
        if(!empty($tjs)){


        $tjuser_id[]=['id','eq',$info['id']];

            $tjss = model('user')->where($tjuser_id)->find();

            // print_r($gzfy['shuliang']);
            // print_r($gzfys);
            if($tjs>$tjss['tjfy']){
                $chatjs=$tjs-$tjss['tjfy'];


            }




        }


$zongshu=$cha+$chagzxqs+$chagzfy+$chatss+$chatjs;






        $this->assign('zongshu',$zongshu);


}








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
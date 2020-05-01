<?php

namespace app\mobile\controller;
use app\common\controller\MobileBase;
class Second extends MobileBase
{
    private $pageSize = 30;
    public function index()
    {

$arr=$this->request->param();

if(!empty($arr['cj'])){
    $cjs=1;

}else{
    $cjs=0;
}
$this->assign('cjs',$cjs);

        $result = $this->getLists();
        $lists  = $result['lists'];

        foreach ($lists as $key => $value) {
             

            $lists[$key]['kptimes']=strtotime($lists[$key]['kptime']);
            $lists[$key]['bianetimes']=strtotime($lists[$key]['bianetime']);
            $sTime=time();
            // print_r($lists[$key]['fcstatus']);
            if($lists[$key]['fcstatus']==169 || $lists[$key]['fcstatus']==170 || $lists[$key]['fcstatus']==171){

                $ctimes=$sTime-$lists[$key]['kptimes'];

                // print_r($lists[$key]['jieduan']);
                if($lists[$key]['jieduan']==163){
                $ctimess=$sTime-$lists[$key]['bianetimes'];
                if($ctimes>=0){
                    //当前时间-开拍时间
                    if($ctimess >= 0){
                    model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>171]);//正在进行169
                    // print_r($ctimes);echo "aaa";
                    }else{
                    model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>169]);//已结束171
                     // print_r($ctimes);echo "aaa";
                    }
                    // else{
                    // model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>170]);//即将开始170
                    //  // print_r($ctimes);echo "aaa";
                    // }

                }else{
                    model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>170]);//即将开始170
                }
                
                }else{
                if($ctimes >= 0 && $ctimes < 3600*24){
                    model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>169]);//正在进行
                    // print_r($ctimes);echo "aaa";
                    }elseif($ctimes >= (3600*24)){
                    model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>171]);//已结束171
                     // print_r($ctimes);echo "aaa";
                    }else{
                    model('second_house')->where(['id'=>$lists[$key]['id']])->update(['fcstatus'=>170]);//即将开始170
                     // print_r($ctimes);echo "aaa";
                    }
                }
            }
        }
    // foreach ($lists as $key => $value) {
  //           print_r($value);exit();
  //           // $lists['qipai']=number_format("$value['qipai']",2,".","");
  //       }
    // print_r($lists->render());exit();

    
        $this->assign('area',$this->getAreaByCityId());
        $this->assign('type',getLinkMenuCache(9));//类型
        $this->assign('renovation',getLinkMenuCache(8));//装修情况

        $this->assign('tags',getLinkMenuCache(14));//标签
        $result = $this->getLists();
        $lists  = $result['lists'];
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('total_page',$lists->lastPage());
        
        $this->assign('top_lists',$result['top']);//置顶房源
        $this->assign('storage_open',getSettingCache('storage','open'));
        if(!empty($_GET['quyu'])){
            $quyuz=$_GET['quyu'];
            
            $quyus=$_GET['quyu'];
            $quyua = model('city')->where('id',$quyus)->find();

$timess=time();
// $sls[] = ['timeout','gt',$timess];
$sls[] = ['city','eq',$quyus];
$shuliang = model('second_house')->where($sls)->count('city');



//成交价
$cjjzh =db('second_house')->field("sum(cjprice) as cjprices")->where(['status'=>1,'fcstatus'=>175,'city'=>$quyus])->find();
$y1=count($cjjzh['cjprices']);
if($y1==0){
$cjjzh['cjprices']=0;
}

$qpjzh =db('second_house')->field("sum(qipai) as qipais")->where(['status'=>1,'fcstatus'=>175,'city'=>$quyus])->find();
$z1=count($qpjzh['qipais']);
if($z1==0){
$qpjzh['qipais']=0;
}
if($qpjzh['qipais']==0){
    $yjl=0;
}elseif($cjjzh['cjprices']==0){
$yjl=0;
}
else{
$yjl=round(($cjjzh['cjprices']-$qpjzh['qipais'])/$qpjzh['qipais']*100,2);
}





$this->assign('shuliang',$shuliang);
$this->assign('yjl',$yjl);
            // print_r($quyu);exit();
            // $quyu=$quyusql['name'];
            $this->assign('quyua',$quyua);

        }else{
            $quyus='0';
            $quyuz='0';
        }
        $this->assign('quyus',$quyus);
        $this->assign('quyuz',$quyuz);
        
        return $this->fetch();
    }

    /**
     * @return \think\response\Json
     * 异步获取房源列表
     */
    public function getSecondLists()
    {
      
        // print_r($_GET['page']);exit();
        $page    = input('get.page/d',1);
        // $page=2;
        // print_r($page);exit();
        $data    = $this->getLists($page);
        $lists   = $data['lists'];
$arr=$this->request->param();
// print_r($arr);

 


        foreach ($lists as $key => $value) {
                $estate_id=$lists[$key]['estate_id'];

               $sql=model('estate')->where('id','eq',$estate_id)->alias('years')->find();
               $years=$sql['years'];
               $lists[$key]['years']=$years;



                

           }
        
        
        $storage_open = getSettingCache('storage','open');
        if($lists)
        {
            foreach($lists as &$v)
            {
                $is_video  = $v['video'] && $storage_open == 1 ? true : false;
                $v['is_video'] = $is_video;
                $v['url']  = url('Second/detail',['id'=>$v['id']]);
                $v['city'] = getCityName($v['city']);
                $v['img']  = thumb($v['img'],200,150);
                $v['renovation'] = getLinkMenuName(8,$v['renovation']);
                $v['acreage']         = $v['acreage'].config('filter.acreage_unit');
                $v['update_time'] = getTime($v['update_time'],'mohu');
                if(!empty($arr['cj'])){
                    $v['cj']=175;
   

                }else{
                   $v['cj']=0;
                }
                $tags = array_filter(explode(',',$v['tags']));
                if(is_array($tags))
                {
                    $tag_str = '';
                    foreach($tags as $key=>$val)
                    {
                        if($key==0){
                             $tag_str .= '<em style="margin-top:.1rem;padding-top:.1rem;padding-bottom:.1rem;background: #ddfce1;
    color: #63cd71;padding: .2rem;margin-right:5px;margin-left: .15rem;">'.getLinkMenuName(14,$val).'</em>';
                        }else{
                             $tag_str .= '<em style="margin-top:.1rem;padding-top:.1rem;padding-bottom:.1rem;background: #fff5de;
    color: #fcc648;padding: .2rem;margin-right:5px;margin-left: .15rem;">'.getLinkMenuName(14,$val).'</em>';
                        }
                       
                    }
                    // foreach($tags as $val)
                    // {
                    //     $tag_str .= '<em>'.getLinkMenuName(14,$val).'</em>';
                    // }
                    $v['tags'] = $tag_str;
                }
            }
        }
        $return['code'] = 1;
        $return['data'] = $lists;
        $return['total_page'] = $data['total_page'];
        return json($return);
    }
    public function detail()
    {
        $id = input('param.id/d',0);

// print_r($id);
        //获取当前登录人
        $info = cookie('userInfo');
        $info = \org\Crypt::decrypt($info);
        if(!empty($info['id'])){
        $data['user_id']=$info['id'];
        $data['house_id']=$id;
        $data['model']='second_house';
        $data['create_time']=time();
        $user_ids=$info['id'];
        $counts = model('zuji')->where(['house_id'=>$id,'user_id'=>$user_ids])->count();
        if($counts=0){
        db('zuji')->insert($data);
        }else{
        db('zuji')->where(['house_id'=>$id,'user_id'=>$user_ids])->delete();
        db('zuji')->insert($data);
        }
        
        }










        $wg = model('second_house')->where('id',$id)->find();
        $wgs=$wg['weiguan']+1;
        model('second_house')->where(['id'=>$id])->update(['weiguan'=>$wgs]);
        // print_r($id);
        // print_r($wgs);
        if($id)
        {
            $where['h.id']     = $id;
            $where['h.status'] = 1;
            $obj  = model('second_house');
            $join = [['second_house_data d','h.id=d.house_id']];
            $info = $obj->alias('h')->join($join)->where($where)->find();
            if(!$info)
            {
                return $this->fetch('public/404');
            }     
      
      
            $info['file'] = json_decode($info['file'],true);
            $this->setSeo($info);
            $share_title = $info['estate_name'].$info['room'].'室'.$info['living_room'].'厅'.$info['acreage'].config('filter.acreage_unit').$info['price'].'万';
            updateHits($info['id'],'second_house');
            $estate = model('estate')->where('id',$info['estate_id'])->find();

             // print_r($info['city']);

                 $city=$info['city'];
              $lists = model('city')->field('id,pid,spid,name,alias')->where('id','eq',$city)->find();
              $spid=$lists['spid'];
              $city_name=$lists['name'];
                 if(substr_count($spid,'|')==2){
                    $listsss = model('city')->field('id,name')->where('id','eq',$lists['pid'])->find();
                    $shi=$listsss['name'];  
                    $citys=$shi.$city_name; 
                 }else{
                    $citys=$city_name;
                 }


               //二手房价格走势

                // $c1 = strtotime(date('Y-01-01 00:00:00'));
                // $c2 = strtotime(date('Y-02-01 00:00:00'));
                // $c3 = strtotime(date('Y-03-01 00:00:00'));
                // $c4 = strtotime(date('Y-04-01 00:00:00'));
                // $c5 = strtotime(date('Y-05-01 00:00:00'));
                // $c6 = strtotime(date('Y-06-01 00:00:00'));
                // $c7 = strtotime(date('Y-07-01 00:00:00'));
                // $c8 = strtotime(date('Y-08-01 00:00:00'));
                // $c9 = strtotime(date('Y-09-01 00:00:00'));
                // $c10 = strtotime(date('Y-10-01 00:00:00'));
                // $c11 = strtotime(date('Y-11-01 00:00:00'));
                // $c12 = strtotime(date('Y-12-01 00:00:00'));
                // $c13 = strtotime(date('Y-12-31 23:59:59'));
                $c1 = date('Y-01-01');
                $c2 = date('Y-02-01');
                $c3 = date('Y-03-01');
                $c4 = date('Y-04-01');
                $c5 = date('Y-05-01');
                $c6 = date('Y-06-01');
                $c7 = date('Y-07-01');
                $c8 = date('Y-08-01');
                $c9 = date('Y-09-01');
                $c10 = date('Y-10-01');
                $c11 = date('Y-11-01');
                $c12 = date('Y-12-01');
                $c13 = date('Y-12-31');
                $xiaoquid=$estate['id'];
                //一月
                $d1[] = ['complate_time','egt',$c1];
                $d1[] = ['complate_time','elt',$c2];
                $d1[] = ['estate_id','eq',$xiaoquid];
                $a1 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d1)->find();               
                $l1 = model('transaction_record')->where($d1)->count();  
                  
                if($l1==0){
                    $y1=0;
                }else{

                 $y1=$a1['cjprices']/$l1;
                }         
                $this->assign('y1',$y1);
                $this->assign('l1',$l1);
                //二月
                $d2[] = ['complate_time','egt',$c2];
                $d2[] = ['complate_time','elt',$c3];
                $d2[] = ['estate_id','eq',$xiaoquid];
                $a2 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d2)->find();
                $l2 = model('transaction_record')->where($d2)->count();  
                if($l2==0){
                    $y2=0;
                }else{

                 $y2=$a2['cjprices']/$l2;
                }            
                // $y2=$a2['cjprices']/$l2;
                $this->assign('y2',$y2);
                $this->assign('l2',$l2);
                //三月
                $d3[] = ['complate_time','egt',$c3];
                $d3[] = ['complate_time','elt',$c4];
                $d3[] = ['estate_id','eq',$xiaoquid];
                $a3 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d3)->find();
                $l3 = model('transaction_record')->where($d3)->count();     
                if($l3==0){
                    $y3=0;
                }else{

                 $y3=$a3['cjprices']/$l3;
                }  
                $this->assign('y3',$y3);
                $this->assign('l3',$l3);
                //四月
                $d4[] = ['complate_time','egt',$c4];
                $d4[] = ['complate_time','elt',$c5];
                $d4[] = ['estate_id','eq',$xiaoquid];
                $a4 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d4)->find();
                $l4 = model('transaction_record')->where($d4)->count();      
                if($l4==0){
                    $y4=0;
                }else{

                 $y4=$a4['cjprices']/$l4;
                }         
                $this->assign('y4',$y4);
                $this->assign('l4',$l4);
                //五月
                $d5[] = ['complate_time','egt',$c5];
                $d5[] = ['complate_time','elt',$c6];
                $d5[] = ['estate_id','eq',$xiaoquid];
                $a5 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d5)->find();
                $l5 = model('transaction_record')->where($d5)->count(); 
                if($l5==0){
                    $y5=0;
                }else{

                 $y5=$a5['cjprices']/$l5;
                }              
                $this->assign('y5',$y5);
                $this->assign('l5',$l5);
                //六月
                $d6[] = ['complate_time','egt',$c6];
                $d6[] = ['complate_time','elt',$c7];
                $d6[] = ['estate_id','eq',$xiaoquid];
                $a6 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d6)->find();
                $l6 = model('transaction_record')->where($d6)->count();   
                if($l6==0){
                    $y6=0;
                }else{

                 $y6=$a6['cjprices']/$l6;
                }            
                $this->assign('y6',$y6);
                $this->assign('l6',$l6);
                //七月
                $d7[] = ['complate_time','egt',$c7];
                $d7[] = ['complate_time','elt',$c8];
                $d7[] = ['estate_id','eq',$xiaoquid];
                $a7 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d7)->find();
                $l7 = model('transaction_record')->where($d7)->count();  
                if($l7==0){
                    $y7=0;
                }else{

                 $y7=$a7['cjprices']/$l7;
                }             
                $this->assign('y7',$y7);
                $this->assign('l7',$l7);
                //八月
                $d8[] = ['complate_time','egt',$c8];
                $d8[] = ['complate_time','elt',$c9];
                $d8[] = ['estate_id','eq',$xiaoquid];
                $a8 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d8)->find();
                $l8 = model('transaction_record')->where($d8)->count(); 
                if($l8==0){
                    $y8=0;
                }else{

                 $y8=$a8['cjprices']/$l8;
                }  
                $this->assign('y8',$y8);
                $this->assign('l8',$l8);
                //九月
                $d9[] = ['complate_time','egt',$c9];
                $d9[] = ['complate_time','elt',$c10];
                $d9[] = ['estate_id','eq',$xiaoquid];
                $a9 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d9)->find();
                $l9 = model('transaction_record')->where($d9)->count();    
                if($l9==0){
                    $y9=0;
                }else{

                 $y9=$a9['cjprices']/$l9;
                }  
                $this->assign('y9',$y9);
                $this->assign('l9',$l9);
                //十月
                $d10[] = ['complate_time','egt',$c10];
                $d10[] = ['complate_time','elt',$c11];
                $d10[] = ['estate_id','eq',$xiaoquid];
                $a10 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d10)->find();
                $l10 = model('transaction_record')->where($d10)->count();   
                if($l10==0){
                    $y10=0;
                }else{

                 $y10=$a10['cjprices']/$l10;
                }           
                
                $this->assign('y10',$y10);
                $this->assign('l10',$l10);
                //十一月
                $d11[] = ['complate_time','egt',$c11];
                $d11[] = ['complate_time','elt',$c12];
                $d11[] = ['estate_id','eq',$xiaoquid];
                $a11 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d11)->find();
                $l11 = model('transaction_record')->where($d11)->count();  
                if($l11==0){
                    $y11=0;
                }else{

                 $y11=$a11['cjprices']/$l11;
                }            
                
                $this->assign('y11',$y11);
                $this->assign('l11',$l11);
                //十二月
                $d12[] = ['complate_time','egt',$c12];
                $d12[] = ['complate_time','elt',$c13];
                $d12[] = ['estate_id','eq',$xiaoquid];
                $a12 = db('transaction_record')->field("sum(cjprice) as cjprices")->where($d12)->find();
                $l12 = model('transaction_record')->where($d12)->count(); 
                if($l12==0){
                    $y12=0;
                }else{

                 $y12=$a12['cjprices']/$l12;
                }             
                
                $this->assign('y12',$y12);
                $this->assign('l12',$l12);
     








             $this->assign('citys',$citys);
            $xqinfo=$estate['data'];
            $this->assign('info',$info);
           

            $this->assign('estate',$estate);
            $this->assign('xqinfo',$xqinfo);
            $this->assign('near_by_house',$this->getNearByHouse($info['lat'],$info['lng'],$info['city']));
            // $this->assign('same_price_house',$this->samePriceHouse($info->getData('price')));
            $this->assign('share_title',$share_title);
            $this->assign('storage_open',getSettingCache('storage','open'));
            $quyu = model('city')->where('id',$info['city'])->find();
      
      
      //print_r($info);
      //exit();
      
      
            $fpy=$info['contacts'];
            $this->assign('fpy',$fpy);
            $this->assign('quyu',$quyu);

            $qipai=$info['qipai'];
            $info['qipai']=number_format("$qipai",2,".","");
            $acreage=$info['acreage'];
            $info['acreage']=number_format("$acreage",2,".","");
        // print_r($lists);exit();
//zp
            header("Content-type:text/html;charset=utf-8");    //设置编码
 
            $time1=strtotime(date("Y-m-d H:i:s")); //获取当前时间
            $kptime=$info['kptime'];
            $time2=strtotime($kptime);
            $second = $time2-$time1;

            $day = floor($second/3600/24);    //倒计时还有多少天
            $hr = floor($second/3600%24);     //倒计时还有多少小时（%取余数）
            $min = floor($second/60%60);      //倒计时还有多少分钟
            $sec = floor($second%60);         //倒计时还有多少秒          
            $str = $day."天".$hr."小时".$min."分钟".$sec."秒";  //组合成字符串
            // echo $str;exit();


            $this->assign('str',$str);
            $this->assign('time2',$time2);
            $this->assign('kptime',$kptime);
            //同小区历史成交
            $xiaoqu=$info['estate_id'];
            $objs   = model('second_house');

            $objss   = model('second_house')->alias('s');
                $joinss  = [['estate m','m.id = s.estate_id']];
                
                
$field   = "s.*";
$field .= ',m.years';






            $tong =  model('second_house')->where('estate_id',$xiaoqu)->where('fcstatus',175)->where('status',1)->select();
            $tcou = $objs->where('estate_id',$xiaoqu)->where('fcstatus',175)->where('status',1)->count();
// print_r($tcou);exit();

            $this->assign('tong',$tong);
            $this->assign('tcou',$tcou);
            // print_r($tong);exit();

            $jilu =  model('transaction_record')->where('estate_id',$xiaoqu)->limit(5)->select();
            $this->assign('jilu',$jilu);
            $tcou1 = model('transaction_record')->where('estate_id',$xiaoqu)->count();
            $this->assign('tcou1',$tcou1);
            $yeares1 =  model('estate')->where('id',$xiaoqu)->field('years')->find();
            $yeares=$yeares1['years'];
            $this->assign('yeares',$yeares);
// print_r($yeares['years']);exit();
// print_r($tong);
// print_r($jilu);exit();
// $arr = array();
// foreach($tong as $k=>$r){
//    $arr[] = array_merge($arr,$jilu[$k]);
//      }
// print_r(array_merge($jilu,$tong));exit();

// $c = array_merge($tong,$jilu);
// print_r($c);exit();

            $infos = cookie('userInfo');
            $infos = \org\Crypt::decrypt($infos);
            $user_id  = $infos['id'];
            $follow   = model('follow');
            $guanzhu = $follow->where('house_id',$xiaoqu)->where('user_id',$user_id)->where('model','estate')->count();
            $this->assign('guanzhu',$guanzhu);
            $fangid=$info['id'];
            $gzfang = $follow->where('house_id',$fangid)->where('user_id',$user_id)->where('model','second_house')->count();
            // print_r($fangid);
            // echo "123";
            // print_r($user_id);
            // print_r($gzfang);exit();
            $this->assign('gzfang',$gzfang);
            $models = model('fydp')->where('house_id',$fangid)->where('model','second_house')->count();

                $this->assign('models',$models);
 $obj     = model('fydp')->alias('s');
                $join  = [['user m','m.id = s.user_id']];   


// $gl=$obj->join($join)->where('s.house_id',$fangid)->where('s.model','second_house')->group('s.user_id')->limit(3)->select();
             
                // $this->assign('gl',$gl);


                  $jjr = model('fydp')->where('house_id',$fangid)->where('user_id',$info['broker_id'])->group('user_id')->count();
                
              
                if($jjr==0){
                    $gl=$obj->join($join)->where('s.house_id',$fangid)->where('s.model','second_house')->group('s.user_id')->limit(3)->select();
                }else{
                    $gls=model('fydp')->alias('s')->join([['user m','m.id = s.user_id']])->where('s.house_id','eq',$fangid)->where('s.model','eq','second_house')->where('s.user_id','eq',$info['broker_id'])->group('s.user_id')->limit(1)->select();
                    // print_r($gls);
                    $this->assign('gls',$gls);
                    $gl=$obj->join($join)->where('s.house_id','eq',$fangid)->where('s.model','eq','second_house')->where('s.user_id','neq',$info['broker_id'])->group('s.user_id')->limit(2)->select();
                }
                $this->assign('jjr',$jjr);
                $this->assign('gl',$gl);
                // echo "11111111111111";
                // print_r($gl);

//zpend



// print_r($info['contacts']['contact_name']);exit();
$jjrname=$info['contacts']['contact_name'];
$jjrtel=$info['contacts']['contact_phone'];

//联系人客服

$jjrcontact=$info['online_consulting'];

//print_r($info['online_consulting']);

//exit();
 $this->assign('jjrcontact',$jjrcontact);

$members   = model('user');
// print_r($jjrname);
// print_r($jjrtel);exit();
$jjwhere['user_name']=$jjrname;
$jjwhere['mobile']=$jjrtel;
$jjwhere['model']=4;

$fpys = $members->where($jjwhere)->select();
// print_r($fpys[0]['lxtel']);
 $datas = model('second_house')->where(['id'=>$id])->find();

 $str=$datas['hximg'];
        // print_r($str);
        $var=explode(",",$str);
        
        $this->assign('var',$var);


$fpytel=$fpys[0]['lxtel'];
$this->assign('fpytel',$fpytel);

         $city=$info['city'];
              $lists = model('city')->field('id,pid,spid,name,alias')->where('id','eq',$city)->find();
              $spid=$lists['spid'];
              $city_name=$lists['name'];
                 if(substr_count($spid,'|')==2){
                    $listsss = model('city')->field('id,name')->where('id','eq',$lists['pid'])->find();
                    $shi=$listsss['name'];  
                    $citys=$shi.$city_name; 
                 }else{
                    $citys=$city_name;
                 }
// if(!empty($fpys['lxtel'])){
//     $info['contacts']['contact_phone']=$fpys['lxtel'];
// }
// print_r($info);exit();
// $citys=$info['city'];
$xiaoqu=$info['estate_id'];
$map = "estate_id=$xiaoqu or city=$city";
// print_r($map);exit();
       $txq = model('second_house')

                 ->where('status',1)

                 ->where('fcstatus','eq',170)

                 ->where('id','neq',$fangid)
                 ->where($map)

                 ->field('id,estate_id,title,estate_name,room,living_room,toilet,acreage,average_price,update_time,qipai,marketprice,kptime,fcstatus,price,img')
                ->order('id desc')

                 ->limit(4)

                 ->select();
 // print_r($txq);
              $txq_count=count($txq);

             $this->assign('txq',$txq);
            $this->assign('txq_count',$txq_count);

            $this->assign('same_price_house',$this->samePriceHouse($info->getData('price'),$txq_count,$fangid));
             $this->assign('sames_price_house',$this->samesPriceHouse());



        }else{
            return $this->fetch('public/404');
        }
        return $this->fetch();
    }
    /**
     * @param $page
     * @return array|\PDOStatement|string|\think\Collection|\think\Paginator
     * 获取房源列表
     */
    private function getLists($page = 0)
    {
        $time    = time();
        $where   = $this->search();
        
        $sort    = input('param.sort/d',0);
        $field   = "id,estate_id,title,city,iscj,fcstatus,estate_name,img,video,room,living_room,toilet,price,average_price,tags,address,acreage,orientations,renovation,update_time,endtime,qipai,marketprice,cjprice,kptime,pano_url";
        $obj     = model('second_house');
                // $join  = [['estate m','m.id = s.user_id']]; 
      // $where="iscj='159'";
        
        // if(!empty($_GET['cj'])){
        //     $cj=$_GET['cj'];
       //      $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
       //      $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        //     $obj     = $obj->where($where)->where('update_time','between',[$beginToday,$endToday])->field($field)->order($this->getSort($sort));

        // }
        // else{
        //     $obj     = $obj->where($where)->field($field)->order($this->getSort($sort));
      //   }
       // print_r($where);
		//print_r($obj);
		if((!empty($where['fcstatus'])) && ($where['fcstatus']==175)){
		
       $obj = $obj->where($where)->field($field)->order($this->getSort(8));
			 //print_r($obj);
       // print_r($page);
       //$page=1;
			if($page)
        {
		
		
		
			//echo 123;
            // print_r($this->pageSize);exit();
            $lists = $obj->where('top_time','lt',$time)->page($page)->limit($this->pageSize)->order(['endtime'=>'desc'])->select();
    		//$lists = $obj->where('fcstatus','eq',175)->order('endtime','desc')->select();
			//echo 111;
	
			//print_r($lists);
      
            $obj->removeOption();
            $count      = $obj->where($where)->where('top_time','lt',$time)->count();
            $total_page = ceil($count/$this->pageSize);
            $lists      = ['lists'=>$lists,'total_page'=>$total_page];

        }else{ 


			//echo 345;
// print_r($this->pageSize);
//print_r(2233333);
            $result = $obj->where('fcstatus','eq',175)->order('endtime','desc')->paginate(100);
			
			//print_r($obj);
			
			//print_r($result);
			

            $top    = $obj->removeOption()->where($where)->where('top_time','gt',$time)->field($field)->order(['endtime'=>'desc','top_time'=>'desc','id'=>'desc'])->select();
            $lists  = ['lists'=>$result,'top'=>$top];

        }
			
			 
		
		}else{
		
			$obj     = $obj->where($where)->field($field)->order($this->getSort($sort))->order(['fcstatus'=>'asc']);
			 if($page)
            {
                // print_r($page);exit();
                $lists = $obj->where('top_time','lt',$time)->page($page)->limit($this->pageSize)->select();
        		//echo 222;
          
                $obj->removeOption();
                $count      = $obj->where($where)->where('top_time','lt',$time)->count();
                $total_page = ceil($count/$this->pageSize);
                $lists      = ['lists'=>$lists,'total_page'=>$total_page];

            }else{ 
                $result = $obj->where('top_time','lt',$time)->paginate($this->pageSize);

                $top    = $obj->removeOption()->where($where)->where('top_time','gt',$time)->field($field)->order(['top_time'=>'desc','id'=>'desc','create_time'=>'desc'])->select();
                $lists  = ['lists'=>$result,'top'=>$top];

            }
			
		
		}
		
		
		
      //   $obj     = $obj->where($where)->field($field)->order($this->getSort($sort))->order(['fcstatus'=>'asc']);
      //   // print_r($obj);
      //   // print_r($cj);
        
      // //print_r($obj);exit();

      //   if($page)
      //   {
      //       // print_r($page);exit();
      //       $lists = $obj->where('top_time','lt',$time)->page($page)->limit($this->pageSize)->select();
    
      
      //       $obj->removeOption();
      //       $count      = $obj->where($where)->where('top_time','lt',$time)->count();
      //       $total_page = ceil($count/$this->pageSize);
      //       $lists      = ['lists'=>$lists,'total_page'=>$total_page];

      //   }else{ 
      //       $result = $obj->where('top_time','lt',$time)->paginate($this->pageSize);

      //       $top    = $obj->removeOption()->where($where)->where('top_time','gt',$time)->field($field)->order(['top_time'=>'desc','id'=>'desc','create_time'=>'desc'])->select();
      //       $lists  = ['lists'=>$result,'top'=>$top];

      //   }
        // print_r($lists);

        





        
        // print_r($lists);
         // foreach ($lists as $key => $value) {
         //         $lists[$key][data]['chajia']=intval($lists[$key][data]['price'])-intval($lists[$key][data]['cjprice']);
         //         print_r($lists[$key]['data']);
         //   }
         

    //echo 111;
    
        // $lists=$obj->order(['create_time'=>'desc'])->select();
    
        return $lists;
    }
    /**
     * @return array
     * 搜索条件
     */
    private function search()
    {

        $estate_id     = input('param.estate_id/d',0);//小区id
        $param['area'] = input('param.area/d', $this->cityInfo['id']);
        $param['tags']       = input('param.tags/d',0);
        $param['price']      = input('param.price',0);
        $param['acreage']    = input('param.acreage',0);//面积
        $param['room']       = input('param.room',0);//户型
        $param['type']       = input('param.type',0);//物业类型
        $param['renovation'] = input('param.renovation',0);//装修情况
        $param['sort']       = input('param.sort/d',0);//排序
        $param['area'] == 0 && $param['area'] = $this->cityInfo['id'];
        $data['status']    = 1;
        $keyword = input('get.keyword');




        if($estate_id)
        {
            $data['estate_id'] = $estate_id;
        }

        if(!empty($param['type']))
        {
            $data['house_type'] = $param['type'];
        }

        if(!empty($_GET['id'])){
            if(empty($param['type']))
            {
                $data['house_type'] = $_GET['id'];
            }
        }

        if(!empty($_GET['quyu'])){
            if(empty($param['type']))
            {
                $data['city'] = $_GET['quyu'];
            }
        }

        if(!empty($_GET['lx'])){
            $data['types'] = $_GET['lx'];

        }

        if(!empty($_GET['city'])){
        
            $data['city'] = $_GET['city'];
        }

        if(!empty($_GET['fcstatus'])){
            $data['fcstatus'] = $_GET['fcstatus'];

        }

        if(!empty($_GET['marketprice']))
        {
            $data['marketprice'] = $_GET['marketprice'];
        }
        





        if(!empty($_GET['cj'])){
            $data['fcstatus'] = $_GET['cj'];

        }

        if($param['renovation'])
        {
            $data['renovation'] = $param['renovation'];
        }

        if($keyword)
        {
            $param['keyword'] = $keyword;
            $data[] = ['title|estate_name','like','%'.$keyword.'%'];
        }

        if(!empty($param['tags'])){
            $data[] = ['','exp',\think\Db::raw("find_in_set({$param['tags']},tags)")];
        }

        if(!empty($param['area']))
        {
            $data[] = ['city','in',$this->getCityChild($param['area'])];
        }

        if(!empty($param['price']))
        {
            $data[] = getSecondPrice($param['price']);
        }

        if(!empty($param['room']))
        {
            $data[] = getRoom($param['room']);
        }

        if(!empty($param['acreage']))
        {
            $data[] = getAcreage($param['acreage']);
        }
         if(empty($_GET['quyu'])){
            $data[] = ['timeout','gt',time()];
        }
          

       
        
        
        return $data;
    }

    /**
     * @param $sort
     * @return array
     * 排序
     */
    private function getSort($sort)
    {

        switch($sort)
        {
            case 0:
                $order = ['fcstatus'=>'asc','ordid'=>'asc','id'=>'desc'];
                break;
            case 1:
                $order = ['fcstatus'=>'asc','price'=>'asc','id'=>'desc'];
                break;
            case 2:
                $order = ['fcstatus'=>'asc','price'=>'desc','id'=>'desc'];
                break;
            case 3:
                $order = ['fcstatus'=>'asc','average_price'=>'asc','id'=>'desc'];
                break;
            case 4:
                $order = ['fcstatus'=>'asc','average_price'=>'desc','id'=>'desc'];
                break;
            case 5:
                $order = ['fcstatus'=>'asc','acreage'=>'asc','id'=>'desc'];
                break;
            case 6:
                $order = ['fcstatus'=>'asc','acreage'=>'desc','id'=>'desc'];
                break;
            case 7:
                $order = ['fabutime'=>'desc','fcstatus'=>'asc','id'=>'desc'];
                break;
            case 8:
                $order = ['endtime'=>'desc','fcstatus'=>'asc','ordid'=>'asc','id'=>'desc'];
                break;
            default:
                $order = ['fcstatus'=>'asc','ordid'=>'asc','id'=>'desc'];
                break;
        }
        return $order;
    }
    /**
     * @param $lat
     * @param $lng
     * @param int $city
     * @return array|\PDOStatement|string|\think\Collection
     * 附近房源
     */
    private function getNearByHouse($lat,$lng,$city = 0)
    {
        $obj = model('second_house');
        if($lat && $lng){
            $point      = "*,ROUND(6378.138*2*ASIN(SQRT(POW(SIN(({$lat}*PI()/180-lat*PI()/180)/2),2)+COS({$lat}*PI()/180)*COS(lat*PI()/180)*POW(SIN(({$lng}*PI()/180-lng*PI()/180)/2),2)))*1000) as distance";
            $bindsql    = $obj->field($point)->buildSql();
            $fields_res = 'id,title,price,estate_name,orientations,city,tags,room,living_room,acreage,img,distance';
            $lists      = $obj->table($bindsql.' d')->field($fields_res)->where('status',1)->where('timeout','gt',time())->where('distance','<',2000)->limit(4)->select();
        }else{
            $where['status'] = 1;
            $city && $where['city'] = $city;
            $where[] = ['timeout','gt',time()];
            $lists = $obj->where($where)->field('id,title,estate_name,city,tags,orientations,price,room,living_room,acreage,img')->limit(4)->select();
        }
        return $lists;
    }
    /**
     * @param $price
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 价格相似房源
     */
    private function samePriceHouse($price,$num,$infoid)
    {

           $num1=4-$num;

        $min_price = $price > 10 ? $price - 10:$price;
        $max_price = $price + 10;
        $lists = model('second_house')
            ->where('status',1)
            ->where('price','between',[$min_price,$max_price])
            ->where('city','in',$this->getCityChild())
            ->where('timeout','gt',time())
            ->where('id','neq',$infoid)
            ->field('id,estate_id,title,img,estate_name,city,tags,orientations,room,living_room,toilet,acreage,average_price,update_time,qipai,marketprice,kptime,price,fcstatus')
            ->order('create_time desc')
            ->limit($num1)
            ->select();
            // print_r($lists);
        return $lists;
    }
    

    // 捡漏房源
     private function samesPriceHouse($num = 4)

    {

        //$min_price = $price - 10;

        //$max_price = $price + 10;

        $lists = model('second_house')

                ->where('status',1)

                ->where('marketprice',5)
                
                ->where('fcstatus',170)

                ->where('city','in',$this->getCityChild())

                ->where('timeout','gt',time())

                ->field('id,estate_id,title,img,estate_name,room,living_room,toilet,jieduan,types,acreage,price,qipai,average_price,marketprice,kptime,fcstatus')

                ->order('create_time desc')

                ->limit($num)

                ->select();
// print_r($lists);
        return $lists;

    }









}
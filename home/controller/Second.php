<?php
namespace app\home\controller;

use app\common\controller\HomeBase;
use app\common\service\Metro;
use app\home\service\IndexServer;
use app\home\service\SecondServer;
use app\home\service\UserService;
use app\server;
use think\facade\Log;
use think\Request;


class Second extends HomeBase{
    public function index()



    {
        $result = $this->getLists();




        $lists  = $result['lists'];


        $arr=$this->request->param();





        if(!empty($arr['keyword'])){

// print_r($arr['keyword']);

            $keywords = $arr['keyword'];

        }else{

            $keywords='';

        }


        $this->assign('keywords',$keywords);

        $this->assign('metro',Metro::index($this->cityInfo['id']));//地铁线



        $this->assign('house_type',getLinkMenuCache(9));//类型



        $this->assign('orientations',getLinkMenuCache(4));//朝向

        $this->assign('floor',getLinkMenuCache(7));//朝向

        $this->assign('types',getLinkMenuCache(26));//类型s

        $this->assign('jieduan',getLinkMenuCache(25));//类型s

        $this->assign('fcstatus',getLinkMenuCache(27));//类型s

        $this->assign('renovation',getLinkMenuCache(8));//装修情况



        $this->assign('tags',getLinkMenuCache(14));//标签



        $this->assign('area',$this->getAreaByCityId());



        $this->assign('position',$this->getPositionHouse(5,4));

        // $result = $this->getLists();





        // $lists  = $result['lists'];



        $this->assign('lists',$lists);



        $this->assign('pages',$lists->render());



        $this->assign('top_lists',$result['top']);



        $this->assign('storage_open',getSettingCache('storage','open'));

        return $this->fetch();



    }
    /**
     * @param mixed
     * @return mixed
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index_新版(){
        $result = $this->getLists();
        $lists  = $result['lists'];
        $arr=$this->request->param();
        if(!empty($arr['keyword'])){
            $keywords = $arr['keyword'];
        }else{
            $keywords='';
        }
        $IndexServer= new IndexServer();
        $SecondServer= new SecondServer();
        //问答
        $answer = model('article')->field('id,title,hits,create_time')->where('cate_id',10)->cache('answer',3600)->order('hits desc')->limit(5)->select();
        $hot_news = model('article')->field('id,title')->where('cate_id','neq',10)->cache('hot_news',3600)->order('hits desc')->limit(5)->select();
        $area = input('param.area/d', $this->cityInfo['id']);
//        if ($area < 57){
//            $area=0;
//        }
        $quality_estate =$IndexServer->get_quality_estate(10);
        $list_page_search_field = $SecondServer->list_page_search_field($area);
        $userInfo = login_user();
        $waist_bunner = $IndexServer->get_home_banner_arr(19,1);
        if (!empty($waist_bunner)) {
            $waist_bunner = $waist_bunner[0];
        }
        $this->assign('waist_bunner',$waist_bunner );//平铺第三个广告
        $this->assign('answer',$answer);//问答
        $this->assign('userInfo',$userInfo);//用户信息
        $this->assign('hot_news',$hot_news);
        $this->assign('quality_estate',$quality_estate);//推荐小区
        $this->assign('list_page_search_field',json_encode($list_page_search_field));//列表页搜索栏数据
        $this->assign('keywords',$keywords);
        $this->assign('metro',Metro::index($this->cityInfo['id']));//地铁线
        $this->assign('house_type',getLinkMenuCache(9));//类型
        $this->assign('orientations',getLinkMenuCache(4));//朝向
        $this->assign('floor',getLinkMenuCache(7));//朝向
        $this->assign('types',getLinkMenuCache(26));//类型s
        $this->assign('jieduan',getLinkMenuCache(25));//阶段
        $this->assign('fcstatus',getLinkMenuCache(27));//状态
        $this->assign('renovation',getLinkMenuCache(8));//装修情况
        $this->assign('tags',getLinkMenuCache(14));//标签
        $this->assign('area',$this->getAreaByCityId());//区域
        $this->assign('position',$this->getPositionHouse(5,4));
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('top_lists',$result['top']);
        $this->assign('storage_open',getSettingCache('storage','open'));
//        dd(1);
        return $this->fetch();
    }

    public function detail_新版(){
        $second_house_id = input('param.id/d',0);
        if($second_house_id){
            $server = new server();
            $SecondServer = new SecondServer();
            //增加围观次数
            db('second_house')->where('id','=',$second_house_id)->setInc('weiguan');
            //second_house详情
            $info = $server->second_model($second_house_id);
            if($info){
                //添加浏览量
                updateHits($info['id'],'second_house');
                //小区详情
                $estate = $server->estate($info['estate_id']);
                $this->assign('info',$info);
                //法拍专员信息
                $user = $server->user($second_house_id,$info['broker_id']);
                $user_info = $server->user_info($second_house_id,$info['broker_id']);
                $this->assign('user',$user);
                $this->assign('user_info',$user_info);
                //本小区拍卖套数
                $estate_num = $SecondServer->estate_second_num($second_house_id,$info['estate_name']);
                $this->assign('estate_num',$estate_num);
                $estate_id=$info['estate_id'];
                //小区的所有房源
//                $estate_seconf = $SecondServer->estate_second($estate_id,10);
//                $this->assign('estate_seconf',$estate_seconf);
                //拍卖成交记录
                $jilu1 =  model('transaction_record')->where('estate_id',$estate_id)->cache("transaction_record_".$estate_id,84000)->select();
                $this->assign('jilu1',$jilu1);

                //法拍专员点评/点评个数
                $second_house_user_comment = $SecondServer->second_house_user_comment($second_house_id);;
                $second_house_user_comment_num= count($second_house_user_comment->toArray());
                $this->assign('second_house_user_comment_num',$second_house_user_comment_num);
                $this->assign('second_house_user_comment',$second_house_user_comment);
                //房源特色标签
                $house_characteristic= $SecondServer->get_house_characteristic($info['xsname'],$info['jieduan_name'],$info['marketprice'],
                    $info['is_commission'],$info['is_school'],$info['is_metro']);
                $this->assign('house_characteristic',$house_characteristic);
                //用户信息
                $infos = cookie('userInfo');
                $infos = \org\Crypt::decrypt($infos);
                $this->assign('login_user',$infos);
                $this->assign('login_user_json',json_encode($infos));
                //获取是否推荐 和 登录手机号
                $userInfo = $this->getUserInfo();
                //获取拍卖成交记录
                $jilu1 =  model('transaction_record')->where('estate_id',$estate_id)->limit('0,5')->select();
                $this->assign('jilu1',$jilu1);
                $jilu =  model('transaction_record')->where('estate_id',$estate_id)->limit('5,200')->select();
                $this->assign('jilu',$jilu);


                //获取根据本房源-推荐房源
                $recommend_house = $SecondServer->get_recommend_house($info['city'],$second_house_id,$estate_id);
                $user_id  = $infos['id'];
                $gzfang = model('follow')->where('house_id',$second_house_id)->where('user_id',$user_id)->where('model','second_house')->count();
                $this->assign('gzfang',$gzfang);
                $follow   = model('follow');
                $guanzhu = $follow->where('house_id',$estate_id)->where('user_id',$user_id)->where('model','estate')->count();
                $api =new Api();
                $house_loan_s = $api->house_loan_s(30,$info['qipai'],'4.9',$info['acreage'],65);
                $house_loan['benxi'] =   $house_loan_s['res'][0]['benxi'];
                $house_loan['shoufu'] =   $house_loan_s['info']['shoufu'];
                $house_loan['qishui'] =   $house_loan_s['info']['qishui_price'];
                $house_loan['daikuan'] =   $house_loan_s['info']['dakuan_price'];
                $seo['title'] = $info['title'].'法拍二手房信息_房拍网法拍房房源栏目';
                $seo['keys']  = $info['title'].'法拍房二手房信息';
                $seo['desc']  = '提供'.$info['title'].'房屋起拍价、户型大小、特色、周边医院公交等法拍二手房信息。';
                $this->assign('seo',$seo);

                $this->assign('house_loan',json_encode($house_loan));
                $this->assign('guanzhu',$guanzhu);
                $this->assign('userInfo',$userInfo);
                $this->assign('recommend_house',$recommend_house);
                $this->assign('estate',$estate);
            }else{
                return $this->fetch('public/404');
            }
        }else{
            return $this->fetch('public/404');
        }
        return $this->fetch();
    }

    public function extension(){
        $extension_id= input('param.id/d',1);
        $second_house_extension =  model('second_house_extension')->where([['id','=',$extension_id],['status','=',1]])->find();
        $field   = "s.id,s.title,s.estate_id,s.estate_name,s.chajia,s.junjia,s.marketprice,s.city,s.video,s.total_floor,s.floor,s.img,s.qipai,s.pano_url,s.room,s.living_room,s.toilet,s.price,s.cjprice,s.average_price,s.tags,s.address,s.acreage,s.orientations,s.renovation,s.user_type,s.contacts,s.update_time,s.kptime,s.jieduan,s.fcstatus,s.types,s.onestime,s.oneetime,s.oneprice,s.twostime,s.twoetime,s.twoprice,s.bianstime,s.bianetime,s.bianprice,s.is_free";
        $obj     = model('second_house')->alias('s');
        $lists ="";
        if (!empty($second_house_extension->val)){
            $second_house_extension_arr = explode(',',$second_house_extension->val);
            $where[] =[$second_house_extension->key,'in',$second_house_extension_arr];
            $where[] = ['s.fcstatus','in',[169,170]];
            $cache_name =  "second_house_extension_".$second_house_extension->key;
            $lists = $obj->field($field)->where($where)->limit($second_house_extension->limit)
//                ->cache($cache_name,86401)
                ->order('kptime asc')->select();
        }
        $this->assign('lists',$lists);
        $this->assign('info',$second_house_extension);
        return $this->fetch();
    }


    /**

     * @return mixed



     * 二手房列表



     */









    /**



     * @return mixed



     * 房源详情



     */
    public function detail()

    {



        $id = input('param.id/d',0);

        $wg = model('second_house')->where('id',$id)->find();


        $wgs=$wg['weiguan']+1;

        model('second_house')->where(['id'=>$id])->update(['weiguan'=>$wgs]);

        if($id)



        {



            $where['h.id']     = $id;



            $where['h.status'] = 1;



            $obj  = model('second_house');



            $join = [['second_house_data d','h.id=d.house_id']];



            $info = $obj->alias('h')->join($join)->where($where)->find();
            if($info)



            {

                $info['file'] = json_decode($info['file'],true);
//                $this->setSeo($info);
                $seo['title'] = $info['title'].'法拍二手房信息_房拍网法拍房房源栏目';


                $seo['keys']  = $info['title'].'法拍房二手房信息';

                $seo['desc']  = '提供'.$info['title'].'房屋起拍价、户型大小、特色、周边医院公交等法拍二手房信息。';
                $this->assign('seo',$seo);


                updateHits($info['id'],'second_house');

//dd($info);
//
                $estate = model('estate')->where('id',$info['estate_id'])->find();

                //二手房价格走势
                $xiaoquid =$info['estate_id'];

                $etr =model('estate_transaction_record')->where('estate_id',$info['estate_id'])->select();
                for ($i=1;$i<13;$i++){
                    $y= 'y'.$i;
                    $l= 'l'.$i;
                    $res_y=$res_l =0;
                    foreach ($etr as $val){
                        if ($i == $val['month']){
                            $res_y =$val['unit_price'];
                            $res_l =$val['num'];
                        }
                    }
                    $this->assign($y,$res_y);
                    $this->assign($l,$res_l);
                }






                $info['total']        = $obj->where('estate_id',$info['estate_id'])->where('status',1)->count();



                $info['rental_total'] = model('rental')->where('estate_id',$info['estate_id'])->where('status',1)->count();



                // $info['junjia']=ceil(intval($info['qipai'])/intval($info['acreage']));










                $info['junjia']=sprintf("%.2f",intval($info['qipai'])/intval($info['acreage'])*10000);

                // print_r($info['qipai']);

                // print_r($info['price']);exit();

                $info['chajia']=intval($info['price'])-intval($info['qipai']);







                $xsname = model('linkmenu')->where('id',$info['types'])->find();



                $info['xsname']=$xsname['name'];



                //print_r($info['price']/$info['qipai']);

                $info['cjprice']=sprintf("%.2f",$info['cjprice']);



                //exit();

                // print_r($info);

                $this->assign('info',$info);

                // print($info['broker_id']);

                $jieduans = model('linkmenu')->where('id',$info['jieduan'])->find();

                // print_r($jieduans);

                $this->assign('jieduans',$jieduans);

                $pinglun = model('user')->where('id',$info['broker_id'])->find();

                // print_r($jieduans);

                $this->assign('pinglun',$pinglun);

                // print_r($pinglun);

                $nianxian = model('user_info')->where('user_id',$info['broker_id'])->find();

                // print_r($jieduans);

                $this->assign('nianxian',$nianxian);



                $counts = model('second_house')->where('estate_name',$info['estate_name'])->count();

                $this->assign('counts',$counts);



                $xiaoqu=$info['estate_id'];

                $objs   = model('second_house');



                $objss   = model('second_house')->alias('s');

                $joinss  = [['estate m','m.id = s.estate_id']];





                $field   = "s.*,s.endtime";

                $field .= ',m.years';



                $tong1 = $objs->where('estate_id',$xiaoqu)->where('fcstatus',175)->where('status',1)->limit('0,5')->select();



                $tong = $objs->where('estate_id',$xiaoqu)->where('fcstatus',175)->where('status',1)->limit('5,200')->select();

                $count = count($tong);





                $tcou = $objs->where('estate_id',$xiaoqu)->where('fcstatus',175)->where('status',1)->count();

                $this->assign('tcou',$tcou);

                $this->assign('tong',$tong);

                $this->assign('tong1',$tong1);





                $jilu1 =  model('transaction_record')->where('estate_id',$xiaoqu)->limit('0,5')->select();

                $this->assign('jilu1',$jilu1);

// print_r($jilu1);

                $jilu =  model('transaction_record')->where('estate_id',$xiaoqu)->limit('5,200')->select();

                $this->assign('jilu',$jilu);

// print_r($jilu);exit();

                $tcou1 = model('transaction_record')->where('estate_id',$xiaoqu)->count();

                $this->assign('tcou1',$tcou1);

                $yeares1 =  model('estate')->where('id',$xiaoqu)->field('years')->find();

                $yeares=$yeares1['years'];

                $this->assign('yeares',$yeares);





                $allcount=$tcou+$tcou1;

                $this->assign('allcount',$allcount);











// print_r($tcou);exit();



                $qipai=$info['qipai'];

                $info['qipai']=number_format("$qipai",2,".","");

                $acreage=$info['acreage'];

                $info['acreage']=number_format("$acreage",2,".","");





                // $userInfo = $this->getUserInfo();



                $infos = cookie('userInfo');

                $infos = \org\Crypt::decrypt($infos);

                $this->assign('pdfpy',$infos);

                $user_id  = $infos['id'];

                $follow   = model('follow');

                $guanzhu = $follow->where('house_id',$xiaoqu)->where('user_id',$user_id)->where('model','estate')->count();

                $this->assign('guanzhu',$guanzhu);
                $fangid=$info['id'];

                $gzfang = $follow->where('house_id',$fangid)->where('user_id',$user_id)->where('model','second_house')->count();

                //获取是否推荐 和 登录手机号
                $userInfo = $this->getUserInfo();
                $this->assign('gzfang',$gzfang);
                $this->assign('userInfo',$userInfo);

                $models = model('fydp')->where('house_id',$fangid)->where('model','second_house')->count();



                $this->assign('models',$gzfang);
                $ud = model('fydp')->where('house_id',$fangid)->where('model','second_house')->group('user_id')->select();

                $obj     = model('fydp')->alias('s');

                $join  = [['user m','m.id = s.user_id']];
                $jjr = model('fydp')->where('house_id',$fangid)->where('user_id',$info['broker_id'])->group('user_id')->count();

                $gl=$obj->join($join)->where('s.house_id',$fangid)->where('s.model','second_house')->group('s.user_id')->limit(3)->select();

//               $common =  model('fydp')->where('house_id',$fangid)->where('status',0)->limit(3)->select();
//                if($jjr==0){
//                    $gl=$obj->join($join)->where('s.house_id',$fangid)->where('s.model','second_house')->group('s.user_id')->limit(3)->select();
//                }else{
//
//                    $gls=model('fydp')->alias('s')->join([['user m','m.id = s.user_id']])->where('s.house_id','eq',$fangid)->where('s.model','eq','second_house')->where('s.user_id','eq',$info['broker_id'])->group('s.user_id')->limit(1)->select();
//
//                    // print_r($gls);
//
//                    $this->assign('gls',$gls);
//
//                    $gl=$obj->join($join)->where('s.house_id','eq',$fangid)->where('s.model','eq','second_house')
//                        ->where('s.user_id','neq',$info['broker_id'])->group('s.user_id')->limit(2)->select();
//
//                }

                $count_gl = count($gl);

                // $gl=$obj->join($join)->where('s.house_id',$fangid)->where('s.model','second_house')->group('s.user_id')->limit(3)->select();

                // print_r($gl);

//                 $this->assign('gls',$gls);

//                $this->assign('common',$common);
                $this->assign('jjr',$jjr);

                $this->assign('gl',$gl);
                $this->assign('count_gl',$count_gl);

                $this->assign('ud',$ud);





                $citys=$info['city'];
                $map = "estate_id=$xiaoqu or city=$citys";

                // print_r($citys);

                // print_r($xiaoqu);exit();

                $txq = model('second_house')



                    ->where('status',1)



                    ->where('fcstatus','eq',170)



                    ->where('id','neq',$info['id'])



                    ->where($map)



                    ->field('id,title,room,living_room,toilet,acreage,fcstatus,price,img')



                    ->order('id desc')



                    ->limit(4)





                    ->select();



                $txq_count=count($txq);
                if ($txq_count < 4){
                    $txq = model('second_house')
                        ->where('status',1)
                        ->where('fcstatus','eq',170)
                        ->where('id','neq',$info['id'])
                        ->where("estate_id=$xiaoqu or city=$citys or id > 0")
                        ->field('id,title,room,living_room,toilet,acreage,fcstatus,price,img')
                        ->order('id desc')
                        ->limit(4)
                        ->select();
                    $txq_count=count($txq);
                }
                // print_r($txq);



                $this->assign('txq_count',$txq_count);





                // print_r($txq);

                // print_r($guanzhu);exit();

                // $estateid=$info['estate_id'];

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

                // $v['city'] = getCityName($info['city']);

                // var_dump($v['city']);

                $this->assign('citys',$citys);





                $this->assign('estate',$estate);



                $this->assign('txq',$txq);



                $this->assign('storage_open',getSettingCache('storage','open'));



                $this->assign('near_by_house',$this->getNearByHouse($info['lat'],$info['lng'],$info['city']));







                $this->assign('same_price_house',$this->samePriceHouse($info->getData('price'),$txq_count,$info['id']));


                $this->assign('sames_price_house',$this->samesPriceHouse());









            }else{



                return $this->fetch('public/404');



            }



        }else{



            return $this->fetch('public/404');



        }



        return $this->fetch();



    }







    /**

     * @return mixed|string

     * 获取用户信息

     */

    private function getUserInfo()

    {

        $info = cookie('userInfo');

        $info = \org\Crypt::decrypt($info);

        return $info;

    }



    /**
     * @return array
     * 获取列表
     */
    private function getLists(){
        $time    = time();
        $where   = $this->search();
        $sort    = input('param.sort/d',0);
        $keyword = input('get.keyword');
        $field   = "s.id,s.title,s.estate_id,s.estate_name,s.chajia,s.junjia,s.marketprice,s.city,s.video,s.total_floor,s.floor,s.img,s.qipai,s.pano_url,s.room,s.living_room,s.toilet,s.price,s.cjprice,s.average_price,s.tags,s.address,s.acreage,s.orientations,s.renovation,s.user_type,s.contacts,s.update_time,s.kptime,s.jieduan,s.fcstatus,s.types,s.onestime,s.oneetime,s.oneprice,s.twostime,s.twoetime,s.twoprice,s.bianstime,s.bianetime,s.bianprice,s.is_free";
        $obj     = model('second_house')->alias('s');
        //二手房列表
        if(isset($where['m.metro_id']) || isset($where['m.station_id'])){
            //查询地铁关联表
            $field .= ',m.metro_name,m.station_name,m.distance';
            $join  = [['metro_relation m','m.house_id = s.id']];
            $lists = $obj->join($join)->where($where)->where('m.model','second_house')->where('s.top_time','lt',$time)->field($field)->group('s.id')->order($this->getSort($sort))->paginate(30,false,['query'=>['keyword'=>$keyword]]);
        }else{
            if($sort==8){
                $lists   = $obj->where($where)->where('s.top_time','lt',$time)->where('s.fcstatus','neq',169)->field($field)->order($this->getSort($sort))->paginate(30,false,['query'=>['keyword'=>$keyword]]);
            }else if($sort==7){
                $lists   = $obj->where($where)->where('s.top_time','lt',$time)->where('s.fcstatus','eq',170)->field($field)->order($this->getSort($sort))->paginate(30,false,['query'=>['keyword'=>$keyword]]);
            }else{
                $lists   = $obj->where($where)->where('s.top_time','lt',$time)->field($field)->order($this->getSort($sort))->paginate(30,false,['query'=>['keyword'=>$keyword]]);
            }
        }
        if($lists->currentPage() == 1){
            //二手房置顶列表
            $obj = $obj->removeOption()->alias('s');
            //关联地铁表
            if(isset($where['m.metro_id']) || isset($where['m.station_id'])){
                $field .= ',m.metro_name,m.station_name,m.distance';
                $join  = [['metro_relation m','m.house_id = s.id']];
                $obj->join($join)->where('m.model','second_house')->group('s.id');
            }
            $top   = $obj->field($field)->where($where)->where('top_time','gt',$time)->order(['timeout'=>'desc','id'=>'desc'])->select();
        }else{
            $top   = false;
        }
            foreach ($lists as $key => $value) {
                $estate_id=$lists[$key]['estate_id'];
                $sql=model('estate')->where('id','eq',$estate_id)->alias('years')->find();
                $years=$sql['years'];
                $lists[$key]['years']=$years;
                $city_id=$lists[$key]['city'];
                $sqls = model('city')->field('id,pid,spid,name,alias')->where('id','eq',$city_id)->find();
                $spid=$sqls['spid'];
                $city_name=$sqls['name'];
                 if(substr_count($spid,'|')==2){
                    $listsss = model('city')->field('id,name')->where('id','eq',$sqls['pid'])->find();
                    $shi=$listsss['name'];
                    $lists[$key]['city']=$shi.$city_name;
                 }else{
                    $lists[$key]['city']=$city_name;
                 }
                $lists[$key]['jieduan_name']=getLinkMenuName(25,$lists[$key]['jieduan']);;
                $lists[$key]['types_name'] =getLinkMenuName(26,$lists[$key]['types']);
                $lists[$key]['chajia']=intval($lists[$key]['price'])-intval($lists[$key]['qipai']);
            }
        return ['lists'=>$lists,'top'=>$top];
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



        $min_price = $price - 10;



        $max_price = $price + 10;





          

        $lists = model('second_house')



                ->where('status',1)



                ->where('price','between',[$min_price,$max_price])



                ->where('city','in',$this->getCityChild())



                ->where('timeout','gt',time())



                ->where('id','neq',$infoid)



                ->field('id,title,img,room,living_room,toilet,jieduan,types,fcstatus,acreage,price')



                ->order('create_time desc')



                ->limit($num1)



                ->select();

                // print_r($lists);

       

        return $lists;



    }

	

	

	 /**



     * @param $price



     * @param int $num



     * @return array|\PDOStatement|string|\think\Collection



     * 漏检房源



     */



    private function samesPriceHouse($num = 4)



    {



        //$min_price = $price - 10;



        //$max_price = $price + 10;



        $lists = model('second_house')



                ->where('status',1)



//                ->where('marketprice',5)

				

				->where('fcstatus',170)



                ->where('city','in',$this->getCityChild())



                ->where('timeout','gt',time())



                ->field('id,title,img,room,living_room,toilet,jieduan,types,fcstatus,acreage,price,qipai,marketprice,kptime,fcstatus')
                ->order(['marketprice'=>'desc'])
                ->orderRand()






                ->limit($num)



                ->select();



        return $lists;



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



            $fields_res = 'id,title,price,room,types,jieduan,fcstatus,living_room,toilet,acreage,img,distance';



            $lists      = $obj->table($bindsql.' d')->field($fields_res)->where('status',1)->where('distance','<',2000)->where('timeout','gt',time())->limit(3)->select();

        }else{



            $where['status'] = 1;



            $city && $where['city'] = $city;



            $where[] = ['timeout','gt',time()];



            $lists = $obj->where($where)->field('id,title,price,room,types,jieduan,fcstatus,living_room,toilet,acreage,img')->limit(3)->select();



        }



        return $lists;



    }



    /**



     * @param $pos_id @推荐位id



     * @param int $num @读取数量



     * @return array|\PDOStatement|string|\think\Collection



     * 获取推荐位楼盘



     */



    private function getPositionHouse($pos_id,$num = 6)



    {



        $service = controller('common/Position','service');



        $service->field   = 'h.id,h.img,h.title,h.price,h.estate_name,h.room,h.types,h.jieduan,h.fcstatus,h.living_room,h.toilet,h.acreage,h.city';



        $service->city    = $this->getCityChild();



        $service->cate_id = $pos_id;



        $service->model   = 'second_house';



        $service->num     = $num;



        $lists = $service->lists();







        return $lists;



    }



    /**



     * @return array



     * 搜索条件



     */



    private function search(){
        $estate_id     = input('param.estate_id/d',0);//小区id
        $param['area'] = input('param.area/d', $this->cityInfo['id']);
//        dd($param);
        $param['rading']     = 0;
        $param['tags']       = input('param.tags/d',0);
        $param['qipai']      = input('param.qipai',0);
        $param['acreage']    = input('param.acreage',0);//面积
        $param['room']       = input('param.room',0);//户型
        $param['types']       = input('param.types',0);//户型
        $param['jieduan']       = input('param.jieduan',0);//户型
        $param['fcstatus']       = input('param.fcstatus',0);//状态
        $param['type']       = input('param.type',0);//物业类型
        $param['renovation'] = input('param.renovation',0);//装修情况
        $param['metro']      = input('param.metro/d',0);//地铁线
        $param['metro_station'] = input('param.metro_station/d',0);//地铁站点
        $param['sort']          = input('param.sort/d',0);//排序
        $param['is_free']          = input('param.is_free/d',0);//自由购
        $param['orientations']  = input('param.orientations/d',0);//朝向
        $param['user_type']  = input('param.user_type/d',0);//1个人房源  2中介房源
        $param['area'] == 0 && $param['area'] = $this->cityInfo['id'];
        $param['search_type']   = input('param.search_type/d',1);//查询方式 1按区域查询 2按地铁查询
        $param['time_frame']   = input('param.time_frame',0);//查询时间
        $data['s.status']    = 1;

        //获取当前请求的参数
        $arr=$this->request->param();
        if(!empty($arr['keyword'])){
            $keyword = $arr['keyword'];
        }else{
            $keyword = input('get.keyword');
        }
        //显示街道时
        if ($param['area']  > 57){
            $param['street'] =$param['area'];
        }
        if(!empty($_GET['rec_position'])){
            $rec_position=$_GET['rec_position'];
        }
        $zprice1 =0;
        $param['zprice1']=$_GET['zprice1'] ??"";
        $param['zprice2']=$_GET['zprice2'] ?? "";
        $param['zmianji1']=$_GET['zmianji1'] ?? "";
        $param['zmianji2']=$_GET['zmianji2'] ?? "";
        if(!empty($_GET['zprice1'])){
            $zprice1=$_GET['zprice1'];
        }
        if(!empty($_GET['zprice2'])){
            $zprice2=$_GET['zprice2'];
        }
        $zmianji1 =0;
        if(!empty($_GET['zmianji1'])){
            $zmianji1=$_GET['zmianji1'];
        }
        if(!empty($_GET['zmianji2'])){
            $zmianji2=$_GET['zmianji2'];
        }
        $seo_title = '';
        if($estate_id) {
            $data['s.estate_id'] = $estate_id;
            $estate_name = model('estate')->where('id',$estate_id)->value('title');
            $seo_title .= '_'.$estate_name.'二手房';
        }
        if(!empty($param['type'])){
            $data['s.house_type'] = $param['type'];
            $seo_title .= '_'.getLinkMenuName(9,$param['type']);
        }
        if(!empty($param['user_type'])){
            $data['s.user_type'] = $param['user_type'];
        }
        if(!empty($param['orientations'])){
            $data['s.orientations'] = $param['orientations'];
            $seo_title .= '_'.getLinkMenuName(4,$param['orientations']).'朝向';
        }
        if($param['renovation']){
            $data['s.renovation'] = $param['renovation'];
            $seo_title .= '_'.getLinkMenuName(8,$param['renovation']);
        }
        if($keyword){
            if(!empty($_GET['type'])){
                $house_type=$_GET['type'];
                $param['types']=$house_type;
            }
            $param['keyword'] = $keyword;
            $data[] = ['s.title','like','%'.$keyword.'%'];
            $seo_title .= '_'.$keyword;
        }

        if($param['search_type'] == 2) {
            if(!empty($param['metro'])){
                $data['m.metro_id'] = $param['metro'];
                $seo_title .= '_地铁'.Metro::getMetroName($param['metro']);
                $this->assign('metro_station',Metro::metroStation($param['metro']));
            }else{
                $data[] = ['s.city','in',$this->getCityChild()];
            }
            if(!empty($param['metro_station'])){
                $data['m.station_id'] = $param['metro_station'];
                $seo_title .= '_'.Metro::getStationName($param['metro_station']);
            }
        }else{
            if(!empty($param['area'])){
                $data[] = ['s.city','in',$this->getCityChild($param['area'])];
                $rading = $this->getRadingByAreaId($param['area']);
                //读取商圈
                $param['rading'] = 0;
                if($rading && array_key_exists($param['area'],$rading)){
                    $param['rading']  = $param['area'];
                    $param['area']    = $rading[$param['area']]['pid'];
                }
                $param['area']!=$this->cityInfo['id'] && $seo_title .= '_'.getCityName($param['area'],'').'二手房';
                $this->assign('rading',$rading);
            }
        }
        if(!empty($param['qipai'])) {
            $data[] = getSecondPrice($param['qipai'],'s.qipai');
            $qipai  = config('filter.second_qipai');
            isset($qipai[$param['qipai']]) && $seo_title .= '_'.$qipai[$param['qipai']]['name'];
        }
        if(!empty($param['room'])){
            $data[] = getRoom($param['room'],'s.room');
            $room   = config('filter.room');
            isset($room[$param['room']]) && $seo_title .= '_'.$room[$param['room']];
        }
        if(!empty($param['types'])){
            $data['s.types'] = $param['types'];
            $seo_title .= '_'.getLinkMenuName(26,$param['types']);
        }
        if(!empty($param['jieduan'])) {
            $data['s.jieduan'] = $param['jieduan'];
            $seo_title .= '_'.getLinkMenuName(25,$param['jieduan']);
        }
        if(!empty($param['fcstatus'])) {
            $data['s.fcstatus'] = $param['fcstatus'];
            $seo_title .= '_'.getLinkMenuName(27,$param['fcstatus']);
        }
        if(!empty($param['acreage'])){
            $data[] = getAcreage($param['acreage'],'s.acreage');
            $acreage = config('filter.acreage');
            isset($acreage[$param['acreage']]) && $seo_title .= '_'.$acreage[$param['acreage']]['name'];
        }
        if(!empty($param['tags'])){
            $data[] = ['','exp',\think\Db::raw("find_in_set({$param['tags']},s.tags)")];
            $seo_title .= '_'.getLinkMenuName(14,$param['tags']);
        }
        $data[] = ['s.timeout','gt',time()];
        //是否是自由购
        if(!empty($param['is_free'])){
            $data['s.is_free'] = $param['is_free'];
        }
        if(!empty($_GET['zprice2'])){
            $data[] = ['s.qipai','between',[$zprice1,$zprice2]];
        }
        if(!empty($_GET['rec_position'])){
            $data[] = ['rec_position','eq',1];
        }
        if(!empty($_GET['zmianji2'])){
            $data[] = ['s.acreage','between',[$zmianji1,$zmianji2]];
        }
        $search = $param;
        $seo_title  = trim($seo_title,'_');
        $seo_title && $this->setSeo(['seo_title'=>$seo_title,'seo_keys'=>str_replace('_',',',$seo_title)]);
        unset($param['rading']);
        $data = array_filter($data);
        $this->assign('search',$search);
        if(!empty($_GET['rec_position'])){
            $param['rec_position']=$_GET['rec_position'];
        }
        if(!empty($_GET['zprice1'])){
            $param['zprice1']=$_GET['zprice1'];
        }
        if(!empty($_GET['zprice2'])){
            $param['zprice2']=$_GET['zprice2'];
        }
        if(!empty($_GET['zmianji1'])){
            $param['zmianji1']=$_GET['zmianji1'];
        }
        if(!empty($_GET['zmianji2'])){
            $param['zmianji2']=$_GET['zmianji2'];
        }
        $this->search_arr(array_filter($param));

        $this->assign('param',$param);
        return $data;
    }

    function search_arr($param){
        $res =[];
        //区域
        if ($param['area'] && $param['area'] != 39){
            $area = getCityName($param['area'],',');
            if ($area){
                $ex_area = explode(',',$area);
                $res[] =$ex_area[1];
            }
        }
        //商圈
        if (!empty($param['street'])){
            $street = getCityName($param['street'],',');
            if ($street){
                $ex_street = explode(',',$street);
                $res[] =$ex_street[2];
            }
        }
        //形式
        if (!empty($param['type'])){
            $res[] = getLinkMenuName(9,$param['type']);
        }
        //类型
        if (!empty($param['types'])){
            $res[] = getLinkMenuName(26,$param['types']);
        }
        //总价
        if (!empty($param['qipai'])){
            $qipai  = config('filter.second_qipai');
            $res[] =$qipai[$param['qipai']]['name'];
        }
        //总价
        if (!empty($param['acreage'])){
            $acreage  = config('filter.acreage');
            $res[] =$acreage[$param['acreage']]['name'];
        }
        //户型
        if (!empty($param['room'])){
            $room = config('filter.room');
            $res[] =$room[$param['room']];
        }
        //阶段
        if (!empty($param['jieduan'])){
            $res[] = getLinkMenuName(25,$param['jieduan']);
        }
        //状态
        if (!empty($param['fcstatus'])){
            $res[] = getLinkMenuName(27,$param['fcstatus']);
        }
        $this->assign('selected_nav',json_encode($res));
    }






    /**



     * @param $sort



     * @return array



     * 排序



     */



    private function getSort($sort){
        switch($sort) {
            case 0:
                $order = ['fcstatus'=>'asc','ordid'=>'asc','id'=>'desc'];
                break;
            case 1:
                $order = ['price'=>'asc','id'=>'desc'];
                break;
            case 2:
                $order = ['price'=>'desc','id'=>'desc'];
                break;
            case 3:
                $order = ['average_price'=>'asc','id'=>'desc'];
                break;
            case 4:
                $order = ['average_price'=>'desc','id'=>'desc'];
                break;
            case 5:
                $order = ['acreage'=>'asc','id'=>'desc'];
                break;
            case 6:
                $order = ['acreage'=>'desc','id'=>'desc'];
                break;
            case 7:
                $order = ['fabutimes'=>'desc','ordid'=>'desc','id'=>'desc'];
                break;
            case 8:
                $order = ['marketprice'=>'desc'];
                break;
            case 9:
                $order = ['rec_position'=>'desc','fcstatus'=>'asc','marketprice'=>'desc'];
                break;
            default:
                $order = ['ordid'=>'asc','id'=>'desc'];
                break;
        }
        return $order;
    }
    
    
    /**
     * @description 获取房源点评
     * @param $second_house_id 房ID
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @auther xiaobin
     */
    public function anotherPerson()
    {
        $code = 200;
        $msg = "success";
        $second_house_id = input("post.second_house_id");
        $onReq = input("post.onReq");
        if ($second_house_id > 0) {
            //法拍专员点评/点评个数
            $onReq = $onReq ==0 || $onReq==1 ? 0: ($onReq-1)*2;
            $second_house_user_comment     = model('user')->alias('s')->join([['user_info info','info.user_id = s.id']])
                ->field('s.id,s.nick_name,s.lxtel,info.history_complate,s.kflj')->where([['s.kflj','neq',''],['model','=',4]])
                ->group('s.id')->limit($onReq,2)
                ->cache('another_'.$onReq,'1800')
                ->select();
            foreach ($second_house_user_comment as &$house){
                $house['avatar'] = getAvatar($house->id,90);
            }
        } else {
            $code = 500;
            $msg = "error";
            $second_house_user_comment = array();
        }
        return json([
            "code" => $code,
            "msg" => $msg,
            "data" => $second_house_user_comment
        ]);
    }
}
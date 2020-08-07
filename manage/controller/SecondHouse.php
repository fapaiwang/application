<?php

namespace app\manage\controller;
//namespace PHPExcel;
//use think\Loader;
use \app\common\controller\ManageBase;
use app\home\service\ToolsServer;
use app\manage\service\SecondHouseService;
use app\manage\service\Synchronization;
use think\Config;
use think\Db;
use think\facade\Log;
use app\common\service\ImageServer;


class SecondHouse extends ManageBase

{

    private $model = 'second_house';

    protected $beforeActionList = [

        'beforeEdit' => ['only'=>['edit']],

        'beforeAdd' => ['only'=>['add']],

        'beforeIndex' => ['only'=>['index']]

    ];

    public function initialize(){

        parent::initialize();

        $storage = getSettingCache('storage');

        $this->assign('storage',$storage);

    }

    protected function beforeIndex()

    {
        $save_id = input('get.id') ?? 0;
        $list=model('second_house')->order('fabutimes desc')->select();
        $this->sort = ['fabutimes'=>'desc'];
        $user = model('user')->field('id,nick_name')->where('model',4)->select();
        $this->assign('save_id',$save_id);
        $this->assign('user_info',$user);
        $this->assign('list',$list);
    }

    /**

     * @return array

     * 搜索条件

     */

    public function search()

    {

        $status  = input('get.status');

        $jieduan  = input('get.jieduan');

        $keyword = input('get.keyword');

        $types = input('get.types');

        $fcstatus = input('get.fcstatus');

        $user = input('get.user');

        $audit_status = input('get.audit_status');

        $rec_position = input('get.rec_position');

        $where   = [];

        is_numeric($status) && $where['status'] = $status;
        is_numeric($jieduan) && $where['jieduan'] = $jieduan;
        is_numeric($types) && $where['types'] = $types;
        is_numeric($fcstatus) && $where['fcstatus'] = $fcstatus;
        is_numeric($rec_position) && $where['rec_position'] = $rec_position;
        is_numeric($user) && $where['broker_id'] = $user;
        is_numeric($audit_status) && $where['audit_status'] = $audit_status;

        $keyword && $where[] = ['title','like','%'.$keyword.'%'];

        $data = [
            'status' => $status,
            'keyword'=> $keyword,
            'user'=> $user,
            'types'=> $types,
            'fcstatus'=> $fcstatus,
            'rec_position'=> $rec_position,
            'jieduan'=> $jieduan,
            'audit_status'=> $audit_status
        ];

        $this->queryData = $data;
// print_r($data);exit();
        $this->assign('search',$data);

        return $where;

    }

    public function beforeAdd()
    {
$fpy = db('user')->where(['model'=>4])->cache('user_name_asc')->order('reg_time asc')->select();

// print_r($fpy);
        $position_lists = \app\manage\service\Position::lists($this->model);
$this->assign('fpy',$fpy);
        $this->assign('position_lists',$position_lists);

    }

    public function beforeEdit()

    {

        $id  = input('param.id/d',0);

        if(!$id)

        {

            $this->error('参数错误');

        }

        $data = model('second_house_data')->where(['house_id'=>$id])->find();

        $position_lists = \app\manage\service\Position::lists($this->model);

        $house_position_cate_id = \app\manage\service\Position::getPositionIdByHouseId($id,$this->model);

        $datas = model('second_house')->where(['id'=>$id])->find();
        $str=$datas['hximg'];

        // print_r($str);
        $var=explode(",",$str);
// print_r($var);

        $this->assign('var',$var);
        $fpy = db('user')->where(['model'=>4])->cache('user_name_asc')->order('reg_time asc')->select();
        $this->assign('fpy',$fpy);

        $this->assign('position_lists',$position_lists);

        $this->assign('position_cate_id',$house_position_cate_id);

        $data['check_file'] = json_encode($data['file']);
        $this->assign('data',$data);

    }

    /**

     * 添加
     * application\manage\view\second_house\add.html

     */

    public function addDo()

    {
        $data = input('post.');
        //给图片打水印star
        $ImageServer = new ImageServer();
        //缩略图
        if($data['img']!=''){
            $ImageServer->ImageWater('../public'.$data['img'],'../public/static/shuiyin/ppshuiyin.png',10);
        }
        //房源图片
        if(isset($data['pic'])){
            if(!empty($data['pic'])){
                foreach($data['pic'] as $k=>$v){
                    $ImageServer->ImageWater('../public'.$v['pic'],'../public/static/shuiyin/ppshuiyin.png',10);
                }
            }
        }
        //给图片打水印end

        //同步到法拍网
        $hr_tid =$data['bianhao'] ?? "";
        $fa_tags =$data['tags'] ?? "";
        $img_url =$data['img'] ?? "";
        $pic_url=$data['pic'] ?? "";

        $ext_attr =0; //庭室
        $house_type =0; //户型
        //转换户型
        if (!empty($data['toilet'])){
            $ext_attr =$this->fa_ext_attr($data['toilet'],'ext_attr');
            $house_type =$this->fa_ext_attr($data['toilet'],'house_type');
        }


        //查询房产所在区域/街道 id name spid
        $sz=new Synchronization();
        if (!empty($data['city'])){
            $address = explode(' ',$sz->get_city($data['city']));
            //用房拍网的地址 对比
            $fa_city=$address[0];
            $fa_area=$address[1];
            $fa_street=$address[2];
            $fa_city=Db::connect('db2')->name('region')->where('name',$fa_city)->find();
            $fa_area=Db::connect('db2')->name('region')->where('name',$fa_area)->find();
            $fa_street=Db::connect('db2')->name('region')->where('name',$fa_street)->find();
        }

        // 同步数据到房拍网 用途
        $fa_years =$fang_estate_title="";
        if (!empty($data['estate_id'])){
            $fa_estate = db('estate')->where('id',$data['estate_id'])->find();
            $fa_years = $fa_estate['years'].'年';
            $fang_estate_title = $fa_estate['title'];
        }
        //查询法拍网小区id
        $fa_community_title =Db::connect('db2')->name('community')->where('title',$fang_estate_title)->find();
        $bid = $ext_attrs = $face =0;

        if(!empty($data['types'])){
            $user = $this->fa_types($data['types']);
        }
        //特色标签拆分为 （分类 阶段 分类）

        if (!empty($fa_tags)){
            $fa_bid = $this->fa_bid($fa_tags,'bid');
            if (!empty($fa_bid)){
                $ex_bid = explode(',',$fa_bid);
                $bid = $ex_bid[0];
//                $user = $ex_bid[1];
            }
        }
        //转换朝向
        if (!empty($data['orientations'])){
            $face =$this->fa_face($data['orientations']);
        }
//        //阶段
        $fa_jieduan =0;
        if (!empty($data['jieduan'])){
           $fa_jieduan = $this->fa_jieduan($data['jieduan']);
        }
        //结束时间
        $fa_etime =946684800;
        if (!empty($data['jieduan'])){
            if ($data['jieduan'] == 161 && $data['oneetime']){
                $fa_etime =strtotime(date($data['oneetime'])) ?? "";
                $data['termination_datetime'] = $data['oneetime'];

            }elseif ($data['jieduan'] == 162 && $data['twoetime']){
                $fa_etime =strtotime(date($data['twoetime'])) ?? "";
                $data['termination_datetime'] = $data['twoetime'];

            }elseif ($data['jieduan'] == 163 && $data['bianetime']){
                $fa_etime =strtotime(date($data['bianetime'])) ?? "";
                $data['termination_datetime'] = $data['bianetime'];
            }
        }
        if($data['fcstatus']==175){
            if(!empty($data['endtime'])){
                $data['termination_datetime'] = $data['endtime'];
            }else{
                $data['termination_datetime'] = '';
            }
        }
        //print_r($data);die;
        //面积
        $fa_acreage =$price=0;
        if (!empty($data['acreage'])){
            $fa_acreage = $this->fa_acreage($data['acreage']);
        }
        //市场价
        $fa_price =0;
        if (!empty($data['price'])){
            $fa_price = $this->fa_price($data['price']);
        }
        $fa_ext_attr = $fa_price.','.$fa_acreage.','.$ext_attr.','.$fa_jieduan;
        //拍卖状态 状态 房拍多了两个
        $fa_status ="";
        if (!empty($data['fcstatus'])){
            $fa_status = $this->fa_status($data['fcstatus']);
        }
        //编号
        $fa_hr_tid =0;
        if (!empty($hr_tid)){
            $fa_hr_tid = $this->fa_hr_tid($hr_tid);
        }
        //列表图片移动
        if(!empty($img_url)){
            $this->fa_mv_img($img_url);
        }
//        static/pics/
        //图片集移动
        $fang_pic_serialize ="";
        if (!empty($pic_url)){
            $fang_pic =[];
            foreach ($pic_url as $k=>$value){
                $fang_pic_name =substr($value['pic'],24);
                $fang_pic[$k] =$fang_pic_name;
                $fang_img_url =substr($value['pic'],1);
                copy($fang_img_url,'../../www.fapaiwang.cn/static/pics/'.$fang_pic_name);
            }
            $fang_pic_serialize = serialize($fang_pic);
        }

        //法拍网同步结束

        $result = $this->validate($data,'SecondHouse');//调用验证器验证

        $code   = 0;

        $msg    = '';

        $obj = model('second_house');


         $data['fabutimes']=strtotime($data['fabutime'] ?? "");



        if(true !== $result) {

            // 验证失败 输出错误信息

            $this->error($result);

        }else{

            \think\Db::startTrans();

            try{

                !empty($data['map']) && $location = explode(',',$data['map']);

               // $data['house_type'] = isset($data['house_type']) ? implode(',',$data['house_type']) : 0;

                $data['lng']     = isset($location[0]) ? $location[0] : 0;

                $data['lat']     = isset($location[1]) ? $location[1] : 0;

                $data['tags'] = isset($data['tags']) ? implode(',',$data['tags']) : 0;

                $data['average_price'] = 0;

                $data['online_consulting']=$data['online_consulting'];


                //$data['marketprice']=$data['price'];



            $marketprices=$data['price'];
            $qipaiprice=$data['qipai'];
            $jlzs=round($marketprices/$qipaiprice,1);
        // print_r($qipaiprice);

            //print_r($jlzs);
            if($jlzs<'1.1'){

                $data['marketprice']='0';

            }

            if(($jlzs>='1.1') && ($jlzs<='2')){

                $data['marketprice']='1';

            }
            if(($jlzs>='1.3') && ($jlzs<='1.4')){

                $data['marketprice']='2';

            }
            if(($jlzs>='1.5') && ($jlzs<='1.6')){

                $data['marketprice']='3';

            }
            if(($jlzs>='1.7') && ($jlzs<='1.8')){

                $data['marketprice']='4';

            }

            if($jlzs>'1.8'){

                $data['marketprice']='5';

            }
            //如果是推荐房型,无需计算,直接5星
            if (!empty($data['rec_position'])){
                $data['marketprice']='5';
            }




                $suiji=rand(1000,9999);
           // $bianhao=$data['bianhao'];
        if(!empty($data['bianhao'])){

             $data['bianhao']=$data['bianhao'].$suiji;
        }else
        {
             $data['bianhao']='T'.$suiji;
        }



          if (!empty($data['toilet'])) {
             $yyy= model('linkmenu')->field('name')->where('id',$data['toilet'])->where('menuid',29)->find();
             // print_r($yyy);
                 if(strpos($yyy['name'],'一室')!==false){
                    $data['room']=1;
                 }

                 else if (strpos($yyy['name'],'两室')!==false){
                     $data['room']=2;
                 }

                  else if (strpos($yyy['name'],'三室')!==false){
                     $data['room']=3;
                 }
                  else if (strpos($yyy['name'],'四室')!==false){
                     $data['room']=4;
                 }


                 else if (strpos($yyy['name'],'五室')!==false){
                     $data['room']=5;
                 }
                 else{
                     $data['room']=6;
                 }


          }

        if(empty($data['fabutime'])){
          // echo "12333";exit();
          $data['fabutime']=date("Y-m-d h:i:s",time());
        }
        if(!empty($data['shu'])){
            $aaa=$data['shu']+1;
            for ($n=0; $n<$aaa; $n++) {
                $hximg[] = request()->file('row'.$n);
            }
            foreach ($hximg as $key => $hximgs) {
                 $info = $hximgs->move(env('root_path'). 'public/uploads/secondhouse');
                 if($info){

                      $image= $info->getSaveName();
                      $bbb[]='/uploads/secondhouse/'.$image;
                 }
            }
            if($aaa=='1'){
                $bbb=$bbb[0];
            }
            if($aaa=='2'){
                $bbb=$bbb[0].','.$bbb[1];
            }
            if($aaa=='3'){
                $bbb=$bbb[0].','.$bbb[1].','.$bbb[2];
            }
            if($aaa=='4'){
                $bbb=$bbb[0].','.$bbb[1].','.$bbb[2].','.$bbb[3];
            }
            if($aaa=='5'){
                $bbb=$bbb[0].','.$bbb[1].','.$bbb[2].','.$bbb[3].','.$bbb[4];
            }
                $data['hximg']=$bbb;
        }
        //给图片打水印star
        //户型图
        if(isset($data['hximg'])){
            if(!empty($data['hximg'])){
                $water_hximg = explode(",",$data['hximg']);
                foreach($water_hximg as $k=>$v){
                    $ImageServer->ImageWater('../public'.$v,'../public/static/shuiyin/ppshuiyin.png',10);
                }
            }
        }
        //给图片打水印end
              //  dd($data['hximg']);
        if (!empty($_FILES['hxsimg']['name'])) {
            $hxsimg = request()->file('hxsimg');
            if($hxsimg){
                 $info = $hxsimg->move(env('root_path'). 'public/wj');
                 if($info){
                      $image= $info->getSaveName();
                    $data['hxsimg']='/wj/'.$image;
                 }
            }
        }




//                if (isset($data['rec_position'])) {
//
//                    $data['rec_position'] = 1;
//
//                }

                $data['file'] = $this->getPic();


                (empty($data['img']) && !empty($data['file'])) && $data['img'] = $data['file'][0]['url'];

                (empty($data['imgs']) && !empty($data['file'])) && $data['imgs'] = $data['file'][0]['url'];

                $fpyid=$data['contacts']['contact_name'];
                $fpys = db('user')->where(['id'=>$fpyid])->find();
                $data['contacts']['contact_name']=$fpys['user_name'];
                $data['contacts']['contact_phone']=$fpys['mobile'];
                $data['online_consulting']=$fpys['online_consulting'];
                if($fpyid==16){
                    $data['broker_id']=0;
                }else{
                    $data['broker_id']=$fpyid;
                }

                $data['qipai']=$data['qipai'];
                if($data['qipai']>0 && $data['acreage']>0)

                {
                 $data['average_price'] =sprintf("%.2f",intval($data['qipai'])/intval($data['acreage'])*10000);

                }

                //同步管理员 //默认管理员
                $fa_admin =2;
                if (!empty($fpys['user_name'])) {
                    if ($fpys['user_name'] =='管理员'){
                        $fpys['user_name'] ="admin";
                    }
                    $fa_admin=Db::connect('db2')->name('admin')->where('username',$fpys['user_name'])->find();
                }

                //户型图 新添加的户型图
                $fa_hximg ="";
                if (!empty($data['hximg'])){
                    $ex_hximg = explode(',',$data['hximg']);
                    $fa_hximg =$ex_hximg[0];
                    $this->fa_mv_img($fa_hximg[0]);
                }

                if($obj->allowField(true)->save($data))
                {
                    $house_id = $obj->id;

                    $fa_qianmfei =$data['qianfei'] ?? "";
                    $fa_xiaci = $data['xiaci'] ?? "";
                    $fa_qianmfei_xiaci = $fa_qianmfei."".$fa_xiaci;
                    $arr =[
                        'title'=>$data['title'] ?? "",
                        'bid'=> $bid, //10住宅  11商业  12别墅  13国有资产
                        'simg'=>$data['img'] ?? "", //列表图片 = 缩略图
                        'ord'=>$data['ordid'] ?? 10,//权重
                        'is_recom'=>$data['rec_position'] ?? 0 ,
//            'seoTitle'=>$data['seo_title'],//seo
//            'seoKeyword'=>$data['seo_keys'],
//            'setDescription'=>$data['position'],
                        'addtime'=>strtotime(date($data['fabutime'])) ?? time(),
                        'imgs'=>$fang_pic_serialize ?? "", //图片集 == 房源图片
                        'price'=>$data['qipai'] ?? "",
                        'per_price'=>$data['average_price'],
                        'market'=>$data['price'] ?? "",
                        'pick'=>$data['marketprice'],//
                        'house_type'=>$house_type,// $data['toilet']//户型
                        'floor'=>$data['floor'] ?? "",
                        'face'=>$face, //朝向 得转成 发牌网 全向问题
                        'uses'=>$user,
                        'tot_area'=>$data['acreage'] ?? "",
                        'build'=>$fa_years,//建成时间
                        'district'=>$data['estate_name'] ?? "",
                        'property'=>'',//产权 40 50 70
                        'margin'=>$data['baozheng'] ?? "",
                        'stime'=>strtotime(date($data['kptime'])) ?? "",
                        'etime'=>$fa_etime,//起拍截至时间
                        'team_id'=>$fa_admin['id'],//管理员id
                        'ext_attr'=>$fa_ext_attr, //价格，面积，户型，阶段
                        'status'=>$fa_status,
                        'm_con_1'=>$fa_qianmfei_xiaci,
                        'city'=>$fa_city['id'],//城市(北京)
                        'regionId'=>$fa_area['id'],//区域(东城)
                        'region_chId'=>$fa_street['id'],//街道(东单)
                        'longitude'=>$data['lng'],
                        'latitude'=>$data['lat'],
                        'address'=>$data['address'] ?? "",
                        'stat_ord'=>$fa_status,
                        'hr_tid'=>$fa_hr_tid,
                        'tot_floor'=>$data['total_floor'] ?? "",
                        'h_map'=>$fa_hximg,//户型图
                        'n_con'=>$data['info'] ?? "",//pc拍卖公告
                        'f_price'=>$data['cjprice'] ?? "",
                        'f_time'=>strtotime(date($data['endtime']))?? "",
                        'com_id'=>$fa_community_title['id'],//所在小区(id)
                        'm_n_con'=>$data['info'] ?? "",//手机拍卖公告
                        'vr_url'=>$data['pano_url'] ?? "",
                        'hr_code'=>rand(100000,999999),//房源编号
                        'fang_second_house_id'=>$house_id,//房拍网 房源id
                        'is_free'=>$data['is_free']  ?? 2,//是否自由购

                    ];

                    model('show')->allowField(true)->save($arr);


                    $data['basic_info_details'] ="";
                    $this->optionHouseData($house_id,$data);

                    $this->addHousePrice($house_id,$data['average_price']);

                    \app\manage\service\Price::calculationPrice($data['estate_id']);

                    \app\manage\service\Position::option($house_id,$this->model);

                    $msg = '添加房源信息成功';

                    $code = 1;

                    //关联学校

                    \org\Relation::addSchool('second_house',$data['lng'],$data['lat'],$house_id,$data['city']);

                    //关联地铁站

                    \org\Relation::addMetro('second_house',$data['lng'],$data['lat'],$house_id,$data['city']);

                }else{

                    $msg = '添加房源信息失败！';

                }

                \think\Db::commit();

            }catch(\Exception $e){

                \think\facade\Log::record('添加房源信息出错：'.$e->getFile().$e->getLine().$e->getMessage());

                \think\Db::rollback();

                 $msg = $e->getMessage();

            }

        }

        if($code == 1)

        {

            $this->success($msg);

        }else{

            $this->error($msg);

        }

    }



    /**

     * 编辑

     */

    public function editDo()

    {
        $data = input('post.');
        //给图片打水印star
        $ImageServer = new ImageServer();
        //缩略图
        if($data['img']!=''){
            if($data['img_check']==''||$data['img_check']!=$data['img']){
                $ImageServer->ImageWater('../public'.$data['img'],'../public/static/shuiyin/ppshuiyin.png',10);
            }
        }
        unset($data['img_check']);
        //房源图片
        $check_file = array();
        if($data['check_file']!=''){
            $check_file = json_decode($data['check_file'],true);
            if(!empty($check_file)){
                foreach($check_file as $k=>$v){
                    $check_file[$k] = $v['url'];
                }
            }
        }
        if(isset($data['pic'])){
            if(!empty($data['pic'])){
                $data_pic = $data['pic'];
                if(!empty($check_file)){
                    foreach($data_pic as $k=>$v){
                        if(in_array($v['pic'],$check_file)){
                            unset($data_pic[$k]);
                        }
                    }
                }
                foreach($data_pic as $k=>$v){
                    $ImageServer->ImageWater('../public'.$v['pic'],'../public/static/shuiyin/ppshuiyin.png',10);
                }
            }
        }
        unset($data['check_file']);
        //给图片打水印end


        if (strpos($data['refer'],'?')){
            $data['refer'] = substr($data['refer'],0,strripos($data['refer'],"?"));
            $data['refer'] = $data['refer'].'?id='.$data['id'];
        }else{
            $data['refer'] = $data['refer'].'?id='.$data['id'];
        }
        $shs = new SecondHouseService();
        //添加房源的基本信息
        $basic_info ="";
        $data['basic_info_details'] =$data['basic_info'] ?? "";
        if (!empty($data['basic_info'])){
            $basic_info =   $shs->basic_info($data['basic_info']);
        }
        $data['basic_info'] =$basic_info;





        //添加小区历史成交平均价格

        $estate_info =$shs->get_estate_record($data['estate_name']);
        $estate_info['estate_id'] =$data['estate_id'];
        $estate_info['estate_name'] =$data['estate_name'];
        $estate_info['year'] =date('Y');
        $fetr = model('estate_transaction_record')->where('estate_id',$data['estate_id'])->where('month',$estate_info['month'])->find();
        if (!empty($fetr)){
            model('estate_transaction_record')->allowField(true)->save($estate_info,['id'=>$fetr['id']]);
        }else{
            model('estate_transaction_record')->allowField(true)->save($estate_info);
        }
        $data['is_commission'] =  $data['is_commission'] ?? 0;
        $data['is_school'] =  $data['is_school'] ?? 0;
        $data['is_metro'] =  $data['is_metro'] ?? 0;


        //同步到法拍网
        $hr_tid =$data['bianhao'] ?? "";
        $fa_tags =$data['tags'] ?? "";
        $img_url =$data['img'] ?? "";
        $pic_url=$data['pic'] ?? "";
        $data['is_free'] =  $data['is_free'] ?? 0;
        $data['rec_position'] =  $data['rec_position'] ?? 0;
        $ext_attr =0; //庭室
        $house_type =0; //户型
        //转换户型
        if (!empty($data['toilet'])){
            $ext_attr =$this->fa_ext_attr($data['toilet'],'ext_attr');
            $house_type =$this->fa_ext_attr($data['toilet'],'house_type');
        }

        //结束时间
        $fa_etime =946684800;
        if (!empty($data['jieduan'])){
            if ($data['jieduan'] == 161 && $data['oneetime']){
                $fa_etime =strtotime(date($data['oneetime'])) ?? "";
                $data['termination_datetime'] = $data['oneetime'];

            }elseif ($data['jieduan'] == 162 && $data['twoetime']){
                $fa_etime =strtotime(date($data['twoetime'])) ?? "";
                $data['termination_datetime'] = $data['twoetime'];

            }elseif ($data['jieduan'] == 163 && $data['bianetime']){
                $fa_etime =strtotime(date($data['bianetime'])) ?? "";
                $data['termination_datetime'] = $data['bianetime'];
            }
        }
        if($data['fcstatus']==175){
            if(!empty($data['endtime'])){
                $data['termination_datetime'] = $data['endtime'];
            }else{
                $data['termination_datetime'] = '';
            }
        }
        //print_r($data);die;
        $sz=new Synchronization();
        if (!empty($data['city'])){
            $address = explode(' ',$sz->get_city($data['city']));
            //用房拍网的地址 对比
            $fa_city=$address[0];
            $fa_area=$address[1];
            $fa_street=$address[2];
            $fa_city=Db::connect('db2')->name('region')->where('name',$fa_city)->find();
            $fa_area=Db::connect('db2')->name('region')->where('name',$fa_area)->find();
            $fa_street=Db::connect('db2')->name('region')->where('name',$fa_street)->find();
        }
        // 同步数据到房拍网 用途
        $fa_years =$fang_estate_title="";
        if (!empty($data['estate_id'])){
            $fa_estate = db('estate')->where('id',$data['estate_id'])->find();
            $fa_years = $fa_estate['years'].'年';
            $fang_estate_title = $fa_estate['title'];
        }


        //查询法拍网小区id
        $fa_community_title =Db::connect('db2')->name('community')->where('title',$fang_estate_title)->find();
        $bid = $ext_attrs = $face =0;

        if ($data['types']){
            $user = $this->fa_types($data['types']);
        }
        //特色标签拆分为 （分类 阶段 分类）

        if (!empty($fa_tags)){
            $fa_bid = $this->fa_bid($fa_tags,'bid');
            if (!empty($fa_bid)){
                $ex_bid = explode(',',$fa_bid);
                $bid = $ex_bid[0];
//                $user = $ex_bid[1];
            }
        }
        //转换朝向
        if (!empty($data['orientations'])){
            $face =$this->fa_face($data['orientations']);
        }
//        //阶段
        $fa_jieduan =0;
        if (!empty($data['jieduan'])){
            $fa_jieduan = $this->fa_jieduan($data['jieduan']);
        }
        //面积
        $fa_acreage =$price=0;
        if (!empty($data['acreage'])){
            $fa_acreage = $this->fa_acreage($data['acreage']);
        }
        //市场价
        $fa_price =0;
        if (!empty($data['price'])){
            $fa_price = $this->fa_price($data['price']);
        }
        $fa_ext_attr = $fa_price.','.$fa_acreage.','.$ext_attr.','.$fa_jieduan;
        //拍卖状态 状态 房拍多了两个
        $fa_status ="";
        if (!empty($data['fcstatus'])){
            $fa_status = $this->fa_status($data['fcstatus']);
        }
        //编号
//        $fa_hr_tid =0;
//        if (!empty($hr_tid)){
//            $fa_hr_tid = $this->fa_hr_tid($hr_tid);
//        }
        //列表图片移动
        if(!empty($img_url)){
            $this->fa_mv_img($img_url);
        }
//        static/pics/
        //图片集移动
        $fang_pic_serialize ="";
        if (!empty($pic_url)){
            $fang_pic =[];
            foreach ($pic_url as $k=>$value){
                $fang_pic_name =substr($value['pic'],24);
                $fang_pic[$k] =$fang_pic_name;
                $fang_img_url =substr($value['pic'],1);
                copy($fang_img_url,'../../www.fapaiwang.cn/static/pics/'.$fang_pic_name);
            }
            $fang_pic_serialize = serialize($fang_pic);
        }

        //二拍和变卖 在重新拍卖时清除报名人数
        if($data['jieduan'] == 162 && $data['fcstatus'] == 170){
            $data['bmrs'] = 0;
        }
        if($data['jieduan'] == 163 && $data['fcstatus'] == 170){
            $data['bmrs'] = 0;
        }
        //法拍网同步结束
        //print_r($data);exit();
        // $data['online_consulting']=$data['online_consulting'];
        //$data['marketprice']=$data['price'];

        $data['fabutimes']=strtotime($data['fabutime']);
        if (!empty($data['rec_position'])){
            $data['marketprice']='5';
        }
        $result = $this->validate($data,'SecondHouse');//调用验证器验证

        $code   = 0;

        $msg    = '';

        $obj = model('second_house');

        $url = null;

        isset($data['refer']) && $url = $data['refer'];

        if(true !== $result)

        {

            // 验证失败 输出错误信息

            $this->error($result);

        }elseif(!$data['id']){

            $this->error('参数错误');

        }else{

            \think\Db::startTrans();

            try{

                !empty($data['map']) && $location = explode(',',$data['map']);

                //$data['house_type'] = isset($data['house_type']) ? implode(',',$data['house_type']) : 0;

                $data['lng']     = isset($location[0]) ? $location[0] : 0;

                $data['lat']     = isset($location[1]) ? $location[1] : 0;

                $data['tags'] = isset($data['tags']) ? implode(',',$data['tags']) : 0;

                $data['average_price'] = 0;
          // print_r($_FILES);exit();


		 // echo $data['shu'];
		//echo 777;

		//exit();

        //户型图上传
        if(!empty($data['shu'])){
            $aaa=$data['shu'];
            for ($n=0; $n<$aaa; $n++) {
    	        $hximg[] = request()->file('row'.$n);
            }
            foreach ($hximg as $key => $hximgs) {
                 $info = $hximgs->move(env('root_path'). 'public/uploads/secondhouse');
                 if($info){
                      $image= $info->getSaveName();
                      $bbb[]='/uploads/secondhouse/'.$image;
                 }
             }

            if($aaa=='1'){
                $bbb=$bbb[0];
            }
            if($aaa=='2'){
                $bbb=$bbb[0].','.$bbb[1];
            }
            if($aaa=='3'){
                $bbb=$bbb[0].','.$bbb[1].','.$bbb[2];
            }
            if($aaa=='4'){
                $bbb=$bbb[0].','.$bbb[1].','.$bbb[2].','.$bbb[3];
            }
            if($aaa=='5'){
                $bbb=$bbb[0].','.$bbb[1].','.$bbb[2].','.$bbb[3].','.$bbb[4];
            }
            $data['hximg']=$bbb;
        }
        //给图片打水印star
        //户型图
        if(isset($data['hximg'])){
            if(!empty($data['hximg'])){
                $water_hximg = explode(",",$data['hximg']);
                foreach($water_hximg as $k=>$v){
                    $ImageServer->ImageWater('../public'.$v,'../public/static/shuiyin/ppshuiyin.png',10);
                }
            }
        }
        //给图片打水印end

             if (!empty($_FILES['hxsimg']['name'])) {

               $hxsimg = request()->file('hxsimg');

               if($hxsimg){
                     $info = $hxsimg->move(env('root_path'). 'public/wj');

                     if($info){

                          $image= $info->getSaveName();
                         $data['hxsimg']='/wj/'.$image;
                     }

                 }
             }
  if (!empty($data['toilet'])) {
             $yyy= model('linkmenu')->field('name')->where('id',$data['toilet'])->where('menuid',29)->find();
             // print_r($yyy);
                 if(strpos($yyy['name'],'一室')!==false){
                    $data['room']=1;
                 }

                 else if (strpos($yyy['name'],'两室')!==false){
                     $data['room']=2;
                 }

                  else if (strpos($yyy['name'],'三室')!==false){
                     $data['room']=3;
                 }
                  else if (strpos($yyy['name'],'四室')!==false){
                     $data['room']=4;
                 }


                 else if (strpos($yyy['name'],'五室')!==false){
                     $data['room']=5;
                 }
                 else{
                     $data['room']=6;
                 }
          }



                if($data['qipai']>0 && $data['acreage']>0)
                {
                    //计算均价
                    $data['average_price'] =sprintf("%.2f",intval($data['qipai'])/intval($data['acreage'])*10000);
                }
//                if (!isset($data['position'])) {
//                    $obj->rec_position = 0;
//                } else {
//                    $obj->rec_position = 1;
//                }
                $data['file'] = $this->getPic();
                (empty($data['img']) && !empty($data['file'])) && $data['img'] = $data['file'][0]['url'];
                (empty($data['imgs']) && !empty($data['file'])) && $data['imgs'] = $data['file'][0]['url'];
                $data['ratio'] = $this->addHousePrice($data['id'],$data['average_price']);
                $fpyid=$data['contacts']['contact_name'];
                $fpys = db('user')->where(['id'=>$fpyid])->find();
                $data['contacts']['contact_name']=$fpys['user_name'];
                $data['contacts']['contact_phone']=$fpys['mobile'];
                $data['online_consulting']=$fpys['online_consulting'];
                if($fpyid==16){
                    $data['broker_id']=0;
                }else{
                    $data['broker_id']=$fpyid;
                }
                //编辑
                //同步管理员 //默认管理员
                $fa_admin =2;
                if (!empty($fpys['user_name'])) {
                    if ($fpys['user_name'] =='管理员'){
                        $fpys['user_name'] ="admin";
                    }
                    $fa_admin=Db::connect('db2')->name('admin')->where('username',$fpys['user_name'])->find();
                }
                $h_map = "";
                //户型图 新添加的户型图
                if (!empty($data['hximg'])){
                    $fa_hximg = explode(',',$data['hximg']);
                    if (!empty($fa_hximg[0])){
                        $this->fa_mv_img($fa_hximg[0]);
                        $h_map = $fa_hximg[0];
                    }
                }else{
                    $fa_show_new = Db::connect('db2')->field('id,h_map')->name('show')->where('fang_second_house_id',$data['id'])->find();
                    if(!empty($fa_show_new['h_map'])){
                        $h_map = $fa_show_new['h_map'];
                    }
                }
               //同步
                if($obj->allowField(true)->save($data,['id'=>$data['id']]))
                {
                    $fa_qianmfei =$data['qianfei'] ?? "";
                    $fa_xiaci = $data['xiaci'] ?? "";
                    $fa_qianmfei_xiaci = $fa_qianmfei."".$fa_xiaci;
                    $arr =[
                        'title'=>$data['title'] ?? "",
                        'bid'=> $bid, //10住宅  11商业  12别墅  13国有资产
                        'simg'=>$data['img'] ?? "", //列表图片 = 缩略图
                        'ord'=>$data['ordid'] ?? 10,//权重
                        'is_recom'=>$data['rec_position'] ?? 0 ,
//            'seoTitle'=>$data['seo_title'],//seo
//            'seoKeyword'=>$data['seo_keys'],
//            'setDescription'=>$data['position'],
                        'addtime'=>strtotime($data['fabutime']) ?? time(),
                        'imgs'=>$fang_pic_serialize ?? "", //图片集 == 房源图片
                        'price'=>$data['qipai'] ?? "",
                        'per_price'=>$data['average_price'],
                        'market'=>$data['price'] ?? "",
                        'pick'=>$data['marketprice'],//
                        'house_type'=>$house_type,// $data['toilet']//户型
                        'floor'=>$data['floor'] ?? "",
                        'face'=>$face, //朝向 得转成 发牌网 全向问题
                        'uses'=>$user,
                        'tot_area'=>$data['acreage'] ?? "",
                        'build'=>$fa_years,//建成时间
                        'district'=>$data['estate_name'] ?? "",
                        'property'=>'',//产权 40 50 70
                        'margin'=>$data['baozheng'] ?? "",
                        'stime'=>strtotime(date($data['kptime'])) ?? "",
                        'etime'=>$fa_etime,//起拍截至时间
                        'team_id'=>$fa_admin['id'],//管理员id
                        'ext_attr'=>$fa_ext_attr, //价格，面积，户型，阶段
                        'status'=>$fa_status,
                        'm_con_1'=>$fa_qianmfei_xiaci,
                        'city'=>$fa_city['id'],//城市(北京)
                        'regionId'=>$fa_area['id'],//区域(东城)
                        'region_chId'=>$fa_street['id'],//街道(东单)
                        'longitude'=>$data['lng'],
                        'latitude'=>$data['lat'],
                        'address'=>$data['address'] ?? "",
                        'stat_ord'=>$fa_status,
//                        'hr_tid'=>$fa_hr_tid,
                        'tot_floor'=>$data['total_floor'] ?? "",
                        'h_map'=>$h_map ?? "",//户型图
                        'n_con'=>$data['info'] ?? "",//pc拍卖公告
                        'f_price'=>$data['cjprice'] ?? "",
                        'f_time'=>strtotime(date($data['endtime']))?? "",
                        'com_id'=>$fa_community_title['id'],//所在小区(id)
                        'm_n_con'=>$data['info'] ?? "",//手机拍卖公告
                        'vr_url'=>$data['pano_url'] ?? "",
                        'hr_code'=>rand(100000,999999),//房源编号
                        'fang_second_house_id'=>$data['id'],//房拍网 房源id
                        'is_free'=>$data['is_free'] ?? 0,//是否自由购
                    ];
                    $get_fa_show_id =$this->get_fa_show_id($data['id'],$data['title']);
                    if(!empty($get_fa_show_id)){
                        model('show')->allowField(true)->save($arr,['id'=>$get_fa_show_id]);
                    }

                    $this->optionHouseData($data['id'],$data,true);

                    \app\manage\service\Price::calculationPrice($data['estate_id']);

                    \app\manage\service\Position::option($data['id'],$this->model);

                    $msg = '编辑房源信息成功';

                    $code = 1;

                    \org\Relation::addSchool('second_house',$data['lng'],$data['lat'],$data['id'],$data['city']);

                    \org\Relation::addMetro('second_house',$data['lng'],$data['lat'],$data['id'],$data['city']);

                }else{

                    $msg = '编辑房源信息失败！';

                }



                \think\Db::commit();

            }catch(\Exception $e){

                \think\facade\Log::record('编辑房源信息出错：'.$e->getFile().$e->getLine().$e->getMessage());

                \think\Db::rollback();

                $msg = $e->getMessage();

            }

        }

        if($code == 1)

        {

            $this->success($msg,$url);

        }else{

            $this->error($msg,$url);

        }

    }

    public function delete()

    {

        \app\common\model\SecondHouse::event('after_delete',function($obj){
            $show_id = $this->get_fa_show_id($obj->id,$obj->title);
            if($show_id){
                Db::connect('db2')->name('show')->where(['id'=>$show_id])->delete();
            }
            //删除扩展数据

            $mod = model('second_house_data');

            $where = ['house_id'=>$obj->id];

            $info= $mod->where($where)->find();
            if($mod->where($where)->delete())

            {

//                model('attachment')->deleteAttachment($info['info'],$obj->img,$info['file']);//删除图片

            }

            model('attachment')->deleteVideo($obj->video);//删除视频

            //删除价格数据

            db('house_price')->where(['house_id'=>$obj->id,'model'=>'second_house'])->delete();

            //删除推荐数据

            action('manage/Position/deleteByHouseId', ['house_id'=>$obj->id,'model'=>$this->model], 'controller');

            //删除地铁关联数据

            \org\Relation::deleteByHouse($obj->id,'second_house');

            //删除学校关联数据

            \org\Relation::deleteByHouse($obj->id,'second_house','school');

        });

        parent::delete();

    }


    public function tuijian()

    {
        \app\common\model\SecondHouse::event('after_tuijian',function($obj){



            $mod = model('second_house');

            $where = ['id'=>$obj->id];

            $info= $mod->where($where)->find();


            // $info['rec_position']  = 1;
            // db('house_price')->where(['house_id'=>$obj->id,'model'=>'second_house'])->delete();
            // model('second_house')->allowField(true)->save($info,['id'=>$obj->id]);


            model('second_house')->where(['id'=>404])->update(['rec_position'=>1]);
        });

        parent::tuijian();
    }

    /**
     * 根据id取消推荐/并同步到法拍网
     * @param mixed
     * @author: al
     */
    public function cancelTuiJian()

    {
        \app\common\model\SecondHouse::event('after_tuijian',function($obj){



            $mod = model('second_house');

            $where = ['id'=>$obj->id];

            $info= $mod->where($where)->find();


            // $info['rec_position']  = 1;
            // db('house_price')->where(['house_id'=>$obj->id,'model'=>'second_house'])->delete();
            // model('second_house')->allowField(true)->save($info,['id'=>$obj->id]);


//            model('second_house')->where(['id'=>404])->update(['rec_position'=>0]);
        });

        parent::canceltuijian();
    }

    public function tuisong()

    {
        \app\common\model\SecondHouse::event('after_tuisong',function($obj){



            $mod = model('second_house');

            $where = ['id'=>$obj->id];

            $info= $mod->where($where)->find();

                $time=time();
            // $info['rec_position']  = 1;
            // db('house_price')->where(['house_id'=>$obj->id,'model'=>'second_house'])->delete();
            // model('second_house')->allowField(true)->save($info,['id'=>$obj->id]);


            model('second_house')->where(['id'=>404])->update(['tuisong'=>1,'tstime'=>$time]);
        });

        parent::tuisong();
    }





    /**

     * @param $house_id

     * @param $data

     * 添加扩展数据

     */

    private function optionHouseData($house_id,$data,$update = false)

    {

        if (empty($data['seo']['seo_title'])) {

            $info['seo_title'] = $data['title'];

        }else{

            $info['seo_title'] = $data['seo']['seo_title'];

        }

        $info['supporting'] = isset($data['supporting'])?implode(',',$data['supporting']):0;

        $info['house_id']  = $house_id;

        $info['info']      = isset($data['info']) ? $data['info'] : '';

        $info['basic_info_details']  = $data['basic_info_details'];
        $info['seo_keys']  = $data['seo']['seo_keys'];

        $info['seo_desc']  = $data['seo']['seo_desc'];

        $info['file']      = $data['file'];//$this->getPic();

        if($update)

        {

            model('second_house_data')->allowField(true)->save($info,['house_id'=>$house_id]);

        }else{

            model('second_house_data')->allowField(true)->save($info);

        }



    }



    /**

     * @param $house_id

     * @param $price

     * 添加价格

     */

    private function addHousePrice($house_id,$price)

    {

        $priceObj  = model('house_price');

        $rate = 0;

        //读取上一次价格

        $prev_price = $priceObj->where(['house_id'=>$house_id,'model'=>'second_house'])->order('create_time desc')->value('price');

        if($prev_price != $price && intval($price) > 0)

        {

            $data['price'] = $price;

            $data['create_time'] = time();

            $data['house_id'] = $house_id;

            $data['model']    = 'second_house';

            //计算涨幅比

            $prev_price && $rate = number_format((($price - $prev_price) / $prev_price) * 100,1);

            //$obj->removeOption();

            $priceObj->insert($data);

        }

        return $rate;

    }

    /**

     * @param $obj

     * 添加图片

     */

    private function getPic(){

        $insert = [];

        if(isset($_POST['pic']) && !empty($_POST['pic'])) {

            $images = $_POST['pic'];

            foreach ($images as $key => $v) {

                $insert[] = [

                    'url' => $v['pic'],

                    'title' => $v['alt'],

                ];

            }

        }

        return $insert;

    }
    /**
     * 房拍网数据 添加到法拍
     * 转换朝向
     */
    public function fa_face($orientations){
        $face = 0;
        if ($orientations == 20){
            $face =59;
        }elseif ($orientations ==21){
            $face =66;
        }elseif ($orientations ==22){
            $face =63;
        }elseif ($orientations ==23){
            $face =68;
        }elseif ($orientations ==24){
            $face =62;
        }elseif ($orientations ==25){
            $face =61;
        }elseif ($orientations ==26){
            $face =64;
        }elseif ($orientations ==27){
            $face =65;
        }elseif ($orientations ==28){
            $face =67;
        }elseif ($orientations ==29){
            $face =60;
        }
        return $face;
    }

    /**
     * 房拍网数据 添加到法拍
     * 房型
     */
    public function fa_ext_attr($toilet,$type=""){
        $ext_attr =0; //庭室
        $house_type =0; //户型

        switch ($toilet) {
            case 18338: //一室
                $house_type="42";
                $ext_attr ="25";
                break;
            case 18339:  // 一室一厅
                $house_type =43;
                $ext_attr =25;
                break;
            case 18340: //二室
                $house_type ="44";
                $ext_attr="26";
                break;
            case 18341: //两室一厅
                $house_type =45;
                $ext_attr=26;
                break;
            case 18342: //两室两厅
                $house_type ="46";
                $ext_attr="26";
                break;
            case 18343: ////三室一厅
                $house_type ="47";
                $ext_attr="27";
                break;
            case 18344: //三室一厅
                $house_type ="48";
                $ext_attr="27";
                break;
            case 18345: //四室一厅
                $house_type ="49";
                $ext_attr="27";
                break;
            case 18346: //四室两厅
                $house_type ="50";
                $ext_attr="36";
                break;
            case 18347: //四室三厅
                $house_type ="51";
                $ext_attr="36";
                break;
            case 18348: //五室一厅
                $house_type ="52";
                $ext_attr="37";
                break;
            case 18349: //四室两厅
                $house_type ="53";
                $ext_attr="37";
                break;
            case 18350: //五室三厅
                $house_type ="54";
                $ext_attr="37";
                break;
            case 18351: //五室五厅
                $house_type ="55";
                $ext_attr="37";
                break;
            case 18352: //六室两厅
                $house_type ="56";
                $ext_attr="38";
                break;
            case 18353: //六室三厅
                $house_type ="57";
                $ext_attr="38";
                break;
            case 18354: //独栋
                $house_type ="71";
                $ext_attr="42";
                break;
            case 18355: //多居
                $house_type ="76";
                $ext_attr="38";
                break;
            case 18356: //土地
                $house_type ="78";
                $ext_attr="47";
                break;
            case 18357: //厂房
                $house_type ="41";
                $ext_attr="46";
                break;

        }
        if ($type == "ext_attr"){
            return $ext_attr;
        }else{
            return $house_type;
        }

    }

    /**
     * 房拍网数据 添加到法拍
     * 阶段 $data['jieduan']
     */
    public function  fa_jieduan($jieduan){
        $res = 0;
        if ($jieduan == 161){
            $res ='28';
        }elseif ($jieduan == 162){
            $res ='29';
        }elseif ($jieduan == 163){
            $res ='45';
        }
        return $res;
    }
    /**
     * 房拍网数据 添加到法拍
     * $data['acreage']
     * 面积
     */
    public function  fa_acreage($acreage){
        $res =0;
        if ($acreage < 50){
            $res =23;
        }elseif ($acreage < 100 && $acreage > 50){
            $res=24;
        }elseif ($acreage < 200 && $acreage > 100){
            $res=32;
        }elseif ($acreage < 500 && $acreage > 200){
            $res=35;
        }elseif ($acreage < 1000 && $acreage > 500){
            $res=40;
        }elseif ($acreage > 1000){
            $res=41;
        }
        return $res;
    }
    /**
     *房拍网数据 添加到法拍
     * 价格
     */
    public function fa_price($price){
        $res =0;
        if ($price < 500){
            $res =20;
        }elseif ($price < 1000 && $price > 500){
            $res=21;
        }elseif ($price < 3000 && $price > 1000){
            $res=22;
        }elseif ($price > 3000){
            $res=34;
        }
        return $res;
    }
    /**
     *房拍网数据 添加到法拍
     * 拍卖状态
     */
    public function fa_status($status){
        $res = 0;
        if ($status == 169){//正在进行
            $res =0;
        }elseif($status == 170){//即将进行
            $res =0;
        }elseif($status == 171){//已结束
            $res =0;
        }elseif($status == 172){//终止
            $res =4;
        }elseif($status == 173){//撤回
            $res =5;
        }elseif($status == 174){//暂缓
            $res =6;
        }elseif($status == 175){//已成交
            $res =7;
        }
        return $res;
    }
    /**
     * 房拍网数据 添加到法拍
     * fa_hr_tid
     */
    public function fa_hr_tid($hr_tid){
        $res = 0;
        if ($hr_tid == 'T'){
            $res =6;
        }elseif ($hr_tid == 'J'){
            $res =7;
        }elseif ($hr_tid == 'S'){
            $res =8;
        }elseif ($hr_tid == 'X'){
            $res =9;
        }elseif ($hr_tid == 'C'){
            $res =22;
        }elseif ($hr_tid == 'G'){
            $res =73;
        }elseif ($hr_tid == 'Z'){
            $res =75;
        }elseif ($hr_tid == 'E'){
            $res =79;
        }
        return $res;
    }
    public function fa_bid($tags,$type =''){
        $bid ="11,商业";
        foreach ($tags as $v){
            if ($v == 184){ //商业
                $bid ="11,商业";
            }elseif($v == 185){//住宅
                $bid ="10,住宅";
            }elseif($v == 186){//国有
                $bid ="13,国有";
            }elseif($v == 187){//别墅
                $bid ="12,别墅";
            }

//            elseif($v == 188){//一拍
//                $ext_attr ="28";
//            }elseif($v == 189){//二拍
//                $ext_attr ="29";
//            }elseif($v == 190){//变卖
//                $ext_attr ="45";
//            }elseif($v == 18324){//公寓
//                $user ="公寓";
//            }elseif($v == 18325){//办公
//                $user ="办公";
//            }elseif($v == 18326){//厂房
//                $user ="厂房";
//            }elseif($v == 18360){//土地
//                $user ="土地";
//            }elseif($v == 18362){//酒店
//                $user ="酒店";
//            }
//            if ($type == 'user'){
//                return $user;
//            }elseif ($type == 'ext_attr'){
//                return $ext_attr;
//            }else{
//                return $bid;
//            }
            return $bid;
        }
    }

    /**
     * 图片移动
     * @param $img_url
     * @return al
     */
    public function fa_mv_img($img_url){
        //检测是否有图片地址
        $res = "";
        $cp_img_url = substr($img_url,0,30);
        $fa_img_url = '../../www.fapaiwang.cn'.$cp_img_url;
        $this->createDir($fa_img_url);
        $img_url =substr($img_url,1);
        if(file_exists($img_url)) {
            $res = copy($img_url, '../../www.fapaiwang.cn/' . $img_url);
        }
        return $res;
    }

    /**
     *  建立文件夹
     * @param $aimUrl
     * @return al
     */
    function createDir($aimUrl) {
        $aimUrl = str_replace('', '/', $aimUrl);
        $aimDir = '';
        $arr = explode('/', $aimUrl);
        $result = true;
        foreach ($arr as $str) {
            $aimDir .= $str . '/';
            if (!file_exists($aimDir)) {
                $result = mkdir($aimDir,0777,true);
            }
        }
        return $result;
    }

    /**
     * 根据房拍网房源信息 获取法拍网房源id
     * @param $id
     * @param string $title
     * @return al
     * @throws \think\Exception
     */
    public function get_fa_show_id($id,$title=""){
        $fa_show_new = Db::connect('db2')->field('id')->name('show')->where('fang_second_house_id',$id)->find();
        //判断是手动加的数据还是自动加的
        if(!empty($fa_show_new['id'])){ //自动
            $fa_show_id = $fa_show_new['id'];
        }else{ //手动
            $fa_show = Db::connect('db2')->field('id')->name('show')->where('title',$title)->find();
            $fa_show_id =$fa_show['id'];
        }
        return $fa_show_id;
    }
    public function fa_types($id = 1){
        $res="房产";
        switch ($id) {
            case 18331: //别墅
                $res="别墅";
                break;
            case 18332:  // 平房.四合院
                $res="平房.四合院";
                break;
            case 18333: //商办
                $res="商办";
                break;
            case 18334: //住宅
                $res="住宅";
                break;
            case 18336: //写字楼
                $res="写字楼";
                break;
            case 18358: //厂房
                $res="厂房";
                break;
            case 18359: //土地
                $res="土地";
                break;
            case 18361: ////酒店
                $res="酒店";
                break;
            case 18363: //公寓
                $res="公寓";
                break;
            case 1: //房产
                $res="房产";
                break;
        }
        return $res;
    }
    /**
     *    房源信息详情
     * 
     */
    public function detail(){
        $id = input('param.id/d');
        $lists = model('operatio_log')->alias('o')->join('user u','u.id = o.operator')->where('o.house_id',$id)->where('o.type','in','3,4')
            ->field('o.id,o.type,o.set_time,u.nick_name')->order('o.set_time desc')->paginate(30,false,['query'=>['id'=>$id]]);
        $this->assign('id',$id);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }
    /**
    *  房源收益计算页面
     */
    function profit(){
        $id = input('param.id/d');
        $arr = model('second_house')->field('id,title,qipai,price,acreage')->where('id',$id)->find();
        if($arr['acreage']>=90){
            $arr['tax_rate']=1.5;
        }else{
            $arr['tax_rate']=1;
        }
        $arr['qipai'] = $arr['qipai']*10000;
        $arr['price'] = $arr['price']*10000;
        $this->assign('arr',$arr);
        $this->assign('id',$id);
        return $this->fetch();
    }
    /**
     * 房产收益计算
     */
    public function ajaxcalculation(){
        $qipai = input('param.qipai');
        $price = input('param.price');
        $qianfei = input('param.qianfei');
        $qita = input('param.qita');
        $tax_rate = input('param.tax_rate');
        $price_increase_range = input('param.price_increase_range');
        $qianfei = $qianfei+$qita;
        $number = intval(($price-$qipai)/$price_increase_range);
        $qishui = round($qipai*$tax_rate/100);
        $data[] = array('qipai'=>$qipai,'qianfei'=>$qianfei,'qishui'=>$qishui,'price'=>$price,'cost'=>$qipai+$qianfei+$qishui,'status'=>0);
        unset($qishui);
        for($i=0;$i<$number;$i++){
            $qipai = $qipai+$price_increase_range;
            $qishui = round($qipai*$tax_rate/100);
            if($qipai/$price>0.9){
                $data[] = array('qipai'=>$qipai,'qianfei'=>$qianfei,'qishui'=>$qishui,'price'=>$price,'cost'=>$qipai+$qianfei+$qishui,'status'=>1); 
            }else{
                $data[] = array('qipai'=>$qipai,'qianfei'=>$qianfei,'qishui'=>$qishui,'price'=>$price,'cost'=>$qipai+$qianfei+$qishui,'status'=>0);
            }
            unset($qishui);
        }
        return $data;
    }
    function excelFileExport() 
    {
        $qipai = input('param.qipai');
        $name = input('param.title');
        $price = input('param.price');
        $qianfei = input('param.qianfei');
        $qita = input('param.qita');
        $tax_rate = input('param.tax_rate');
        $price_increase_range = input('param.price_increase_range');
        $qianfei = $qianfei+$qita;
        $number = intval(($price-$qipai)/$price_increase_range);
        $qishui = round($qipai*$tax_rate/100);
        $data[] = array('qipai'=>$qipai,'qianfei'=>$qianfei,'qishui'=>$qishui,'price'=>$price,'cost'=>$qipai+$qianfei+$qishui,'status'=>0);
        unset($qishui);
        for($i=0;$i<$number;$i++){
            $qipai = $qipai+$price_increase_range;
            $qishui = round($qipai*$tax_rate/100);
            if($qipai/$price>0.9){
                $data[] = array('qipai'=>$qipai,'qianfei'=>$qianfei,'qishui'=>$qishui,'price'=>$price,'cost'=>$qipai+$qianfei+$qishui,'status'=>1); 
            }else{
                $data[] = array('qipai'=>$qipai,'qianfei'=>$qianfei,'qishui'=>$qishui,'price'=>$price,'cost'=>$qipai+$qianfei+$qishui,'status'=>0);
            }
            unset($qishui);
        }
        $title='配资房产收益表';
        //文件名
        $fileName = $title. '('.date("Y-m-d",time()) .'导出）'. ".xls";
        //加载第三方类库
        require'../extend/PHPExcel/Classes/PHPExcel.php';
        require'../extend/PHPExcel/Classes/PHPExcel/IOFactory.php';
        //实例化excel类
        $excelObj = new \PHPExcel();
        //构建列数--根据实际需要构建即可
        $letter = array('A','B','C','D','E');
        $excelObj->getActiveSheet()->mergeCells( 'A1:E1'); 
        $excelObj->getActiveSheet()->setCellValue("A1",$name);
        //表头数组--需和列数一致
        $tableheader = array('起拍价','物业欠费','契税','市场价 ','共计成本');
        //填充表头信息
        for ($i = 0; $i < count($tableheader); $i++) {
            $excelObj->getActiveSheet()->setCellValue("$letter[$i]2", "$tableheader[$i]");
        }
        //循环填充数据
        foreach ($data as $k => $v) {
            $num = $k + 3;
            //设置每一列的内容
            $excelObj->setActiveSheetIndex(0)
                ->setCellValue('A' . $num, $v['qipai'])
                ->setCellValue('B' . $num, $v['qianfei'])
                ->setCellValue('C' . $num, $v['qishui'])
                ->setCellValue('D' . $num, $v['price'])
                ->setCellValue('E' . $num, $v['cost']);
                //设置行高
                $excelObj->getActiveSheet()->getRowDimension($k+3)->setRowHeight(30);
            //设置行背景色
            if($v['status']==1){
                $excelObj->getActiveSheet()->getStyle('A'.$num.':E'.$num)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FF0000');
            }
        }
        //以下是设置宽度
        $excelObj->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $excelObj->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $excelObj->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $excelObj->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $excelObj->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        //设置表头行高
        $excelObj->getActiveSheet()->getRowDimension(1)->setRowHeight(28);
        $excelObj->getActiveSheet()->getRowDimension(2)->setRowHeight(28);
        //$excelObj->getActiveSheet()->getRowDimension(3)->setRowHeight(28);

        //设置居中
        //$excelObj->getActiveSheet()->getStyle('A1:D1'.($k+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        //所有垂直居中
        //$excelObj->getActiveSheet()->getStyle('A1:D1'.($k+2))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置字体样式
        $excelObj->getActiveSheet()->getStyle('A1:D1')->getFont()->setName('黑体');
        $excelObj->getActiveSheet()->getStyle('A1:D1')->getFont()->setSize(20);
        $excelObj->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold(true);
        $excelObj->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold(true);  
        $excelObj->getActiveSheet()->getStyle('A1:D1')->getFont()->setName('宋体');
        $excelObj->getActiveSheet()->getStyle('A1:D1')->getFont()->setSize(16);
        $excelObj->getActiveSheet()->getStyle('A1:D1'.($k+2))->getFont()->setSize(10);

        //设置自动换行
        $excelObj->getActiveSheet()->getStyle('A1:D1'.($k+2))->getAlignment()->setWrapText(true);

        // 重命名表
        $fileName = iconv("utf-8", "gb2312", $fileName); 

        // 设置下载打开为第一个表
        $excelObj->setActiveSheetIndex(0); 

        //设置header头信息
        header('Content-Type: application/vnd.ms-excel;charset=UTF-8');
        header("Content-Disposition: attachment;filename={$fileName}");
        header('Cache-Control: max-age=0');
        $writer = \PHPExcel_IOFactory::createWriter($excelObj, 'Excel2007');
        $writer->save('php://output');
        exit();
    }
    /**
    *   尽职调查报告
     */
    public function report(){
        $ToolsServer= new ToolsServer();
        $id  = input('param.id/d',0);
        if(!$id)
        {
            $this->error('参数错误');
        }
        $url   = request()->server('HTTP_REFERER');
        $data = model('second_house_data')->where(['house_id'=>$id])->find();
        if(empty($data)){
            model('second_house_data')->insert(array('house_id'=>$id,'update_time'=>time()));
            $data = model('second_house_data')->where(['house_id'=>$id])->find();
        }
        $position_lists = model('position_cate')->where(['model'=>"second_house"])->field('id,title')->select();
        $house_position_cate_id = model('position')->where(['house_id' => $id, 'model' => 'second_house'])->column('cate_id');//获取该楼盘 所属的推荐位id
        $info = model('second_house')->find($id);
        $estate = model('estate')->where(['id'=>$info['estate_id']])->field("years,data")->find();
        if($data['bidding_cycle']==0||$data['bidding_cycle']==''){
            $data['bidding_cycle']=1;
        }
        if($data['decoration']==0||$data['decoration']==''){
            $data['decoration']=2;
        }
        if($info['qianfei']==''){
            $info['qianfei']='无';
        }
        if($info['xiaci']==''){
            $info['xiaci']='无';
        }
        if($info['lease']==''){
            $info['lease']='无';
        }
        if($data['influence_factor']==''){
            $data['influence_factor']='无';
        }
        if($data['gas_cost']==0||$data['gas_cost']==''){
            $data['gas_cost']='2.63元/立方米';
        }
        if($data['parking_information']==''){
            $data['parking_information']=$estate['data']['parking_space'];
        }
        if($info['years']==''){
            $info['years']=$estate['years'];
        }
        if($data['property_fee']==''){
            $data['property_fee']=$estate['data']['property_fee'];
        }
        $jiage  = 0;
        $mianji = $info['acreage'];
        if($info['ckprice']>0){
            $jiage = $info['ckprice'];
        }elseif($info['price']>0){
            $jiage = $info['price'];
        }
        if(empty($data['deed_tax'])&&!empty($info['qipai'])){
            $data['deed_tax'] = $ToolsServer->deen_tax($info['qipai'],$mianji);
        }
        if(empty($data['first_suite'])&&!empty($jiage)){
            $data['first_suite'] = $ToolsServer->deen_tax($jiage,$mianji);
        }
        if(empty($data['second_suite'])&&!empty($jiage)){
            $data['second_suite'] = $ToolsServer->deen_tax($jiage,$mianji,501);
        }
        if(empty($data['lending_rate'])){
            $lending_rate = getSettingCache('tools');
            $data['lending_rate'] = $lending_rate['lilv'];
        }
        if(empty($data['bidding_rules'])){
            $data['bidding_rules'] = '至少一人报名且出价不低于变卖价，方可成交。';
        }
        $operatio_log_where['house_id'] = $id;
        $operatio_log_where['type'] = 2;
        $edit_number = model('operatio_log')->where($operatio_log_where)->count();
        $this->assign('edit_number',$edit_number);
        $this->assign('developer',$estate['data']['developer']);
        $this->assign('back_url',$url);
        $this->assign('info', $info);
        $this->assign('position_lists',$position_lists);
        $this->assign('position_cate_id',$house_position_cate_id);
        $this->assign('data',$data);
        $this->assign('id',$id);
        return $this->fetch();
    }
    /**
    *    尽职调查报告编辑
     */
    public function report_edit(){
        if(request()->isPost())
        {
            $data = input('post.');
            $code   = 0;
            $msg    = '';
            $house_array = array();
            $house_array['elevator']         = $data['elevator'];
            $house_array['xiaci']            = $data['xiaci'];
            $house_array['xiaci_status']     = $data['xiaci_status'];
            $house_array['qianfei']          = $data['qianfei'];
            $house_array['qianfei_status']   = $data['qianfei_status'];
            $house_array['enforcement']      = $data['enforcement'];
            $house_array['land_purpose']     = $data['land_purpose'];
            $house_array['land_certificate'] = $data['land_certificate'];
            $house_array['property_no']      = $data['property_no'];
            //$house_array['house_purpse']     = $data['house_purpse'];
            $house_array['management']       = $data['management'];
            $house_array['lease']            = $data['lease'];
            $house_array['sequestration']    = $data['sequestration'];
            $house_array['vacate']           = $data['vacate'];
            $house_array['mortgage']         = $data['mortgage'];
            $house_array['elevator_status']  = $data['elevator_status'];
            $house_array['toilet']  = $data['toilet'];
            $house_array['acreage']  = $data['acreage'];
            $house_array['price']  = $data['price'];
            $house_array['ckprice']  = $data['ckprice'];
            $house_array['floor']  = $data['floor'];
            $house_array['total_floor']  = $data['total_floor'];
            $house_array['orientations']  = $data['orientations'];

            $data['update_time'] = time();
            $house_array['update_time'] = time();
            $house_id = $data['id'];
            $back_url = $data['back_url'];
            if($data['decoration']==1){
                $decoration = '精装';
            }elseif($data['decoration']==2){
                $decoration = '简装';
            }elseif($data['decoration']==3){
                $decoration = '毛坯';
            }elseif($data['decoration']==0){
                $decoration = '不详';
            }
            if($data['toilet']!=''){
                $toilet = model('linkmenu')->where(['id'=>$data['toilet']])->value("name");
            }else{
                $toilet = "不详";
            }
            if($data['heating_mode']==1){
                $heating_mode = '集中供暖';
            }elseif($data['heating_mode']==2){
                $heating_mode = '自供暖';
            }elseif($data['heating_mode']==3){
                $heating_mode = '无供暖';
            }elseif($data['heating_mode']==4){
                $heating_mode = '不详';
            }
            $nature = '';
            if($data['nature']==1){
                $nature = '商品房';
            }elseif($data['nature']==2){
                $nature = '公房';
            }elseif($data['nature']==3){
                $nature = '央产房';
            }elseif($data['nature']==4){
                $nature = '军产房';
            }elseif($data['nature']==5){
                $nature = '一类经适房';
            }elseif($data['nature']==6){
                $nature = '二类经适房（回迁）';
            }elseif($data['nature']==7){
                $nature = '央产经适房';
            }elseif($data['nature']==8){
                $nature = '平房私产';
            }elseif($data['nature']==9){
                $nature = '平房使用权';
            }elseif($data['nature']==10){
                $nature = '商办';
            }elseif($data['nature']==11){
                $nature = '限价商品房';
            }
            $basic_info = array($nature,$data['years'],$toilet,$decoration,$heating_mode,
                $data['parking_information'],$data['developer'],$data['education'].$data['medical_care'].$data['shangchao'],$data['traffic']
            );
            $house_array['basic_info'] = implode('|',$basic_info);
            unset($data['elevator']);unset($data['xiaci']);unset($data['qianfei']);unset($data['back_url']);unset($data['years']);
            unset($data['enforcement']);unset($data['land_purpose']);unset($data['land_certificate']);unset($data['hxsimg']);
            unset($data['property_no']);unset($data['management']);unset($data['lease']);unset($data['xiaci_status']);unset($data['qianfei_status']);
            unset($data['sequestration']);unset($data['vacate']);unset($data['mortgage']);unset($data['id']);unset($data['years']);
            unset($data['toilet']);unset($data['acreage']);unset($data['price']);unset($data['ckprice']);unset($data['floor']);
            unset($data['total_floor']);unset($data['orientations']);unset($data['elevator_status']);unset($data['developer']);
            \think\Db::startTrans();
            try{
                $a = model('second_house')->where(['id'=>$house_id])->update($house_array);
                $b = model('second_house_data')->where(['house_id'=>$house_id])->update($data);
                if($a&&$b){
                    $code = 1;
                    $msg = '编辑房源信息成功';
                    \think\Db::commit();
                }else{
                    $code = 0;
                    $msg = '编辑房源信息失败';
                    \think\Db::rollback();
                }
            }catch(\Exception $e){
                \think\facade\Log::record('编辑房源信息出错：'.$e->getMessage());
                \think\Db::rollback();
                $msg = $e->getMessage();
            }
            if($code == 1)
            {
                $this->success($msg,'second_house/report?id='.$house_id);
            }else{
                $this->error($msg,'second_house/report?id='.$house_id);
            }
        }
    }
    /**
     *   修改面积和市场价更新契税第一套和第二套契税
     */
    function deen_tax(){
        $acreage  = input('post.acreage');
        $jiage = input('post.jiage');
        $qipai = input('post.qipai');
        $ToolsServer= new ToolsServer();
        $data['deed_tax'] = $ToolsServer->deen_tax($qipai,$acreage);
        $data['first_suite'] = $ToolsServer->deen_tax($jiage,$acreage);
        $data['second_suite'] = $ToolsServer->deen_tax($jiage,$acreage,501);
        return $data;
    }
    /**
    *   成交人记录，签单人记录，签单时间
     */
    public function operation(){
        if(request()->isPost())
        {
            $data = input('post.');
            $data['create_time'] = time();
            if($data['set_time']==''){
                $this->error('未选择操作时间');
            }
            $c = model('operatio_log')->insert($data);
            if($c)
            {
                $this->success('记录成功');
            }else{
                $this->error('记录失败');
            }
        }else{
            $id  = input('param.id/d',0);
            $user = model('user')->field('id,nick_name')->where('model',4)->select();
            $this->assign('user_info',$user);
            $this->assign('id',$id);
            $this->assign('set_time',date('Y-m-d'));
            return $this->fetch();
        }
    }
    /**
     *   成交人记录，签单人记录，签单时间
     */
    public function operation_list(){
        $type  = input('param.type/d');
        $set_time  = input('param.set_time');
        $keyword  = input('param.keyword');
        $operator  = input('param.operator');
        $search = array('type'=>$type,'set_time'=>$set_time,'keyword'=>$keyword);
        $where = array();
        $where['u.model'] = 4;
        if($type!=''){
            $where['o.type'] = $type;
        }else{
            $where[] = array('o.type','in','3,4');
        }
        if($set_time!=''){
            $start_time = $set_time.' 00:00:00';
            $end_time = date('Y-m-d H:i:s',strtotime($set_time)+3600*24);
            $where[] = ['o.set_time','>=',$start_time];
            $where[] = ['o.set_time','<',$end_time];
        }
        if($keyword!=''){
            $where[] = array('h.title','like','%'.$keyword.'%');
        }
        if($operator!=''){
            $where['o.operator'] = $operator;
        }
        $join1 = [['operatio_log o','h.id=o.house_id']];
        $join2 = [['user u','u.id=o.operator']];
        $list = model('second_house')->alias('h')->join($join1)->join($join2)
            ->field("h.id,h.title,o.type,o.set_time,u.nick_name")->where($where)
            //->fetchSql(true)->select();dd($list);
            ->paginate(20,false,['query'=>$search]);
        $pages = $list->render();
        $user = model('user')->field('id,nick_name')->where('model',4)->select();
        $this->assign('user_info',$user);
        $this->assign('list',$list);
        $this->assign('search',$search);
        $this->assign('pages',$pages);
        return $this->fetch();
    }
    /**
    *  尽调报告审核
     */
    public function audit_status(){
        $id = input('post.id');
        $data['audit_status'] = input('post.audit_status');
        $data['audit_notes'] = input('post.audit_notes');
        $code = model('second_house')->where(array('id'=>$id))->update($data);
        if($code)
        {
            return true;
        }else{
            return false;
        }
    }
}
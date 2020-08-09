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
    /**
     * @param mixed
     * @return mixed
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index(){
        $parameter = input('param.a');
        $estate_id     = input('param.estate_id/d',0);//小区id
        $ids  = input('param.ids');//房源批量id(,)
        $result = $this->getLists($parameter,$estate_id,$ids);
        $lists  = $result['lists'];
        $IndexServer= new IndexServer();
        $SecondServer= new SecondServer();
        //问答
        $answer = model('article')->field('id,title,hits,create_time')->where('cate_id',10)->cache('answer',3600)->order('hits desc')->limit(5)->select();
        $hot_news = model('article')->field('id,title')->where('cate_id','neq',10)->cache('hot_news',3600)->order('hits desc')->limit(5)->select();
        $area = input('param.area/d', $this->cityInfo['id']);

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
        //$this->assign('keywords',$keywords);
        $this->assign('metro',Metro::index($this->cityInfo['id']));//地铁线
        //$this->assign('house_type',getLinkMenuCache(9));//类型
        $this->assign('orientations',getLinkMenuCache(4));//朝向
        $this->assign('floor',getLinkMenuCache(7));//朝向
        //$this->assign('types',getLinkMenuCache(26));//类型s
        //$this->assign('jieduan',getLinkMenuCache(25));//阶段
        $this->assign('fcstatus',getLinkMenuCache(27));//状态
        $this->assign('renovation',getLinkMenuCache(8));//装修情况
        $this->assign('tags',getLinkMenuCache(14));//标签
        $this->assign('area',$this->getAreaByCityId());//区域
        $this->assign('position',$this->getPositionHouse(5,4));
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('top_lists',$result['top']);
        $this->assign('storage_open',getSettingCache('storage','open'));
        $this->assign('search',$result['search']);
        $this->assign('jieduan',$result['jieduan']);
        $this->assign('huxing',$result['huxing']);
        $this->assign('types',$result['types']);
        $this->assign('house_type',$result['house_type']);
        $this->assign('param',$result['param']);
        $this->assign('keyword',$result['keyword']);
        $this->assign('seo_array',$result['seo_array']);
        $this->assign('parameter_json',$result['parameter_json']);
        $this->assign('is_show_more',$result['is_show_more']);
        $this->assign('start_time',$result['start_time']);
        $this->assign('end_time',$result['end_time']);
        $this->assign('rading',$result['rading']);
        $this->setSeo($result['setSeo'],'','');
        return $this->fetch();
    }
    public function detail(){
        $second_house_id = input('param.id/d',0);
        if($second_house_id){
            $server = new server();
            $SecondServer = new SecondServer();
            //增加围观次数
            db('second_house')->where('id','=',$second_house_id)->setInc('weiguan');
            //second_house详情
            $info = $server->second_model($second_house_id);
            if($info){
                if (empty($info["bmrs"])){
                    $info["bmrs"] = 0;
                }
                $info["bianhao"] = substr($info["bianhao"],0,5);
                //添加浏览量
                updateHits($info['id'],'second_house');
                //小区详情
                $estate = $server->estate($info['estate_id']);

                //法拍专员信息
                $user = $server->user($second_house_id,$info['broker_id']);
                $user_info = $server->user_info($second_house_id,$info['broker_id']);
                $this->assign('user',$user);
                $this->assign('user_info',$user_info);
                //本小区拍卖套数
                $estate_num = $SecondServer->estate_second_num($second_house_id,$info['estate_name']);
                $this->assign('estate_num',$estate_num);
                $estate_id=$info['estate_id'];
                //拍卖成交记录
                $jilu1 =  model('transaction_record')->where('estate_id',$estate_id)->cache("transaction_record_".$estate_id,84000)->select();
                $this->assign('jilu1',$jilu1);

                //法拍专员点评/点评个数
                $second_house_user_comment = $SecondServer->second_house_user_comment($second_house_id);
                $user["user_id"] = $user["id"];
                $user["history_complate"] = $user_info->history_complate ?? 0;
                
                $second_house_business= [];
                if (empty($second_house_business) && !empty($user["user_id"])){
                    $second_house_business[0]= $user->toArray();
                }
                $second_house_user_comment_list = $SecondServer->second_house_user_comment_list($second_house_id,$info['broker_id']);
                if ($second_house_user_comment_list){
                    $second_house_business[1] =$second_house_user_comment_list;
                }
                $second_house_user_comment_num= count($second_house_user_comment->toArray());
                $this->assign('second_house_user_comment_num',$second_house_user_comment_num);
                $this->assign('second_house_user_comment',$second_house_user_comment);
                $this->assign('second_house_business',$second_house_business);
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
                if($info['years']==''){
                    $info['years']=$estate['years'];
                }
                if($info['parking_space']==''){
                    $info['parking_space']=$estate['data']['parking_space'];
                }
                $this->tdk($info["title"],$info["house_type"]);
                $this->assign('house_loan',json_encode($house_loan));
                $this->assign('guanzhu',$guanzhu);
                $this->assign('userInfo',$userInfo);
                $this->assign('recommend_house',$recommend_house);
                $this->assign('estate',$estate);
                $this->assign('id',$second_house_id);
                $this->assign('info',$info);
            }else{
                return $this->fetch('public/404');
            }
        }else{
            return $this->fetch('public/404');
        }
        return $this->fetch();
    }

    /**
     * 分享
     * @param mixed
     * @return mixed
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail_sharing(){

        $second_house_id = input('param.id/d',0);
        if($second_house_id) {
            $server = new server();
            $info = $server->second_model($second_house_id);
            $this->assign('info', $info);
            //房源分享图片处理
            $fenxiang_img[0] = $info["file"][1]["url"] ?? $info["img"];
            $fenxiang_img[1] = $info["file"][0]["url"] ?? $info["img"];
            $fenxiang_img[2] = $info["file"][2]["url"] ?? $info["img"];
            $fenxiang_img[3] = $info["file"][3]["url"] ?? $info["img"];
            $fenxiang_img[4] = $info["file"][4]["url"] ?? $info["img"];
            //小区详情
            $estate = $server->estate($info['estate_id']);
            $this->assign('estate', $estate);
            $this->assign('fenxiang_img', $fenxiang_img);
            $userInfo = $this->getUserInfo();
            $user = model('user')->field('share_img')->where('id',$userInfo["id"])->find();
            $userInfo["qr_code"] = $user->share_img ?? "";
            $this->assign('login_user',$userInfo);
        }else{
            return $this->fetch('public/404');
        }
        return $this->fetch();
    }
    /**
     * 微信分享
     * @param mixed
     * @return mixed
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail_sharing_wechat(){

        $second_house_id = input('param.id/d',0);
        if($second_house_id) {
            $server = new server();
            $info = $server->second_model($second_house_id);
            $this->assign('info', $info);
            //房源分享图片处理
            $fenxiang_img[0] = $info["file"][1]["url"] ?? $info["img"];
            $fenxiang_img[1] = $info["file"][0]["url"] ?? $info["img"];
            $fenxiang_img[2] = $info["file"][2]["url"] ?? $info["img"];
            //小区详情
            $estate = $server->estate($info['estate_id']);
            $this->assign('estate', $estate);
            $this->assign('fenxiang_img', $fenxiang_img);
            $userInfo = $this->getUserInfo();
            $user = model('user')->field('share_img')->where('id',$userInfo["id"])->find();
            $userInfo["qr_code"] = $user->share_img ?? "";
            $this->assign('login_user',$userInfo);
        }else{
            return $this->fetch('public/404');
        }
        return $this->fetch();
    }
    /**
     * 特色房源
     * @param mixed
     * @return mixed
     * @author: al
     */
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
                ->cache($cache_name,86401)
                ->order('kptime asc')->select();
            $seo['title'] = $second_house_extension->seo_title;
            $seo['keys']  =  $second_house_extension->seo_keys;
            $seo['desc']  =  $second_house_extension->seo_desc;
        }
        $this->assign('lists',$lists);
        $this->assign('seo',$seo);
        $this->assign('info',$second_house_extension);
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
    public function getLists($parameter="",$estate_id="",$ids=[]){
        $SecondServer = new SecondServer();
        $time    = time();
        $where_data   = $this->search($parameter,$estate_id,$ids);
        $where = $where_data['data'];
        $index_url = $where_data['index_url_new'];
        $param = $where_data['param'];
        $sort    = $param['sort'];//input('param.sort/d',0);
        if ( $param['fcstatus'] == 175){
            $sort = 11;
        }
        $keyword = input('get.keyword');
        $field   = "s.id,s.title,s.estate_id,s.estate_name,s.chajia,s.junjia,s.marketprice,s.city,s.video,s.total_floor,s.floor,
        s.img,s.qipai,s.pano_url,s.room,s.living_room,s.toilet,s.price,s.cjprice,s.average_price,s.tags,s.address,s.acreage,
        s.orientations,s.renovation,s.user_type,s.contacts,s.update_time,s.kptime,s.jieduan,s.fcstatus,s.types,s.onestime,
        s.oneetime,s.oneprice,s.twostime,s.twoetime,s.twoprice,s.bianstime,s.bianetime,s.bianprice,s.is_free,s.endtime,s.house_type";
        $obj     = model('second_house')->alias('s');
        //二手房列表
        if(isset($where['m.metro_id']) || isset($where['m.station_id'])){
            //查询地铁关联表
            $field .= ',m.metro_name,m.station_name,m.distance';
            $join  = [['metro_relation m','m.house_id = s.id']];
            $lists = $obj->join($join)->where($where)->where('m.model','second_house')->where('s.top_time','lt',$time)->field($field)->group('s.id')->order($this->getSort($sort))->paginate(30,false,['query'=>['a'=>$index_url]]);
        }else{
            if($sort==8){
                $lists   = $obj->where($where)->where('s.top_time','lt',$time)->where('s.fcstatus','neq',169)->field($field)->order($this->getSort($sort))->paginate(30,false,['query'=>['a'=>$index_url]]);
            }else if($sort==7){
                $lists   = $obj->where($where)->where('s.top_time','lt',$time)->where('s.fcstatus','eq',170)->field($field)->order($this->getSort($sort))->paginate(30,false,['query'=>['a'=>$index_url]]);
            }else{
                $lists   = $obj->where($where)->where('s.top_time','lt',$time)->field($field)->order($this->getSort($sort))->paginate(30,false,['query'=>['a'=>$index_url]]);
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
                $lists[$key]['fcstatus_name']=getLinkMenuName(27,$lists[$key]['fcstatus']);;
                $lists[$key]['types_name'] =getLinkMenuName(26,$lists[$key]['types']);
                $lists[$key]['orientations_name'] =getLinkMenuName(4,$lists[$key]['orientations']);
                $lists[$key]['chajia']=intval($lists[$key]['price'])-intval($lists[$key]['qipai']);
                $lists[$key]['characteristic_name'] = $SecondServer->house_characteristic_one($value['marketprice'],
                    $value['is_commission'],$value['is_school'],$value['is_metro']);
                if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
                    $lists[$key]['img'] = thumb($lists[$key]['img'],240,149);
                    $lists[$key]['is_app'] ="111";
                }

            }
        return ['lists'=>$lists,'top'=>$top,'search'=>$where_data['search'],'jieduan'=>$where_data['jieduan'],'huxing'=>$where_data['huxing'],
            'house_type'=>$where_data['house_type'],'types'=>$where_data['types'],'param'=>$where_data['param'],'keyword'=>$where_data['keyword'],
            'seo_array'=>$where_data['seo_array'],'parameter_json'=>$where_data['parameter_json'],'is_show_more'=>$where_data['is_show_more'],
            'start_time'=>$where_data['start_time'],'end_time'=>$where_data['end_time'],'rading'=>$where_data['rading'],'setSeo'=>$where_data['setSeo']];
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
    private function search($parameter,$estate_id,$ids){
        $secondSer = new SecondServer();
        $param = $secondSer->decompose($parameter,$this->cityInfo['id']);
        $time_frame  = $param['time_frame'];//查询时间
        $mod_type  = input('param.mod',0);//传值
        $data['s.status']    = 1;
        if (!empty($ids)){
            $data[] =["s.id","in",$ids];
        }
        $area_id = $param['area'];
        $is_show_more = 0;
        //seo优化
        $seo_title = '';
        $seo_array = array();
        //分页参数
        $index_url_new = $parameter;
        $title_area = '';
        $rading = array();

        //最新发布和自由购 只存在一个
        if ($param['sort'] == 10){
            $param['is_free']  = 1;
        }else{
            $param['is_free']  = 0;
        }
        //获取当前请求的参数
        $arr=$this->request->param();
        //显示街道时
        if ($param['area']  > 57){
            $param['street'] =$param['area'];
        }
        if(!empty($_GET['rec_position'])){
            $rec_position=$_GET['rec_position'];
        }
        $zmianji1=$param['zmianji1'];
        $zmianji2=$param['zmianji2'];
        $zprice1=$param['zprice1'];
        $zprice2=$param['zprice2'];
        if($estate_id) {
            $data['s.estate_id'] = $estate_id;
            $estate_name = model('estate')->where('id',$estate_id)->value('title');
        }
        //判断是否有关键字
        if(!empty($arr['keyword'])){
            $keyword = $arr['keyword'];
            $index_url_new = $parameter.'x'.$arr['keyword'];
        }else{
            $keyword = $param['keyword'];
        }
        //使用关键字拼接搜索条件-拼接SEO信息
        if($keyword){
            $param['keyword'] = $keyword;
            $search_keyword = $secondSer->search_keyword($keyword);
            $data[] = $search_keyword['title'];
            $seo_array[] = $search_keyword['seo'];
        }

        if($param['search_type'] != 2 && !empty($param['area'])){
                $data[] = ['s.city','in',$this->getCityChild($param['area'])];
                $rading = $this->getRadingByAreaId($param['area']);
                //读取商圈
                $param['rading'] = 0;
                if($rading && array_key_exists($param['area'],$rading)){
                    $param['rading']  = $param['area'];
                    $param['area']    = $rading[$param['area']]['pid'];
                }
                if($param['area']!=$this->cityInfo['id']){
                    $seo_title .= getCityName($area_id,'');
                }else{
                    $seo_title .= '北京';
                }
                $title_area = getCityName($param['area'],'');
                if($param['area']!=39){
                    $seo_array[] = array('letter'=>'a','keyword'=>getCityName($area_id),'type'=>1,'id'=>0);
                }
        }
        if(!empty($param['qipai'])) {
            $data[] = getSecondPrice($param['qipai'],'s.qipai');
            $qipai  = config('filter.second_qipai');
            isset($qipai[$param['qipai']]) && $seo_title .= $qipai[$param['qipai']]['name'];
            $seo_array[] = array('letter'=>'c','keyword'=>$qipai[$param['qipai']]['name'],'type'=>1,'id'=>0);
            $param['zprice1'] = 0;
            $param['zprice2'] = 0;
        }elseif($zprice2!=''&&$zprice1!=''){
            $data[] = ['s.qipai','between',[$zprice1,$zprice2]];
            $seo_title .= $zprice1.'到'.$zprice2.'万元';
            $seo_array[] = array('letter'=>'tu','keyword'=>$zprice1.'到'.$zprice2.'万元','type'=>1,'id'=>0);
        }
        //对面积搜索进行逻辑处理
        $search_acreage = $secondSer->search_acreage($param['acreage'],$zmianji1,$zmianji2);
        if(!empty($search_acreage['data'])){
            $data[] = $search_acreage['data'];
            $seo_title .= $search_acreage['seo_title'];
            $seo_array[] = $search_acreage['seo_array'];
            $is_show_more = 1;
            $param['zmianji1'] = $search_acreage['zmianji1'];
            $param['zmianji2'] = $search_acreage['zmianji2'];
        }
        if(!empty($param['room'])){
            $room_array   = config('filter.room');
            $data[] = array('s.room','in',$param['room']);
            $room_find = explode(',',$param['room']);
            foreach($room_array as $k=>$v){
                if(in_array($k,$room_find)){
                    $seo_title .= $v;
                    $seo_array[] = array('letter'=>'e','keyword'=>$v,'type'=>1,'id'=>$k);
                }
            }
            $is_show_more = 1;
        }
        if(!empty($param['jieduan'])) {
            $data[] = array('s.jieduan','in',$param['jieduan']);
            $jieduan_list = model('linkmenu')->field('id,name')->where('status','=',1)->where('id','in',$param['jieduan'])->select();
            $jieduan_list = objToArray($jieduan_list);
            foreach($jieduan_list as $k=>$v){
                $seo_title .= $v['name'];
                $seo_array[] = array('letter'=>'g','keyword'=>$v['name'],'id'=>$v['id']);
            }
            $is_show_more = 1;
        }
        if(!empty($param['fcstatus'])) {
            $data['s.fcstatus'] = $param['fcstatus'];
            $seo_title .= getLinkMenuName(27,$param['fcstatus']);
            $seo_array[] = array('letter'=>'h','keyword'=>getLinkMenuName(27,$param['fcstatus']),'id'=>$param['fcstatus']);
            $is_show_more = 1;
        }
        $start_time = $end_time = "";
        //对时间搜索进行逻辑处理
        $search_time = $secondSer->search_time($time_frame,$param['end_time']);
        if(!empty($search_time['start_time'])){
            $start_time = $search_time['start_time'];
            $end_time = $search_time['end_time'];
            $seo_title .= $search_time['seo_title'];
        }
        if(!empty($search_time['seo_array'])){
            $seo_array[] =  $search_time['seo_array'];
        }
        if (!empty($param['time_frame'])){
            $data[] = ['s.termination_datetime','>',$start_time];
            $data[] = ['s.termination_datetime','<',$end_time];
            $is_show_more = 1;
        }
        if ($mod_type == 3){
            $data[] = ['s.fabutime','>',date('Y-m-d')];
            $data[] = ['s.fabutime','<',date('Y-m-d',strtotime( '+1 day'))];
        }
        if(!empty($param['types'])){
            $data[] = array('s.types','in',$param['types']);
            $types_list = model('linkmenu')->field('id,name')->where('status','=',1)->where('id','in',$param['types'])->select();
            $types_list = objToArray($types_list);
            foreach($types_list as $k=>$v){
                $seo_title .= $v['name'];
                $seo_array[] = array('letter'=>'f','keyword'=>$v['name'],'id'=>$v['id']);
            }
        }
        $data[] = ['s.timeout','gt',time()];
        //是否是自由购
        if(!empty($param['is_free'])){
            $data['s.is_free'] = $param['is_free'];
            $seo_title .= '自由购';
        }
        //房源类型
        if(!empty($param['type'])){
            $data['s.house_type'] = $param['type'];
            $seo_array[] = array('letter'=>'i','keyword'=>getLinkMenuName(9,$param['type']),'id'=>$param['type']);
        }
        if(!empty($_GET['rec_position'])){
            $data[] = ['rec_position','eq',1];
        }
        if(!empty($param['rec_position'])){
            $data[] = ['rec_position','eq',1];
        }
        if(!empty($param['marketprice'])){
            $data[] = ['marketprice','in',$param['marketprice']];
        }
        if(!empty($param['is_school'])){
            $data[] = ['rec_position','eq',$param['is_school']];
        }
        $search = $param;
        $setSeo = $secondSer->seo_data($seo_title,$title_area,$param['type']);

        //$seo_title && $this->setSeo($setSeo,'','');
        $data = array_filter($data);
        //解决地区商圈选择问题
        if(isset($param['rading'])&&$param['rading']>0){
            $param['area']    = $param['rading'];
        }
        $column = $secondSer->column($param);
        //返回搜索条件
        $parameter_json = $secondSer->parameter_json($parameter,$keyword);
        return array('data'=>$data, 'index_url_new'=>$index_url_new,'param'=>$param,'search'=>$search,'jieduan'=>$column['jieduan'],'huxing'=>$column['huxing'],
            'types'=>$column['types'],'house_type'=>$column['house_type'],'param'=>$param,'keyword'=>$keyword,'seo_array'=>$seo_array,'parameter_json'=>$parameter_json,
            'is_show_more'=>$is_show_more,'start_time'=>$start_time,'end_time'=>$end_time,'rading'=>$rading,'setSeo'=>$setSeo);
    }
    /**
     * 搜索字段
     * @param $param
     * @param mixed
     * @author: al
     */
    function search_arr($param){
         $res =[];
        //区域
        if (!empty($param['area']) && $param['area'] != 39){
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
         //搜索时间
        if (!empty($param['time_frame'])){
            $room = config('filter.all_search');
            if (is_numeric($param['time_frame'])){
                $res[] =$room[$param['time_frame']];
            }
//            else{
//                $res[] =$param['time_frame'].'-'.$param['end_time'];
//            }

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
                $order = ['qipai'=>'asc','id'=>'desc'];
                break;
            case 2:
                $order = ['qipai'=>'desc','id'=>'desc'];
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
            case 10://自由狗
                $order = ['rec_position'=>'desc','fcstatus'=>'asc','marketprice'=>'desc'];
                break;
            case 11://已成交
                $order = ['endtime'=>'desc','id'=>'desc'];
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
        $secondSer = new SecondServer();
        if ($second_house_id > 0) {
            //法拍专员点评/点评个数
            $onReq = input("post.onReq");
            $second_house_user_comment = $secondSer->second_house_user_comment($second_house_id,2,1);
//            $onReq = $onReq ==0 || $onReq==1 ? 0: ($onReq-1)*2;
//            $second_house_user_comment = model('user')->alias('s')->join([['user_info info','info.user_id = s.id']])
//                ->field('s.id,s.nick_name,s.lxtel,info.history_complate,s.kflj')->where([['s.kflj','neq',''],['model','=',4]])
////                ->group('s.id')
//                    ->order("2")
////                ->limit($onReq,2)
////                ->cache('another_'.$onReq,'1800')
//                ->select();
            foreach ($second_house_user_comment as &$house){
                $house['avatar'] = getAvatar($house->user_id,90);
            }
            Log::write("--------------second_house_user_comment-------------".$second_house_user_comment);
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
    function sitemap(){
        echo 'www.fangpaiwang.com';echo '<br/>';
        $arr = model('second_house')->field('id')->where('status','=',1)->select();
        foreach($arr as $k=>$v){
            echo 'www.fangpaiwang.com/erf-'.$v['id'].'.html';echo '<br/>';
        }
    }
    /* echo 'www.fangpaiwang.com';echo '<br/>';
        $arr = model('second_house')->field('id')->where('status','=',1)->select();
        foreach($arr as $k=>$v){
            $url = 'https://www.fangpaiwang.com/erf-'.$v['id'].'.html';
            $a = $this->curl_get($url);
            if($a==200){
                //echo 'https://www.fangpaiwang.com/erf-'.$v['id'].'.html';echo '<br/>';
            }else{
echo 'www.fangpaiwang.com/erf-'.$v['id'].'.html';echo '<br/>';
    //}
}
     * function curl_get($url){
        $ch = curl_init ();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 200);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_NOBODY, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_exec($ch);
        $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $httpCode;
    }*/
    /**
     *
     * @param $title
     * @param mixed
     * @author: al
     */
    public function tdk($title,$house_type=45){
        if ($house_type == 48){
            $seo['title'] = $title.',北京社会委托房源拍卖-金铂顺昌房拍网';
            $seo['keys']  = $title.',北京二手房源,社会委托拍卖,金铂顺昌房拍网';
            $seo['desc']  = '金铂顺昌房拍网为您提供北京【'.$title.'】社会委托拍卖二手房源信息详情：费用价格、时间、流程、注意事项等服务信息，让您安心购便宜房！';
        }else{
            $seo['title'] = $title.',北京法拍房-金铂顺昌房拍网';
            $seo['keys']  = $title.',北京法拍房,金铂顺昌房拍网';
            $seo['desc']  = '金铂顺昌房拍网为您提供北京【'.$title.'】法拍房源信息详情：拍卖价格、公告、时间、流程、注意事项及风险评估等服务内容，让您安心购房！';
        }
        $this->assign('seo',$seo);
    }
    public function houseNotice(){
        $house_id = input("param.house_id");
        $second_house_data = model("second_house_data")->field("info")->where('house_id',$house_id)->find();
        $this->assign('second_house_data',$second_house_data);
        return $this->fetch();
    }


}
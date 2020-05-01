<?php


namespace app\mobile\controller;
use app\common\controller\MobileBase;
class Second extends MobileBase
{

    private $pageSize = 10;
    public function index()
    {
        $result = $this->getLists();
        $lists  = $result['lists'];
		// foreach ($lists as $key => $value) {
  //           print_r($value);exit();
  //           // $lists['qipai']=number_format("$value['qipai']",2,".","");
  //       }
		// print_r($lists);exit();
		
        $this->assign('area',$this->getAreaByCityId());
        $this->assign('type',getLinkMenuCache(9));//类型
        $this->assign('renovation',getLinkMenuCache(8));//装修情况
        $this->assign('tags',getLinkMenuCache(14));//标签
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('total_page',$lists->lastPage());
        $this->assign('top_lists',$result['top']);//置顶房源
        $this->assign('storage_open',getSettingCache('storage','open'));
        if(!empty($_GET['quyu'])){
            $quyus=$_GET['quyu'];
            $quyua = model('city')->where('id',$quyus)->find();
            // print_r($quyu);exit();
            // $quyu=$quyusql['name'];
            $this->assign('quyua',$quyua);

        }else{
            $quyus='0';
        }
        $this->assign('quyus',$quyus);
        
        return $this->fetch();
    }

    /**
     * @return \think\response\Json
     * 异步获取房源列表
     */
    public function getSecondLists()
    {
        $page    = input('get.page/d',1);
        $data    = $this->getLists($page);
        $lists   = $data['lists'];
        //print_r($lists);exit();
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
                $tags = array_filter(explode(',',$v['tags']));
                if(is_array($tags))
                {
                    $tag_str = '';
                    foreach($tags as $val)
                    {
                        $tag_str .= '<em>'.getLinkMenuName(14,$val).'</em>';
                    }
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
			
			
			
			//$where1['id']     = $id;
//
//            $where1['status'] = 1;
//			$price=$obj->where($where1)->field('marketprice')->select();
//			
//			
//			$marketprice=$price[0]['marketprice'];

			//$marketprice=$info['marketprice'];
//			$qipaiprice=$info['qipai'];
//			
//			$jlzs=round($marketprice/$qipaiprice,1);
//			
//			//print_r($jlzs);
//			if($jlzs<'1.1'){
//			
//				$jlzss='0';
//			
//			}
//			
//			if(($jlzs>='1.1') && ($jlzs<='2')){
//			
//				$jlzss='1';
//			
//			}
//			if(($jlzs>='1.3') && ($jlzs<='1.4')){
//			
//				$jlzss='2';
//			
//			}
//			if(($jlzs>='1.5') && ($jlzs<='1.6')){
//			
//				$jlzss='3';
//			
//			}
//			if(($jlzs>='1.7') && ($jlzs<='1.8')){
//			
//				$jlzss='4';
//			
//			}
//
//			if($jlzs>'1.8'){
//			
//				$jlzss='5';
//			
//			}
//
//			
//			//echo $jlzs;
//			//echo 444;
//			  $this->assign('jlzss',$jlzss);
			
			
			
			
			
			
			
			
            $info['file'] = json_decode($info['file'],true);
            $this->setSeo($info);
            $share_title = $info['estate_name'].$info['room'].'室'.$info['living_room'].'厅'.$info['acreage'].config('filter.acreage_unit').$info['price'].'万';
            updateHits($info['id'],'second_house');
            $estate = model('estate')->where('id',$info['estate_id'])->find();
            $xqinfo=$estate['data'];
            $this->assign('info',$info);
            $this->assign('estate',$estate);
            $this->assign('xqinfo',$xqinfo);
            $this->assign('near_by_house',$this->getNearByHouse($info['lat'],$info['lng'],$info['city']));
            $this->assign('same_price_house',$this->samePriceHouse($info->getData('price')));
            $this->assign('share_title',$share_title);
            $this->assign('storage_open',getSettingCache('storage','open'));
            $quyu = model('city')->where('id',$info['city'])->find();
            $fpy=$info['contacts'];
            $this->assign('fpy',$fpy);
            // print_r($quyu);exit();
            // $quyu=$quyusql['name'];
            $this->assign('quyu',$quyu);

            $qipai=$info['qipai'];
            $info['qipai']=number_format("$qipai",2,".","");
            $acreage=$info['acreage'];
            $info['acreage']=number_format("$acreage",2,".","");
        // print_r($lists);exit();
//zp倒计时
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
 




//zpend
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
        $field   = "id,title,city,iscj,fcstatus,estate_name,img,video,room,living_room,toilet,price,average_price,tags,address,acreage,orientations,renovation,update_time,qipai,marketprice";
        $obj     = model('second_house');
      // $where="iscj='159'";
        
        if(!empty($_GET['cj'])){
            $cj=$_GET['cj'];
        }
        // print_r($cj);
        $obj     = $obj->where($where)->field($field)->order($this->getSort($sort));
    	//print_r($obj);exit();
        if($page)
        {
            $lists = $obj->where('top_time','lt',$time)->page($page)->limit($this->pageSize)->select();
			//print_r($lists);
			
            $obj->removeOption();
            $count      = $obj->where($where)->where('top_time','lt',$time)->count();
            $total_page = ceil($count/$this->pageSize);
            $lists      = ['lists'=>$lists,'total_page'=>$total_page];
        }else{
            $result = $obj->where('top_time','lt',$time)->paginate($this->pageSize);
            $top    = $obj->removeOption()->where($where)->where('top_time','gt',$time)->field($field)->order(['top_time'=>'desc','id'=>'desc'])->select();
            $lists  = ['lists'=>$result,'top'=>$top];
			//print_r($result);
        }
		//echo 111;
		//print_r($lists);
		
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
        }}
      if(!empty($_GET['quyu'])){
        if(empty($param['type']))
        {
            $data['city'] = $_GET['quyu'];
        }}

        if(!empty($_GET['cj'])){
            $data['iscj'] = $_GET['cj'];

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
        $data[] = ['timeout','gt',time()];
        // print_r($data);
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
                $order = ['ordid'=>'asc','id'=>'desc'];
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
            default:
                $order = ['ordid'=>'asc','id'=>'desc'];
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
    private function samePriceHouse($price,$num = 4)
    {
        $min_price = $price > 10 ? $price - 10:$price;
        $max_price = $price + 10;
        $lists = model('second_house')
            ->where('status',1)
            ->where('price','between',[$min_price,$max_price])
            ->where('city','in',$this->getCityChild())
            ->where('timeout','gt',time())
            ->field('id,title,img,estate_name,city,tags,orientations,room,living_room,acreage,price')
            ->order('create_time desc')
            ->limit($num)
            ->select();
        return $lists;
    }
}
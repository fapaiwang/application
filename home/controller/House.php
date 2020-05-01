<?php


namespace app\home\controller;
use app\common\controller\HomeBase;
use app\common\service\Metro;
class House extends HomeBase
{
    private $mod = 'house';
    public function initialize(){
        parent::initialize();
        $this->cur_url = 'OfficeRental';
        $this->assign('action',request()->action());
    }
    /**
     * @return mixed
     * 新房列表
     */
    public function index()
    {
        $sort    = input('param.sort/d',0);
        $keyword = input('get.keyword');
        $where   = $this->search();
        $obj     = model('house');
        $join[]  = ['house_search s','h.id = s.house_id','left'];
        $field   = 'h.id,h.title,h.img,h.video,h.sale_status,h.red_packet,h.city,h.address,h.tags_id,h.price,h.unit,h.pano_url,s.min_type,s.max_type,s.min_acreage,s.max_acreage';
        if(isset($where['m.metro_id']) || isset($where['m.station_id']))
        {
            $join[] = ['metro_relation m','m.house_id = h.id'];
            $field .= ',m.metro_name,m.station_name,m.distance';
            $where[] = ['m.model','eq','house'];
        }
        $lists = $obj->alias('h')
            ->where($where)
            ->field($field)
            ->join($join)
            ->order($this->getSort($sort))
            ->group('h.id')
            ->paginate(10,false,['query'=>['keyword'=>$keyword]]);
        $storage_open = getSettingCache('storage','open');//去存储是否开启
        $this->assign('metro',Metro::index($this->cityInfo['id']));//地铁线
        $this->assign('area',$this->getAreaByCityId());
        $this->assign('special',getLinkMenuCache(3));//特色
        $this->assign('type',getLinkMenuCache(2));//类型
        $this->assign('status',getLinkMenuCache(1));//销售状态
        $this->assign('renovation',getLinkMenuCache(8));//装修情况
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('position',$this->getPositionHouse(4));
        $this->assign('storage_open',$storage_open);
        return $this->fetch();
    }

    /**
     * @return mixed
     * 楼盘详细页
     */
    public function detail()
    {
        $id = input('param.id/d',0);
        if(!$id)
        {
            return $this->fetch('public/404');
        }else{
            $info = $this->getHouseInfo($id);
            if(!$info)
            {
                return $this->fetch('public/404');
            }
            $this->getHouseSand($id);
            $info['photo'] = $this->getHousePhoto($id);
            $info['room_count'] = $this->countHouseRoom($id);
            $info['photo_cate'] = $this->getPhotoCate($id);
            updateHits($info['id'],'house');
            $info['seo_title'] .= '_楼盘详情';
            $this->setSeo($info);
            $storage_open = getSettingCache('storage','open');
            $this->assign('info',$info);
            $this->assign('id',$id);
            $this->assign('storage_open',$storage_open);
            $this->assign('nearby_house',$this->getNearByHouse($info['lat'],$info['lng'],$info['city']));
            return $this->fetch();
        }
    }

    /**
     * @return mixed
     * 新闻动态
     */
    public function news()
    {
        $id = input('param.house_id/d',0);
        if(!$id)
        {
            return $this->fetch('public/404');
        }else {
            $info = $this->getHouseInfo($id);
            if (!$info) {
                return $this->fetch('public/404');
            }
            $info['seo_title'] = $info['seo_title'].'新闻动态';
            $this->setSeo($info);
            $this->assign('info',$info);
            $this->assign('id',$id);

        }
        return $this->fetch();
    }

    /**
     * @return mixed
     * 在售户型
     */
    public function room()
    {
        $id = input('param.house_id/d',0);
        if(!$id)
        {
            return $this->fetch('public/404');
        }else {
            $info = $this->getHouseInfo($id);
            if (!$info) {
                return $this->fetch('public/404');
            }
            // $this->setSeo($info);
            $room = input('param.room/d',0);
            $where['house_id'] = $id;
            $where['status']   = 1;
            $room && $where['room']     = $room;
            $info['room_cate'] = $this->countHouseRoom($id);
            $room_list = model('house_type')->where($where)->field('id,title,img,room,living_room,price,sale_status,kitchen,toilet,acreage')->order('id desc')->paginate(12);
            $info['seo_title'] = $info['seo_title'].'在售户型';
            $this->setSeo($info);
            $this->assign('room_list',$room_list);
            $this->assign('pages',$room_list->render());
            $this->assign('room',$room);
            $this->assign('info',$info);

        }
        return $this->fetch();
    }

    /**
     * @return mixed
     * 户型详细
     */
    public function roomDetail()
    {
        $id   = input('param.id/d',0);
        $room = input('param.room/d',0);//户型 室
        if(!$id)
        {
            return $this->fetch('public/404');
        }else {
            $where['id']     = $id;
            $where['status'] = 1;
            $obj = model('house_type');
            $room_info = $obj->where($where)->find();
            if($room_info)
            {
                $map['house_id']      = $room_info['house_id'];
                $room && $map['room'] = $room;
                $map['status']        = 1;
                $room_list = $obj->where($map)->field('id,room,living_room,toilet,acreage')->order('room asc')->select();
                $info      = $this->getHouseInfo($room_info['house_id']);
                $room_cate = $this->countHouseRoom($room_info['house_id']);
                //链接户型room-id
                $room_id = $room_info['room'].'-'.$room_info['id'];
                $room_info['next'] = $this->getPrevNextRoom($map,$room_id);
                $room_info['prev'] = $this->getPrevNextRoom($map,$room_id,'desc');

                $info['seo_title'] = $info['seo_title'].$room_info['title'];
                $this->setSeo($info);
                $this->assign('room_info',$room_info);//户型详细
                $this->assign('room_list',$room_list);//户型列表
                $this->assign('info',$info);//楼盘详细
                $this->assign('room_cate',$room_cate);//户型分类统计
                $this->assign('id',$room_info['house_id']);
                $this->assign('room',$room);
            }else{
                return $this->fetch('public/404');
            }

        }
        return $this->fetch();
    }

    /**
     * @return mixed
     * 用户问答
     */
    public function question()
    {
        $id = input('param.house_id/d',0);
        if(!$id)
        {
            return $this->fetch('public/404');
        }else {
            $info = $this->getHouseInfo($id);
            if (!$info) {
                return $this->fetch('public/404');
            }
            $info['seo_title'] = $info['seo_title'].'用户问答';
            $this->setSeo($info);
            $this->assign('info',$info);
            $this->assign('id',$id);
        }
        return $this->fetch();
    }

    /**
     * @return mixed
     * 问答详情
     */
    public function questionDetail()
    {
        $id = input('param.id/d',0);
        if(!$id)
        {
            return $this->fetch('public/404');
        }else {
            $question_info = model('question')->where('id',$id)->find();
            if (!$question_info) {
                return $this->fetch('public/404');
            }
            $info = $this->getHouseInfo($question_info['house_id']);
            $info['seo_title'] = $info['seo_title'].'_'.$question_info['content'];
            $info['seo_keys']  = $info['seo_keys'].','.$question_info['content'];
            $this->setSeo($info);
            $this->assign('info',$info);
            $this->assign('question_info',$question_info);
            $this->assign('id',$question_info['house_id']);
        }
        return $this->fetch();
    }

    /**
     * @return mixed
     * 楼盘相册
     */
    public function photo()
    {
        $id = input('param.house_id/d',0);
        $cate_id = input('param.cate_id/d',0);
        if(!$id)
        {
            return $this->fetch('public/404');
        }else {
            $info = $this->getHouseInfo($id);
            if (!$info) {
                return $this->fetch('public/404');
            }
            $where['house_id'] = $id;
            $where['status']   = 1;
            $cate_id && $where['cate_id']  = $cate_id;
            //相册列表
            $lists = model('house_photo')->where($where)->order(['ordid'=>'asc','id'=>'desc'])->paginate(16);

            $info['seo_title'] = $info['seo_title'].'楼盘相册';
            $this->setSeo($info);
            $this->assign('info',$info);
            $this->assign('cate',$this->getPhotoCate($id));//相册分类
            $this->assign('id',$id);
            $this->assign('lists',$lists);
            $this->assign('pages',$lists->render());
            $this->assign('cate_id',$cate_id);
        }
        return $this->fetch();
    }

    /**
     * @return mixed
     * 全景看房
     */
    public function pano()
    {
        $id = input('param.house_id/d',0);
        if(!$id)
        {
            return $this->fetch('public/404');
        }else {
            $info = $this->getHouseInfo($id);
            if (!$info) {
                return $this->fetch('public/404');
            }
            $info['seo_title'] = $info['seo_title'].'全景看房';
            $this->setSeo($info);
            $this->assign('info',$info);
            $this->assign('id',$id);
        }
        return $this->fetch();
    }
    /**
     * @return mixed
     * 楼栋信息
     */
    public function build()
    {
        $id = input('param.house_id/d',0);
        if(!$id)
        {
            return $this->fetch('public/404');
        }else {
            $info = $this->getHouseInfo($id);
            if (!$info) {
                return $this->fetch('public/404');
            }
            $this->getHouseSand($id);
            $info['seo_title'] = $info['seo_title'].'楼栋信息';
            $this->setSeo($info);
            $this->assign('info',$info);
        }
        return $this->fetch();
    }
    /**
     * @return mixed
     * 楼盘点评
     */
    public function comment()
    {
        $id = input('param.house_id/d',0);
        if(!$id)
        {
            return $this->fetch('public/404');
        }else {
            $info = $this->getHouseInfo($id);
            if (!$info) {
                return $this->fetch('public/404');
            }
            $info['seo_title'] = $info['seo_title'].'楼盘点评';
            $this->setSeo($info);
            $this->assign('info',$info);
            $this->assign('id',$id);
            $this->assign('nearby_house',$this->getNearByHouse($info['lat'],$info['lng'],$info['city'],10));

        }
        return $this->fetch();
    }
    /**
     * @return mixed
     * 楼盘详情
     */
    public function info()
    {
        $id = input('param.house_id/d',0);
        if(!$id)
        {
            return $this->fetch('public/404');
        }else {
            $info = $this->getHouseInfo($id);
            if (!$info) {
                return $this->fetch('public/404');
            }
            $info['seo_title'] = $info['seo_title'].'详细信息';
            $this->setSeo($info);
            $this->assign('info',$info);
        }
        return $this->fetch();
    }

    /**
     * @return mixed
     * 周边配套
     */
    public function support()
    {
        $id = input('param.house_id/d',0);
        if(!$id)
        {
            return $this->fetch('public/404');
        }else {
            $info = $this->getHouseInfo($id);
            if (!$info) {
                return $this->fetch('public/404');
            }
            $info['seo_title'] = $info['seo_title'].'周边配套';
            $this->setSeo($info);
            $this->assign('info',$info);
        }
        return $this->fetch();
    }

    /**
     * @param $where
     * @param $id
     * @param string $op
     * @return array|null|\PDOStatement|string|\think\Model
     * 获取上一户型、下一户型
     */
    private function getPrevNextRoom($where,$id,$op = 'asc')
    {
        if($op == 'asc')
        {
            $map[] = ['r','gt',$id];
        }else{
            $map[] = ['r','lt',$id];
        }
        $obj = model('house_type');
        $bindSql = $obj->field(["*,CONCAT(room,'-',id) as r"])->where($where)->buildSql();
        $info =$obj->table($bindSql.' a')->field('id,r')->where($map)->order('r',$op)->find();
        return $info;
    }
    /**
     * @param $lat
     * @param $lng
     * @param int $city
     * @return array|\PDOStatement|string|\think\Collection
     * 附近楼盘
     */
    private function getNearByHouse($lat,$lng,$city = 0,$num = 4)
    {
        $obj = model('house');
        if($lat && $lng){
            $point      = "*,ROUND(6378.138*2*ASIN(SQRT(POW(SIN(({$lat}*PI()/180-lat*PI()/180)/2),2)+COS({$lat}*PI()/180)*COS(lat*PI()/180)*POW(SIN(({$lng}*PI()/180-lng*PI()/180)/2),2)))*1000) as distance";
            $bindsql    = $obj->field($point)->buildSql();
            $fields_res = 'id,title,price,unit,img,distance';
            $lists      = $obj->table($bindsql.' d')->field($fields_res)->where('status',1)->where('distance','<',2000)->limit(4)->select();
        }else{
            $where['status'] = 1;
            $city && $where['city'] = $city;
            $lists = $obj->where($where)->field('id,title,price,unit,img')->limit(4)->select();
        }
        return $lists;
    }
    /**
     * @param $id
     * @return array|\PDOStatement|string|\think\Collection
     * 统计户型数量
     */
    private function countHouseRoom($id)
    {
        $where['house_id'] = $id;
        $where['status']   = 1;
        $lists = model('house_type')->where($where)->field('id,room,count(id) as total')->order('room')->group('room')->select();
        return $lists;
    }
    /**
     * @param $id
     * @return array
     * 相册分类
     */
    private function getPhotoCate($id)
    {
        $where['house_id'] = $id;
        $where['status']   = 1;
        $field = 'id,cate_id,count(id) as total';
        $lists = model('house_photo')->where($where)->field($field)->group('cate_id')->order(['ordid'=>'asc','id'=>'desc'])->select();
        $data  = [];
        $total = 0;
        if($lists)
        {
            foreach($lists as $k=>$v)
            {
                $total += $v['total'];
                $data[] = [
                    'id'  => $v['id'],
                    'cate_id' => $v['cate_id'],
                    'name'=>getLinkMenuName(6,$v['cate_id']),
                    'total' => $v['total']
                ];
            }
        }
        return ['data'=>$data,'total'=>$total];
    }

    /**
     * @param $id
     * 沙盘信息
     */
    private function getHouseSand($id)
    {
        $where['house_id'] = $id;
        $detail = model('house_sand_pic')->where($where)->find();
        $where['status']   = 1;
        //获取楼栋
        $ban_lists    = model('house_sand')->where($where)->order('id asc')->select();
        $type   = [];
        $ban    = false;
        if(!$ban_lists->isEmpty())
        {
            $ban = $ban_lists[0];
        }
        if($ban && isset($ban['house_type_id']))
        {
            //获取楼栋户型
            $type = model('house_type')->where('id','in',$ban['house_type_id'])->field('title,id,room,living_room,acreage,price')->select();
        }
        $this->assign('ban_type',$type);
        $this->assign('ban_lists',$ban_lists);
        $this->assign('points',$detail);
    }
    /**
     * @param $id
     * @return false|\PDOStatement|string|\think\Collection
     * 相册图
     */
    private function getHousePhoto($id)
    {
        $where['house_id'] = $id;
        $where['status']   = 1;
        $lists = model('house_photo')->where($where)->field('cate_name,url,id')->group('cate_id')->limit(5)->select();
        return $lists;
    }
    /**
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Model
     * 获取楼盘详情
     */
    private function getHouseInfo($id)
    {
        $where['h.status'] = 1;
        $where['h.id']     = $id;
        $obj = model($this->mod);
        $field = 'h.*,hd.attr,hd.info,hd.seo_title,hd.seo_keys,hd.seo_desc';
        $join  = [['house_data hd','h.id = hd.house_id']];
        $info = $obj->alias('h')
            ->join($join)
            ->field($field)
            ->where($where)
            ->find();
        $info && $info['attr'] = json_decode($info['attr'],true);

        return $info;
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
        $service->field = 'h.id,h.img,h.title,h.price,h.unit,h.city';
        $service->city  = $this->getCityChild();
        $service->cate_id = $pos_id;
        $service->num     = $num;
        $lists = $service->lists();
        if($lists)
        {
            foreach($lists as &$v)
            {
                $v['unit'] = getUnitData($v['unit']);
            }
        }
        return $lists;
    }
    /**
     * @return array
     * 搜索条件
     */
    private function search()
    {
        $param['area']       = input('param.area/d', $this->cityInfo['id']);
        $param['price']      = input('param.price',0);
        $param['special']    = input('param.special',0);
        $param['type']       = input('param.type',0);//楼盘类型
        $param['status']     = input('param.status',0);//楼盘状态
        $param['renovation'] = input('param.renovation',0);//装修情况
        $param['metro']      = input('param.metro/d',0);//地铁线
        $param['metro_station'] = input('param.metro_station/d',0);//地铁站点
        $param['sort']       = input('param.sort/d',0);//排序
        $param['discount']   = input('param.discount/d',0);//是否有优惠
        $data['h.status']    = 1;
        $param['area'] == 0 && $param['area'] = $this->cityInfo['id'];
        $param['search_type']   = input('param.search_type/d',1);//查询方式 1按区域查询 2按地铁查询
        $keyword = input('get.keyword');
        $seo_keys = '';
        if(!empty($param['renovation']))
        {
            $data['h.renovation'] = $param['renovation'];
            $seo_keys .= '_'.getLinkMenuName(8,$param['renovation']);
        }
        if(!empty($param['status'])){
            $data['h.sale_status'] = $param['status'];
            $seo_keys .= '_'.getLinkMenuName(1,$param['status']);
        }
        if($param['discount'])
        {
            $data['h.is_discount'] = $param['discount'];
            $seo_keys .= '_优惠楼盘';
        }
        if($keyword){
            $param['keyword'] = $keyword;
            $data[] = ['h.title','like','%'.$keyword.'%'];
            $seo_keys .= '_'.$keyword;
        }
        if($param['search_type'] == 2)
        {
            if(!empty($param['metro']))
            {
                $data['m.metro_id'] = $param['metro'];
                $seo_keys .= '_地铁'.Metro::getMetroName($param['metro']);
                $this->assign('metro_station',Metro::metroStation($param['metro']));
            }else{
                $data[] = ['h.city','in',$this->getCityChild()];
            }
            if(!empty($param['metro_station']))
            {
                $data['m.station_id'] = $param['metro_station'];
                $seo_keys .= '_'.Metro::getStationName($param['metro_station']);
            }
        }else{
            if(!empty($param['area'])){
                $data[] = ['h.city','in',$this->getCityChild($param['area'])];
                $rading = $this->getRadingByAreaId($param['area']);
                //读取商圈
                $param['rading'] = 0;
                if($rading && array_key_exists($param['area'],$rading))
                {
                    $param['rading']  = $param['area'];
                    $param['area']    = $rading[$param['area']]['pid'];
                }
                $param['area']!=$this->cityInfo['id'] && $seo_keys .= '_'.getCityName($param['area'],'').'新楼盘';
                $this->assign('rading',$rading);
            }
        }
        if(!empty($param['price'])){
            $data[] = getHousePrice($param['price']);
            $price  = config('filter.house_price');
            isset($price[$param['price']]) && $seo_keys .= '_'.$price[$param['price']]['name'].'元/平方米';
        }
        if(!empty($param['special'])){
            $data[] = ['','exp',\think\Db::raw("find_in_set({$param['special']},h.tags_id)")];
            $seo_keys .= '_'.getLinkMenuName(3,$param['special']);
        }
        if(!empty($param['type'])){
            $data[] = ['','exp',\think\Db::raw("find_in_set({$param['type']},h.type_id)")];
            $seo_keys .= '_'.getLinkMenuName(2,$param['type']);
        }
        $seo_keys = trim($seo_keys,'_');
        $seo_keys && $this->setSeo(['seo_title'=>$seo_keys,'seo_keys'=>str_replace('_',',',$seo_keys)]);
        $search = $param;
        unset($param['rading']);
        $data = array_filter($data);
        $this->assign('search',$search);
        $this->assign('param',$param);
        return $data;
    }
    private function getSort($sort)
    {
        switch($sort)
        {
            case 0:
                $order = ['h.ordid'=>'asc','h.id'=>'desc'];
                break;
            case 1:
                $order = ['h.price'=>'asc','h.id'=>'desc'];
                break;
            case 2:
                $order = ['h.price'=>'desc','h.id'=>'desc'];
                break;
            case 3:
                $order = ['h.opening_time'=>'desc','h.id'=>'desc'];
                break;
            case 4:
                $order = ['h.opening_time'=>'asc','h.id'=>'desc'];
                break;
            case 5:
                $order = ['h.hits'=>'desc','h.id'=>'desc'];
                break;
            default:
                $order = 'h.ordid asc,h.id desc';
                break;
        }
        return $order;
    }
}
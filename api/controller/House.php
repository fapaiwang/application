<?php

namespace app\api\controller;
use app\common\controller\ApiBase;
class House extends ApiBase
{
    private $pageSize = 15;
    private $mod;
    public function __construct()
    {
        parent::__construct();
        $this->mod = 'house';
    }
    /**
     * @return \think\response\Json
     * 楼盘列表
     */
    public function index()
    {
        $return['code'] = 0;
        $page           = input('get.page/d',1);
        $sort           = input('get.sort/d',0);
        $where          = $this->search();
        $obj            = model($this->mod);
        $field          = 'h.id,h.title,h.is_discount,h.discount,h.img,h.sale_status,h.red_packet,h.city,h.address,h.tags_id,h.price,h.unit';
        $lists          = $obj->alias('h')
                        ->where($where)
                        ->field($field)
                        ->order($this->getSort($sort))
                        ->page($page)
                        ->limit($this->pageSize)
                        ->select();
        $obj->removeOption();
        $count  = $obj->alias('h')
                ->where($where)
                ->order($this->getSort($sort))
                ->group('h.id')->count();
                $total_page = ceil($count/$this->pageSize);
        $red_packet = getSettingCache('site','red_packet');
        if(!$lists->isEmpty())
        {
            foreach($lists as &$v)
            {
                $v['img']     = $this->getImgUrl(thumb($v['img'],400,300));
                $v['city']    = getCityName($v['city'],'-');
                $v['price']   = $v['price'].$v['unit'];
                $tags         = array_filter(explode(',',$v['tags_id']));
                $v['tags']    = $this->getTags(3,$tags);
                $v['sale_status'] = getLinkMenuName(1,$v['sale_status']);
                $v['red_packet']  = ($red_packet == 1 && $v['red_packet'] > 0) ?:0;
            }
            $return['code'] = 200;
        }
        $return['page']       = $page;
        $return['total_page'] = $total_page;
        $return['data']       = $lists;
        return json($return);
    }

    /**
     * @return \think\response\Json
     * 楼盘列表筛选属性
     */
    public function houseAttr()
    {
        $city       = getCity();
        $data['city']       = isset($city[$this->city]['_child']) ? $city[$this->city]['_child'] : $city;
        $data['price']      = getHousePrice();
        $data['type']       = getLinkMenuCache(2);
        $data['special']    = getLinkMenuCache(3);
        $data['renovation'] = getLinkMenuCache(8);
        $data['status']     = getLinkMenuCache(1);
        $data['room']       = getRoom();
        $data['sort']       = [
            0 => ['id'=>0,'name'=>'默认'],
            1 => ['id'=>1,'name'=>'价格从低到高'],
            2 => ['id'=>2,'name'=>'价格从高到低'],
            3 => ['id'=>3,'name'=>'开盘时间降序'],
            4 => ['id'=>4,'name'=>'开盘时间升序']
        ];
        $return['code'] = 1;
        $return['data'] = $data;
        return json($return);
    }

    /**
     * @param $id
     * @return \think\response\Json
     * 楼盘详情
     */
    public function read($id)
    {
        $return['code'] = 0;
        if($id)
        {
            $info = $this->getHouseInfo($id);
            if($info)
            {
                $return['code']  = 200;
                $info['photo']   = $this->getHousePhoto($id);//相册图
                $info['topNews'] = $this->getTopNews($id);//楼盘动态
                $info['question']    = $this->getTopAsk($id);//楼盘问答
                $info['photo_total'] = model('house_photo')->where('house_id',$id)->where('status',1)->count();
                $info['relation']    = $this->getInterestHouse($info['price']);//相关楼盘
                $info['ban']         = $this->getHouseSand($id);//楼盘沙盘
                $info['room']        = $this->getHouseRoom($id);//在售户型
                $info['pano_url']    = base64_encode($info['pano_url']);
                $info['red_packet']  = $info['red_packet'] > 0 && getSettingCache('site','red_packet') == 1 ? $info['red_packet'] : 0;
                $tags         = array_filter(explode(',',$info['tags_id']));
                $info['tags']    = $this->getTags(3,$tags,10);

                $param['lat']   = $info['lat'];
                $param['lng']   = $info['lng'];
                $param['title'] = $info['title'];
                $param['model'] = 'house';
                $param['id']    = $info['id'];
                $map_url = CreateMap::index($param);
                $info['map_img'] = $this->getImgUrl($map_url);
                updateHits($info['id'],'house');
                $return['data'] = $info;
            }
        }
        return json($return);
    }
    /**
     * @return mixed
     * 楼盘相册
     */
    public function photo()
    {
        $page    = input('get.page/d',1);
        $id      = input('get.id/d',0);
        $cate_id = input('get.cate/d',0);
        $return['code'] = 0;
        if(!$id)
        {
            $return['msg'] = '参数错误';
        }else {
            $where['house_id'] = $id;
            $where['status']   = 1;
            $cate_id && $where['cate_id']  = $cate_id;
            //相册列表
            $obj   = model('house_photo');
            $lists = $obj->where($where)->field('cate_name,url')->order(['ordid'=>'asc','id'=>'desc'])->page($page)->limit(15)->select();
            $count = $obj->removeOption()->where($where)->count();
            $total_page = ceil($count/15);//总页数
            if(!$lists->isEmpty())
            {
                foreach($lists as &$v)
                {
                    $v['thumb'] = $this->getImgUrl(thumb($v['url'],150,130));
                    $v['url']   = $this->getImgUrl($v['url']);
                }
                $return['code'] = 200;
            }
            $return['page']       = $page;
            $return['total_page'] = $total_page;
            $return['data'] = $lists;
        }
        return json($return);
    }

    /**
     * @return \think\response\Json
     * 相册分类
     */
    public function photoCate()
    {
        $id = input('get.id/d',0);//楼盘id
        $return['code'] = 0;
        if($id)
        {
            $return['code'] = 200;
            $return['data'] = $this->getPhotoCate($id);
        }else{
            $return['msg'] = '参数错误';
        }
       return json($return);
    }

    /**
     * @return \think\response\Json
     * 楼盘动态
     */
    public function news()
    {
        $page    = input('get.page/d',1);
        $id      = input('get.id/d',0);
        $return['code'] = 0;
        if(!$id)
        {
            $return['msg'] = '参数错误';
        }else{
            $obj = model('article');
            $lists = $obj->where('house_id',$id)->where('status',1)->field('id,title,img,description,create_time')->order('ordid asc,id desc')->page($page)->limit($this->pageSize)->select();
            $count = $obj->removeOption()->where('house_id',$id)->where('status',1)->count();
            $total_page = ceil($count/$this->pageSize);
            $return['page']       = $page;
            $return['total_page'] = $total_page;
            if(!$lists->isEmpty())
            {
                foreach($lists as &$v)
                {
                    $v['img'] = $this->getImgUrl(thumb($v['img'],120,80));
                    $v['create_time_date'] = getTime($v['create_time']);
                }
                $return['code'] = 200;
            }
            $return['data'] = $lists;
        }
        return json($return);
    }

    /**
     * @return \think\response\Json
     * 楼盘户型
     */
    public function room()
    {
        $id = input('get.room_id/d',0);
        $house_id = input('get.house_id/d',0);
        $return['code'] = 0;
        if($id)
        {
            $field = 'title,img,room,living_room,acreage,price,sale_status,characteristic,orientation';
            $info  = model('house_type')->where('house_id',$house_id)->where('id',$id)->where('status',1)->field($field)->find();
            if($info)
            {
                $info['img'] = $this->getImgUrl($info['img']);
                $info['acreage'] = $info['acreage'].config('filter.acreage_unit');
                $info['price']   = $info['price'].'万';
                $info['sale_status'] = getLinkMenuName(5,$info['sale_status']);
                $info['orientation'] = getLinkMenuName(4,$info['orientation']);
            }
            $lists = $this->getHouseRoom($house_id,0);
            $return['code'] = 200;
            $return['data'] = ['data'=>$lists,'info'=>$info];
        }else{
            $return['msg']  = '参数错误';
        }
        return json($return);
    }

    /**
     * @return \think\response\Json
     * 楼盘问答列表
     */
    public function question()
    {
        $id   = input('get.id/d',0);
        $page = input('get.page/d',1);
        $return['code'] = 0;
        if($id)
        {
            $field = "q.id,q.content,count(q.id) as total,q.reply_num,FROM_UNIXTIME(q.create_time,'%Y-%m-%d') as create_time,a.content as answer";
            $where['q.house_id'] = $id;
            $where['q.status']   = 1;
            $join  = [['answer a','a.question_id = q.id','left']];
            $obj   = model('question');
            $lists = $obj->alias('q')->where($where)->field($field)->join($join)->order('q.id','desc')->group('q.id')->page($page)->limit($this->pageSize)->select();
            $total = $obj->removeOption()->alias('q')->where($where)->count();
            $total_page = ceil($total/$this->pageSize);
            $return['code'] = 200;
            $return['page'] = $page;
            $return['total_page'] = $total_page;
            $return['data']       = $lists;
        }else{
            $return['msg'] = '参数错误';
        }
        return json($return);
    }

    /**
     * @return \think\response\Json
     * 问答详细
     */
    public function questionDetail()
    {
        $id             = input('get.id/d',0);
        $return['code'] = 0;
        if($id)
        {
            $field             = "id,content,user_name,house_id,FROM_UNIXTIME(create_time,'%Y-%m-%d') as create_time";
            $where['id']       = $id;
            $where['status']   = 1;
            $obj   = model('question');
            $info  = $obj->where($where)->field($field)->order('id','desc')->find();
            if($info)
            {
                $map['question_id'] = $info['id'];
                $map['status']      = 1;
                $field = "broker_id,broker_name,content,FROM_UNIXTIME(create_time,'%Y-%m-%d') as create_time";
                $lists = model('answer')->where($map)->field($field)->order('create_time desc')->select();
                if(!$lists->isEmpty())
                {
                    foreach($lists as &$v)
                    {
                        $v['img'] = $this->getImgUrl(getAvatar($v['broker_id'],90));
                    }

                }
                $return['code'] = 200;
                $return['info'] = $info;
                $return['answer']= $lists;
            }
        }else{
            $return['msg'] = '参数错误';
        }
        return json($return);
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
        $attr = [];
        $field = 'h.*,hd.attr,hd.info,hd.seo_title,hd.seo_keys,hd.seo_desc';
        $join  = [['house_data hd','h.id = hd.house_id']];
        $info = $obj->alias('h')
            ->join($join)
            ->field($field)
            ->where($where)
            ->find();
        if($info)
        {
            $info['info'] = $this->filterContent($info['info']);
            $info['sale_status'] = getLinkMenuName(1,$info['sale_status']);
            $info['city']        = getCityName($info['city']);
            $info['price']       = $info->getData('price') > 0 ? $info['price'].$info['unit']:$info['price'];
            $info['img'] = $this->getImgUrl($info['img']);
            $info['opening_time'] = $info['opening_time']?date('Y-m-d',$info['opening_time']):'';
            $info['complate_time'] = $info['complate_time']?date("Y-m-d",$info['complate_time']):'';
            $phone = $info['sale_phone']['phone'];
            $extension = $info['sale_phone']['extension'];
            if($extension)
            {
                $phone .= ','.$extension;
            }
            $info['phone'] = $phone;
            $location = $this->turnLocation($info['lat'].','.$info['lng']);
            if($location)
            {
                $info['t_lng'] = $location[0]['lng'];
                $info['t_lat'] = $location[0]['lat'];
            }else{
                $info['t_lng'] = 0;
                $info['t_lat'] = 0;
            }
            $attr = json_decode($info['attr'],true);
            foreach($attr as &$v)
            {
                if(empty($v))
                {
                    $v = '暂无资料';
                }
            }
            $info['attr'] = $attr;
        }
        return $info;
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
        if(!$lists->isEmpty())
        {
            foreach($lists as &$v)
            {
                $v['url'] = $this->getImgUrl($v['url']);
            }
        }
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
     * @return array|null|\PDOStatement|string|\think\Model
     * 获取最新资讯
     */
    private function getTopNews($id)
    {
        $field = 'id,title,description';
        $where['house_id'] = $id;
        $where['status']   = 1;
        $info = model('article')->field($field)->where($where)->order(['ordid'=>'asc','id'=>'desc'])->find();
        return $info;
    }

    /**
     * @param $id
     * @param int $num
     * @return array
     * 最新问答
     */
    private function getTopAsk($id,$num = 2)
    {
        $field = "q.id,q.content,count(q.id) as total,q.reply_num,FROM_UNIXTIME(q.create_time,'%Y-%m-%d') as create_time,a.content as answer";
        $where['q.house_id'] = $id;
        $where['q.status']   = 1;
        $join = [['answer a','a.question_id = q.id','left']];
        $obj   = model('question');
        $lists = $obj->alias('q')->where($where)->field($field)->join($join)->order('q.id','desc')->group('q.id')->limit($num)->select();
        //$total = $obj->alias('q')->where($where)->count();
        return $lists;
    }

    /**
     * @param $price
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 相关楼盘
     */
    private function getInterestHouse($price,$num = 5)
    {
        $where['status'] = 1;
        if($price > 0)
        {
            $where[] = ['price','between',[$price-1000,$price+1000]];
        }
        $this->getCityChild() && $where[] = ['city','in',$this->getCityChild()];
        $field = 'h.id,h.title,h.img,h.sale_status,h.city,h.address,h.tags_id,h.price,h.unit';
        $lists = model($this->mod)->alias('h')
            ->where($where)
            ->field($field)
            ->order('id','desc')
            ->group('h.id')
            ->limit($num)
            ->select();
        if(!$lists->isEmpty())
        {
            foreach($lists as &$v)
            {
                $v['img']  = $this->getImgUrl(thumb($v['img'],120,80));
                $v['city'] = getCityName($v['city']);
                $v['price']   = $v['price'].$v['unit'];
                $tags         = array_filter(explode(',',$v['tags_id']));
                $v['tags']    = $this->getTags(3,$tags);
                $v['sale_status'] = getLinkMenuName(1,$v['sale_status']);
            }
        }
        return $lists;
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
        if($detail)
        {
            $points = $detail['data'];
            if($points)
            {
                foreach($points as &$v)
                {
                    $p = explode(',',$v['point']);
                    $v['point'] = $p;
                }
            }
            $detail['data'] = $points;
            $img = \think\Image::open('.'.$detail['img']);
            $detail['img']   = $this->getImgUrl($detail['img']);
            $detail['width'] = $img->width();
            $detail['height'] = $img->height();
        }
        $data = [
            'ban_type' => $type,
            'ban_lists' => $ban_lists,
            'points'    => $detail
        ];
        return $data;
    }

    /**
     * @param $id
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 在售户型
     */
    private function getHouseRoom($id,$num = 5)
    {
        $where['house_id'] = $id;
        $where['status']   = 1;
        $obj   = model('house_type');
        $obj = $obj->where($where)->field('id,title,room,img,living_room,price,acreage,sale_status')->order('ordid asc,id desc');
        if($num > 0)
        {
            $lists = $obj->limit($num)->select();
        }else{
            $lists = $obj->select();
        }
        if(!$lists->isEmpty())
        {
            foreach($lists as &$v)
            {
                $v['img'] = $this->getImgUrl($v['img']);
                $v['sale_status'] = getLinkMenuName(5,$v['sale_status']);
                $v['price']       = $v['price'].config('acreage_unit');
                $v['acreage']     = $v['acreage'].config('filter.acreage_unit');
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
        $param['city'] = input('param.city/d', $this->city);
        $param['price']      = input('param.price',0);
        $param['special']    = input('param.special',0);
        $param['type']       = input('param.type',0);//楼盘类型
        $param['status']     = input('param.status',0);//楼盘状态
        $param['renovation'] = input('param.renovation',0);//装修情况
        $param['sort']       = input('param.sort/d',0);//排序
        $data['h.status']    = 1;
        $keyword = input('get.keyword');
        if($keyword){
            $param['keyword'] = $keyword;
            $data[] = ['h.title','like','%'.$keyword.'%'];
        }
        if(!empty($param['city'])){
            $data[] = ['h.city','in',$this->getCityChild($param['city'])];
        }
        if(!empty($param['price'])){
            $data[] = getHousePrice($param['price']);
        }
        if(!empty($param['special'])){
            $data[] = ['','exp',\think\Db::raw("find_in_set({$param['special']},h.tags_id)")];
        }
        if(!empty($param['type'])){
            $data[] = ['','exp',\think\Db::raw("find_in_set({$param['type']},h.type_id)")];
        }
        if(!empty($param['status'])){
            $data['h.sale_status'] = $param['status'];
        }
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
            default:
                $order = 'h.ordid asc,h.id desc';
                break;
        }
        return $order;
    }
}
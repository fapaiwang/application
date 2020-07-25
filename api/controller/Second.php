<?php



namespace app\api\controller;

use app\common\controller\ApiBase;



class Second extends ApiBase

{

    private $pageSize = 10;



    /**

     * @return \think\response\Json

     * 二手房列表

     */

    public function index()

    {

        $return['code'] = 0;

        $page           = input('param.page/d',1);

        $where   = $this->search();

        $sort    = input('param.sort/d',0);

        $field   = "id,title,city,estate_name,img,room,living_room,price,tags,address,acreage,renovation";

        $obj     = model('second_house');

        $lists   = $obj->where($where)->field($field)->order($this->getSort($sort))->page($page)->limit($this->pageSize)->select();

        $count   = $obj->removeOption()->where($where)->count();

        $total_page = ceil($count/$this->pageSize);

        if(!$lists->isEmpty())

        {

            $lists = $lists->toArray();

            foreach($lists as &$v)

            {

                $v['price']   = str_replace(['<i>','</i>'],['',''],$v['price']);

                $v['img']     = $this->getImgUrl(thumb($v['img'],120,80));

                $v['city']    = getCityName($v['city'],'-');

                $v['tags']    = array_filter(explode(',',$v['tags']));

                $v['acreage'] = $v['acreage'].config('filter.acreage_unit');

                $v['renovation'] = getLinkMenuName(8,$v['renovation']);

            }

            $return['code'] = 200;

        }

        $return['page']       = $page;

        $return['total_page'] = $total_page;

        $return['data']       = $lists;

        return json($return);

    }

    public function read($id)

    {

        $return['code'] = 0;

        if($id)

        {

            $where['h.id']     = $id;

            $where['h.status'] = 1;

            $obj  = model('second_house');

            $join = [['second_house_data d','h.id=d.house_id']];

            $field = "h.id,h.title,h.estate_name,h.img,d.file,h.room,h.living_room,h.contacts,h.address,h.price,h.user_type,h.average_price,h.tags,h.pano_url,h.renovation,h.floor,h.total_floor,h.orientations,FROM_UNIXTIME(h.create_time,'%Y-%m-%d') as create_time,h.acreage,d.info,d.supporting";

            $info = $obj->alias('h')->join($join)->field($field)->where($where)->find();

            if($info)

            {

                $info = objToArray($info);

                $file = $info['file']?json_decode($info['file'], true):[];

                $info['file']       = $this->turnFileUrl($file);

                $info['img']        = $this->getImgUrl($info['img']);

                $info['renovation'] = getLinkMenuName(8,$info['renovation']);

                $info['floor']      = getLinkMenuName(7,$info['floor']);

                $info['orientations'] = getLinkMenuName(4,$info['orientations']);

                $info['price']        = str_replace(['<i>','</i>'],['',''],$info['price']);

                $info['acreage']      = $info['acreage'].config('filter.acreage_unit');

                $info['tags']         = $this->getTags(14,explode(',',$info['tags'],30));

                $info['pano_url']     = base64_encode($info['pano_url']);

                $info['relation']     = $this->samePriceHouse($info['price']);

                $info['info']         = $this->filterContent($info['info']);

                $supporting           = explode(',',$info['supporting']);

                $info['supporting']   = $this->getTags(12,$supporting,30);

                updateHits($info['id'],'second_house');

                $return['code'] = 200;

            }

            $return['data'] = $info;

        }else{

            $return['msg']  = '参数错误';

        }

        return json($return);

    }

    /**

     * @return \think\response\Json

     * 筛选条件

     */

    public function secondAttr()

    {

        $city               = getCity();

        $data['city']       = isset($city[$this->city]['_child']) ? $city[$this->city]['_child'] : $city;

        $data['price']      = getSecondPrice();

        $data['type']       = getLinkMenuCache(9);

        $data['renovation'] = getLinkMenuCache(8);

        $data['acreage']    = getAcreage();

        $data['sort']       = [

            0 => ['id'=>0,'name'=>'默认'],

            1 => ['id'=>1,'name'=>'总价从低到高'],

            2 => ['id'=>2,'name'=>'总价从高到低'],

            3 => ['id'=>3,'name'=>'均价从低到高'],

            4 => ['id'=>4,'name'=>'均价从高到低'],

            5 => ['id'=>5,'name'=>'面积从低到高'],

            6 => ['id'=>6,'name'=>'面积从高到低']

        ];

        $room = getRoom();

        $room_arr = [];

        foreach($room as $key=>$v)

        {

            $room_arr[$key] = [

              'id' => $key,

                'name' => $v

            ];

        }

        $data['room']   = $room_arr;

        $return['code'] = 1;

        $return['data'] = $data;

        return json($return);

    }

    private function search()

    {

        $estate_id     = input('get.estate_id/d',0);//小区id

        $param['city'] = input('get.city/d', $this->city);

        $param['price']      = input('get.price',0);

        $param['acreage']    = input('get.acreage',0);//面积

        $param['room']       = input('get.room',0);//户型

        $param['type']       = input('get.type',0);//物业类型

        $param['renovation'] = input('get.renovation',0);//装修情况

        $param['sort']       = input('get.sort/d',0);//排序

        $data['status']    = 1;

        $keyword = input('get.keyword');

        if($keyword)

        {

            $param['keyword'] = $keyword;

            $data[] = ['title|estate_name','like','%'.$keyword.'%'];

        }

        if($estate_id)

        {

            $data['estate_id'] = $estate_id;

        }

        if(!empty($param['city']))

        {

            $data[] = ['city','in',$this->getCityChild($param['city'])];

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

        if(!empty($param['type']))

        {

            $data['house_type'] = $param['type'];

        }

        if($param['renovation'])

        {

            $data['renovation'] = $param['renovation'];

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

     * @param $price

     * @param int $num

     * @return array|\PDOStatement|string|\think\Collection

     * 价格相似房源

     */

    private function samePriceHouse($price,$num = 4)

    {

        $min_price = $price > 10 ? $price - 10:$price;

        $max_price = $price + 10;

        $where[] = ['status','eq',1];

        $where[] = ['price','between',[$min_price,$max_price]];

        $this->city && $where[] = ['city','in',$this->getCityChild()];

        $lists = model('second_house')

            ->where($where)

            ->field('id,title,img,estate_name,city,address,room,living_room,acreage,price')

            ->order('create_time desc')

            ->limit($num)

            ->select();

        if(!$lists->isEmpty())

        {

            $lists = $lists->toArray();

            foreach($lists as &$v)

            {

                $v['price']   = str_replace(['<i>','</i>'],['',''],$v['price']);

                $v['img'] = $this->getImgUrl($v['img']);

                $v['acreage'] = $v['acreage'].config('filter.acreage_unit');

            }

        }

        return $lists;

    }

}
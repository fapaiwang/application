<?php




namespace app\mobile\controller;

use app\common\controller\MobileBase;

class House extends MobileBase

{

    private $pageSize = 10;

    private $mod      = 'house';



    /**

     * @return mixed

     * 楼盘列表

     */

    public function index()

    {

        $lists = $this->getLists();

        $this->assign('area',$this->getAreaByCityId());

        $this->assign('special',getLinkMenuCache(3));//特色

        $this->assign('type',getLinkMenuCache(2));//类型

        $this->assign('status',getLinkMenuCache(1));//销售状态

        $this->assign('renovation',getLinkMenuCache(8));//装修情况

        $this->assign('lists',$lists);

        $this->assign('pages',$lists->render());

        $this->assign('total_page',$lists->lastPage());

        $this->assign('storage_open',getSettingCache('storage','open'));

        return $this->fetch();

    }



    /**

     * @return mixed

     * 楼盘详情

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

            $info['photo']   = $this->getHousePhoto($id);

            $info['topNews'] = $this->getTopNews($id);

            $info['question'] = $this->getTopAsk($id);

            $info['photo_total'] = model('house_photo')->where('house_id',$id)->where('status',1)->count();

            $this->getHouseSand($id);

            updateHits($info['id'],'house');

            $this->setSeo($info);

            $this->assign('info',$info);

            $this->assign('id',$id);

            $this->assign('interest',$this->getInterestHouse($info['price']));

            $this->assign('storage_open',getSettingCache('storage','open'));

            return $this->fetch();

        }

    }

    //楼盘动态

    public function news()

    {

        $id = input('param.house_id/d',0);

        if(!$id)

        {

            return $this->fetch('public/404');

        }else{

            $info = $this->getHouseInfo($id);

            if(!$info)

            {

                return $this->fetch('public/404');

            }

            $where['status']   = 1;

            $where['house_id'] = $id;

            $lists = model('article')->where($where)->field('id,title,description,create_time')->order(['ordid'=>'asc','id'=>'desc'])->paginate(10);

            $info['seo_title'] = $info['seo_title'].'新闻动态';

            $this->setSeo($info);

            $this->assign('info',$info);

            $this->assign('id',$id);

            $this->assign('lists',$lists);

            $this->assign('pages',$lists->render());

            $this->assign('title','楼盘动态');

            return $this->fetch();

        }

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

            $room_list = model('house_type')->where($where)->field('id,title,img,room,living_room,orientation,price,sale_status,kitchen,toilet,acreage')->order('id desc')->paginate(10);

            $info['seo_title'] = $info['seo_title'].'在售户型';

            $this->setSeo($info);

            $this->assign('room_list',$room_list);

            $this->assign('pages',$room_list->render());

            $this->assign('room',$room);

            $this->assign('info',$info);

            $this->assign('title','在售户型');

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

                $info      = $this->getHouseInfo($room_info['house_id']);

                $info['seo_title'] = $info['seo_title'].$room_info['title'];

                $this->setSeo($info);

                $this->assign('room_info',$room_info);//户型详细

                $this->assign('info',$info);//楼盘详细

                $this->assign('id',$room_info['house_id']);

                $this->assign('room',$room);

                $this->assign('title',$info['title'].$room_info['title']);

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

            $field = 'q.id,q.content,count(q.id) as total,q.reply_num,q.create_time,a.answer';

            $where['q.house_id'] = $id;

            $where['q.status']   = 1;

            $bind_field = "question_id,content as answer";

            $bind_sql   = model('answer')->field($bind_field)->order('id','desc')->buildSql();

            $join = [

                [$bind_sql.' a','a.question_id = q.id','left']

            ];

            $obj   = model('question');

            $lists = $obj->alias('q')->where($where)->field($field)->join($join)->order('q.id','desc')->group('q.id')->paginate(10);



            $info['seo_title'] = $info['seo_title'].'用户问答';

            $this->setSeo($info);

            $this->assign('info',$info);

            $this->assign('id',$id);

            $this->assign('title','楼盘问答');

            $this->assign('lists',$lists);

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

            $this->assign('title','楼盘问答');

        }

        return $this->fetch();

    }

    /**

     * @return mixed

     * 评论列表

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

            $this->assign('title','楼盘点评');

        }

        return $this->fetch();

    }



    /**

     * @return mixed

     * 发表点评

     */

    public function sendComment()

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

            $this->assign('title','发表点评');

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

            $this->assign('title','楼栋信息');

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

            $lists = model('house_photo')->where($where)->order(['ordid'=>'asc','id'=>'desc'])->paginate(15);



            $info['seo_title'] = $info['seo_title'].'楼盘相册';

            $this->setSeo($info);

            $this->assign('info',$info);

            $this->assign('cate',$this->getPhotoCate($id));//相册分类

            $this->assign('id',$id);

            $this->assign('lists',$lists);

            $this->assign('pages',$lists->render());

            $this->assign('cate_id',$cate_id);

            $this->assign('title','楼盘相册');

        }

        return $this->fetch();

    }

    /**

     * @return \think\response\Json

     * 异步获取楼盘列表

     */

    public function getHouseLists()

    {

        $page    = input('get.page/d',1);

        $data    = $this->getLists($page);

        $lists   = $data['lists'];

        $storage_open = getSettingCache('storage','open');

        if($lists)

        {

            foreach($lists as &$v)

            {

                $is_video  = $v['video'] && $storage_open == 1 ? true : false;

                $v['is_video'] = $is_video;

                $v['url']  = url('House/detail',['id'=>$v['id']]);

                $v['city'] = getCityName($v['city']);

                $v['img']  = thumb($v['img'],200,150);

                $v['sale_status_name'] = getLinkMenuName(1,$v['sale_status']);

                $v['has_red_packet'] = (getSettingCache('site','red_packet') == 1 && $v['red_packet'] > 0) ? true : false;

                $tags = array_filter(explode(',',$v['tags_id']));

                if(is_array($tags))

                {

                    $tag_str = '';

                    foreach($tags as $val)

                    {

                        $tag_str .= '<em>'.getLinkMenuName(3,$val).'</em>';

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



    /**

     * @param int $page

     * @return array|\PDOStatement|string|\think\Collection|\think\Paginator

     * 楼盘列表

     */

    private function getLists($page = 0)

    {

        $sort    = input('param.sort/d',0);

        $where   = $this->search();

        $obj     = model($this->mod);

        $join = [

            ['house_search s','h.id = s.house_id','left']

        ];

        $field = 'h.id,h.title,h.is_discount,h.video,h.discount,h.img,h.sale_status,h.red_packet,h.city,h.address,h.tags_id,h.price,h.unit,s.min_type,s.max_type,s.min_acreage,s.max_acreage';

        $obj = $obj->alias('h')

            ->where($where)

            ->field($field)

            ->join($join)

            ->order($this->getSort($sort))

            ->group('h.id');

        if($page)

        {

            $lists = $obj->page($page)

                ->limit($this->pageSize)

                ->select();

            $obj->removeOption();

            $count   = $obj->alias('h')

                ->where($where)

                ->field($field)

                ->join($join)

                ->order($this->getSort($sort))

                ->group('h.id')->count();

            $total_page = ceil($count/$this->pageSize);

            $lists      = ['lists'=>$lists,'total_page'=>$total_page];

        }else{

            $lists = $obj->paginate($this->pageSize);

        }

            return $lists;

    }

    /**

     * @return array

     * 搜索条件

     */

    private function search()

    {

        $param['area'] = input('param.area/d', $this->cityInfo['id']);

        $param['price']      = input('param.price',0);

        $param['special']    = input('param.special',0);

        $param['type']       = input('param.type',0);//楼盘类型

        $param['status']     = input('param.status',0);//楼盘状态

        $param['renovation'] = input('param.renovation',0);//装修情况

        $param['sort']       = input('param.sort/d',0);//排序

        $param['discount']   = input('param.discount/d',0);//是否有优惠

        $param['area']  == 0 && $param['area'] = $this->cityInfo['id'];

        $data['h.status']    = 1;

        $keyword = input('get.keyword');

        if($keyword){

            $param['keyword'] = $keyword;

            $data[] = ['h.title','like','%'.$keyword.'%'];

        }

        if(!empty($param['area'])){

            $data[] = ['h.city','in',$this->getCityChild($param['area'])];

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

        if($param['discount'])

        {

            $data['h.is_discount'] = $param['discount'];

        }

        $search = $param;

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

            default:

                $order = 'h.ordid asc,h.id desc';

                break;

        }

        return $order;

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

            $img = \think\Image::open('.'.$detail['img']);

            $detail['width'] = $img->width();

            $detail['height'] = $img->height();

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

     * @return array|null|\PDOStatement|string|\think\Model

     * 获取最新资讯

     */

    private function getTopNews($id)

    {

        $field = 'id,title,description,count(id) as total';

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

        $field = 'q.id,q.content,count(q.id) as total,q.reply_num,q.create_time,a.content as answer';

        $where['q.house_id'] = $id;

        $where['q.status']   = 1;

        $join = [['answer a','a.question_id = q.id','left']];

        $obj   = model('question');

        $lists = $obj->alias('q')->where($where)->field($field)->join($join)->order('q.id','desc')->group('q.id')->limit($num)->select();

        $total = $obj->alias('q')->where($where)->count();

        return ['lists'=>$lists,'total'=>$total];

    }

    private function getInterestHouse($price,$num = 5)

    {

        $where['status'] = 1;

        if($price > 0)

        {

            $where[] = ['price','between',[$price-1000,$price+1000]];

        }

        $this->getCityChild() && $where[] = ['city','in',$this->getCityChild()];

        $join = [

            ['house_search s','h.id = s.house_id','left']

        ];

        $field = 'h.id,h.title,h.img,h.sale_status,h.city,h.address,h.tags_id,h.price,h.unit,s.min_type,s.max_type,s.min_acreage,s.max_acreage';

        $lists = model($this->mod)->alias('h')

            ->where($where)

            ->field($field)

            ->join($join)

            ->order('id','desc')

            ->group('h.id')

            ->limit($num)

            ->select();

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

}
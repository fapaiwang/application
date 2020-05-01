<?php
namespace app\api\controller;


use app\common\controller\ApiBase;

class Broker extends ApiBase
{
    private $pageSize = 15;
    public function index()
    {
        $where = $this->search();
        $sort  = input('get.sort/d',0);
        $page  = input('get.page/d',1);
        $return['code'] = 0;
        $field = 'u.id,u.nick_name,u.mobile,d.service_area,d.tags,d.point,(FORMAT((d.point/5)*100,0)) as goods';
        //统计二手房和出租房数量 where条件里需要替换estate_id为estate表每条记录的id, 不能用字符(会被转成 0)所以用9999代替替换
        $second_sql = model('second_house')->where('broker_id','9999')->where('status',1)->field('count(id) as second_total')->buildSql();
        $rental_sql = model('rental')->where('broker_id','9999')->where('status',1)->field('count(id) as second_total')->buildSql();
        $field .= ','.$second_sql.' as second_total,'.$rental_sql.' as rental_total';
        $field  = str_replace('9999','u.id',$field);
        $join   = [['user_info d','u.id=d.user_id'],'left'];
        $obj    = model('user');
        $lists  = $obj
            ->alias('u')
            ->field($field)
            ->join($join)
            ->where($where)
            ->order($this->getSort($sort))
            ->page($page)
            ->limit($this->pageSize)
            ->select();
        $count = $obj->alias('u')->join($join)->where($where)->count();
        $totalPage = ceil($count/$this->pageSize);
        if(!$lists->isEmpty())
        {
            foreach($lists as &$v)
            {
                $area = array_filter(explode(',',$v['service_area']));
                if($area)
                {
                    foreach($area as &$val)
                    {
                        $val = getCityName($val,'-');
                    }
                }
                $v['point'] = array_fill(0,$v['point'],1);
                $v['service_area'] = implode(',',array_filter($area));
                $v['avatar'] = $this->getImgUrl(getAvatar($v['id'],90));
            }
            $return['code'] = 200;

        }
        $return['page'] = $page;
        $return['total_page'] = $totalPage;
        $return['data'] = $lists;
        return json($return);
    }

    /**
     * @return mixed
     * 经纪人详情
     */
    public function read($id)
    {
        $return['code'] = 0;
        if($id)
        {
            $where['broker_id'] = $id;
            $where['status']    = 1;
            $secondObj = model('second_house');
            $rentalObj = model('rental');
            $commentObj = model('user_comment');

            $field = 'id,city,title,estate_name,img,room,living_room,toilet,acreage,price,average_price,address,renovation';
            $second_lists = $secondObj->where($where)->field($field)->order('create_time desc')->limit(4)->select();
            $count['second_total'] = $secondObj->where($where)->count();
            if(!$second_lists->isEmpty())
            {
                $second_lists = $second_lists->toArray();
                foreach($second_lists as &$v)
                {
                    $v['price']   = str_replace(['<i>','</i>'],['',''],$v['price']);
                    $v['city'] = getCityName($v['city'],'-');
                    $v['img']  = $this->getImgUrl($v['img']);
                    $v['renovation'] = getLinkMenuName(8,$v['renovation']);
                    $v['acreage']    = $v['acreage'].'㎡';
                }
            }

            $field = 'id,city,title,estate_name,img,room,living_room,address,toilet,acreage,price,renovation';
            $rental_lists = $rentalObj->where($where)->field($field)->order('create_time desc')->limit(4)->select();
            $count['rental_total'] = $rentalObj->where($where)->count();
            if(!$rental_lists->isEmpty())
            {
                $rental_lists = $rental_lists->toArray();
                foreach($rental_lists as &$v)
                {
                    $v['price']   = str_replace(['<i>','</i>'],['',''],$v['price']);
                    $v['city'] = getCityName($v['city'],'-');
                    $v['img']  = $this->getImgUrl($v['img']);
                    $v['renovation'] = getLinkMenuName(8,$v['renovation']);
                    $v['acreage']    = $v['acreage'].'㎡';
                }
            }


            $field = "id,user_id,user_name,content,point,good,bad,FROM_UNIXTIME(create_time,'%Y-%m-%d %H:%i') as create_time";
            $comment_lists = $commentObj->where($where)->field($field)->order('create_time desc')->limit(4)->select();
            $count['comment_total'] = $commentObj->where($where)->count();
            if(!$comment_lists->isEmpty())
            {
                foreach($comment_lists as &$v)
                {
                    $v['avatar'] = $this->getImgUrl(getAvatar($v['user_id'],90));
                    $v['point']  = array_fill(0,$v['point'],1);
                }
            }


            $data['info'] = $this->getUserInfo($id);
            $data['second'] = $second_lists;
            $data['rental'] = $rental_lists;
            $data['comment'] = $comment_lists;
            $data['count']   = $count;

            $return['code'] = 200;
            $return['data'] = $data;
        }else{
            $return['msg'] = '参数错误';
        }
        return json($return);
    }
    /**
     * @return mixed
     * 二手房列表
     */
    public function second()
    {
        $id = input('get.id/d',0);
        $page = input('get.page/d',1);
        $return['code'] = 0;
        if($id)
        {
            $where['broker_id'] = $id;
            $where['status']    = 1;
            $field = 'id,city,title,estate_name,img,room,living_room,toilet,acreage,price,average_price,renovation,tags';
            $obj   = model('second_house');
            $lists = $obj->where($where)->field($field)->order('create_time desc')->page($page)->limit($this->pageSize)->select();
            $count = $obj->where($where)->count();
            $totalPage = ceil($count/$this->pageSize);
            if(!$lists->isEmpty())
            {
                $lists = $lists->toArray();
                foreach($lists as &$v)
                {
                    $v['city'] = getCityName($v['city'],'-');
                    $v['img']  = $this->getImgUrl($v['img']);
                    $v['renovation'] = getLinkMenuName(8,$v['renovation']);
                    $v['acreage']    = $v['acreage'].'㎡';
                    $v['price']   = str_replace(['<i>','</i>'],['',''],$v['price']);
                }
                $return['code'] = 200;
            }
            $data['info'] = $this->getUserInfo($id);
            $data['info']['count'] = $this->countSecondAndRental($id);
            $data['lists'] = $lists;
            $return['page'] = $page;
            $return['total_page'] = $totalPage;
            $return['data'] = $data;
        }else{
            $return['msg'] = '参数错误';
        }
        return json($return);
    }

    /**
     * @return mixed
     * 出租房
     */
    public function rental()
    {
        $id = input('get.id/d',0);
        $page = input('get.page/d',1);
        $return['code'] = 0;
        if($id)
        {
            $where['broker_id'] = $id;
            $where['status']    = 1;
            $field = 'id,city,title,estate_name,img,room,living_room,toilet,acreage,price,address,renovation';
            $obj   = model('rental');
            $lists = $obj->where($where)->field($field)->order('create_time desc')->page($page)->limit($this->pageSize)->select();
            $count = $obj->where($where)->count();
            $totalPage = ceil($count/$this->pageSize);
            if(!$lists->isEmpty())
            {
                $lists = $lists->toArray();
                foreach($lists as &$v)
                {
                    $v['city'] = getCityName($v['city'],'-');
                    $v['img']  = $this->getImgUrl($v['img']);
                    $v['renovation'] = getLinkMenuName(8,$v['renovation']);
                    $v['acreage']    = $v['acreage'].'㎡';
                    $v['price']   = str_replace(['<i>','</i>'],['',''],$v['price']);
                }
                $return['code'] = 200;
            }
            $data['info'] = $this->getUserInfo($id);
            $data['info']['count'] = $this->countSecondAndRental($id);
            $data['lists'] = $lists;
            $return['page'] = $page;
            $return['total_page'] = $totalPage;
            $return['data'] = $data;
        }else{
            $return['msg'] = '参数错误';
        }
        return json($return);
    }

    /**
     * @return mixed
     * 评论
     */
    public function comment()
    {
        $id = input('get.id/d',0);
        $page = input('get.page/d',1);
        $return['code'] = 0;
        if($id)
        {
            $where['broker_id'] = $id;
            $where['status']    = 1;
            $field = "id,user_id,user_name,content,point,good,bad,tags,FROM_UNIXTIME(create_time,'%Y-%m-%d %H:%i') as create_time";
            $obj   = model('user_comment');
            $lists = $obj->where($where)->field($field)->order('create_time desc')->page($page)->limit($this->pageSize)->select();
            $count = $obj->where($where)->count();
            $totalPage = ceil($count/$this->pageSize);
            if(!$lists->isEmpty())
            {
                foreach($lists as &$v)
                {
                    $v['avatar'] = $this->getImgUrl(getAvatar($v['user_id'],90));
                    $v['point']  = array_fill(0,$v['point'],1);
                }
                $return['code'] = 200;
            }
            $data['info'] = $this->getUserInfo($id);
            $data['info']['count'] = $this->countSecondAndRental($id);
            $data['lists'] = $lists;
            $return['page'] = $page;
            $return['total_page'] = $totalPage;
            $return['data'] = $data;
        }else{
            $return['msg'] = '参数错误';
        }
        return json($return);
    }

    /**
     * @param $id
     * @return array|null|\PDOStatement|string|\think\Model
     * 经纪人信息
     */
    private function getUserInfo($id)
    {
        $where['id'] = $id;
        $info = model('user')->field('id,nick_name,mobile')->where($where)->find();
        $info['avatar'] = $this->getImgUrl(getAvatar($info['id'],90));
        $area = array_filter(explode(',',$info['userInfo']['service_area']));
        if($area)
        {
            foreach($area as &$val)
            {
                $val = getCityName($val,'-');
            }
        }
        $tags = array_filter(explode(',',$info['userInfo']['tags']));
        foreach($tags as &$v)
        {
            $v = getLinkMenuName(13,$v);
        }
        $info['service_area'] = implode(',',array_filter($area));
        $info['point'] = array_fill(0,$info['userInfo']['point'],1);
        $info['tags']  = implode(' ',array_filter($tags));
        return $info;
    }
    /*
     * 统计经纪人二手房出租房数量
     */
    private function countSecondAndRental($id)
    {
        $where['broker_id'] = $id;
        $where['status']    = 1;
        $count['second_total'] = model('second_house')->where($where)->count();
        $count['rental_total'] = model('rental')->where($where)->count();
        return $count;
    }
    private function search()
    {
        $param['city'] = input('param.city/d', $this->city);
        $param['sort']     = input('param.sort/d',0);
        $data['u.status']  = 1;
        $keyword = input('get.keyword');
        if($keyword)
        {
            $param['keyword'] = $keyword;
            $data[] = ['u.nick_name','like','%'.$keyword.'%'];
        }
        if(!empty($param['city']))
        {
            $city   = $this->getCityChild($param['city']);
            $orWhere = '';
            foreach($city as $v)
            {
                $orWhere .= " or find_in_set({$v},d.service_area)";
            }
            $data[] = ['','exp',\think\Db::raw(trim($orWhere,' or '))];

        }
        $data[]   = ['u.model','neq',1];
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
                $order = \think\db::raw('second_total+rental_total desc');
                break;
            case 1:
                $order = ['d.point'=>'desc','u.id'=>'desc'];
                break;
            case 2:
                $order = ['d.point'=>'asc','u.id'=>'desc'];
                break;
            default:
                $order = \think\db::raw('second_total+rental_total desc');
                break;
        }
        return $order;
    }
}
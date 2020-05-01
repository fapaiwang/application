<?php




namespace app\mobile\controller;

use app\common\controller\MobileBase;

class Broker extends MobileBase

{

    public function index()

    {

        $where = $this->search();

        $sort  = input('param.sort/d',0);

        $field = 'u.id,u.nick_name,u.mobile,d.company,d.history_complate,d.service_area,d.tags,d.point,(FORMAT((d.point/5)*100,0)) as goods';

        //统计二手房和出租房数量 where条件里需要替换estate_id为estate表每条记录的id, 不能用字符(会被转成 0)所以用9999代替替换

        $second_sql = model('second_house')->where('broker_id','9999')->where('status',1)->field('count(id) as second_total')->buildSql();

        $rental_sql = model('rental')->where('broker_id','9999')->where('status',1)->field('count(id) as second_total')->buildSql();

        $field .= ','.$second_sql.' as second_total,'.$rental_sql.' as rental_total';

        $field  = str_replace('9999','u.id',$field);

        $join   = [['user_info d','u.id=d.user_id'],'left'];


        $lists  = model('user')

                  ->alias('u')

                  ->field($field)

                  ->join($join)

                  ->where($where)

                  ->order($this->getSort($sort))

                  ->paginate(10);

        $this->assign('lists',$lists);
        // print(1111);
        // print_r($lists[0]['id']);
        // foreach ($lists as $key => $value) {
        //     print_r($value['id']);
        //     print_r(22);
        // }


        $this->assign('pages',$lists->render());

        $this->assign('title','法拍专员列表');

        return $this->fetch();

    }



    /**

     * @return mixed

     * 经纪人详情

     */

    public function detail()

    {

        $id = input('param.id/d',0);

        if($id)

        {

            $where['broker_id'] = $id;

            $where['status']    = 1;

            $secondObj = model('second_house');

            $rentalObj = model('rental');

            $commentObj = model('user_comment');



            $field = 'id,city,title,estate_name,img,room,living_room,toilet,acreage,price,average_price,address,orientations,renovation,tags,marketprice';

            $second_lists = $secondObj->where($where)->field($field)->order('create_time desc')->limit(4)->select();

            $count['second_total'] = $secondObj->where($where)->count();



            $field = 'id,city,title,estate_name,img,room,living_room,toilet,acreage,price,rent_type,address,orientations,renovation,tags';

            $rental_lists = $rentalObj->where($where)->field($field)->order('create_time desc')->limit(4)->select();

            $count['rental_total'] = $rentalObj->where($where)->count();



            $field = 'id,user_id,user_name,content,point,good,bad,tags,create_time';

            $comment_lists = $commentObj->where($where)->field($field)->order('create_time desc')->limit(4)->select();

            $count['comment_total'] = $commentObj->where($where)->count();



            $this->getUserInfo($id);

            $this->assign('second_lists',$second_lists);

            $this->assign('rental_lists',$rental_lists);

            $this->assign('comment_lists',$comment_lists);

            $this->assign('count',$count);

            $this->assign('title','专员名下房源');

        }else{

            return $this->fetch('public/404');

        }

        return $this->fetch();

    }

    /**

     * @return mixed

     * 二手房列表

     */

    public function second()

    {

        $id = input('param.id/d',0);

        if($id)

        {

            $where['broker_id'] = $id;

            $where['status']    = 1;

            $field = 'id,city,title,estate_name,img,room,living_room,toilet,acreage,price,average_price,address,orientations,renovation,tags,marketprice';

            $lists = model('second_house')->where($where)->field($field)->order('create_time desc')->paginate(5);



            $info = $this->getUserInfo($id);

            $this->assign('lists',$lists);

            $this->assign('pages',$lists->render());

            $this->assign('title',$info['nick_name'].'法拍房');

        }else{

            return $this->fetch('public/404');

        }

        return $this->fetch();

    }



    /**

     * @return mixed

     * 出租房

     */

    public function rental()

    {

        $id = input('param.id/d',0);

        if($id)

        {

            $where['broker_id'] = $id;

            $where['status']    = 1;

            $field = 'id,city,title,estate_name,img,room,living_room,toilet,acreage,price,rent_type,address,orientations,renovation,tags';

            $lists = model('rental')->where($where)->field($field)->order('create_time desc')->paginate(5);



            $info = $this->getUserInfo($id);

            $this->assign('lists',$lists);

            $this->assign('pages',$lists->render());

            $this->assign('title',$info['nick_name'].'出租房');

        }else{

            return $this->fetch('public/404');

        }

        return $this->fetch();

    }



    /**

     * @return mixed

     * 评论

     */

    public function comment()

    {

        $id = input('param.id/d',0);

        if($id)

        {

            $where['broker_id'] = $id;

            $where['status']    = 1;

            $field = 'id,user_id,user_name,content,point,good,bad,tags,create_time';

            $lists = model('user_comment')->where($where)->field($field)->order('create_time desc')->paginate(5);



            $info = $this->getUserInfo($id);

            $this->assign('lists',$lists);

            $this->assign('pages',$lists->render());

            $this->assign('title',$info['nick_name'].'的评论');

        }else{

            return $this->fetch('public/404');

        }

        return $this->fetch();

    }

    private function getUserInfo($id)

    {

        $where['id'] = $id;

        $info = model('user')->where($where)->find();

        if(!$info)

        {

            return $this->fetch('public/404');

        }

        $this->assign('userInfo',$info);


        return $info;

    }

    private function search()

    {

        $param['city'] = input('param.city/d', $this->cityInfo['id']);

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
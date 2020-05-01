<?php





namespace app\home\controller;

use app\common\controller\HomeBase;

class Broker extends HomeBase

{

    public function initialize()

    {

        $this->cur_url = 'Second';

        parent::initialize();

        $this->assign('action',request()->action());

    }

    public function index()

    {

        $where = $this->search();

        $sort  = input('param.sort/d',0);

        $field = 'u.id,u.nick_name,u.mobile,u.lxtel,d.description,d.service_area,d.company,d.tags,d.point,(FORMAT((d.point/5)*100,0)) as goods';

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

                  ->paginate(20);


        $this->assign('area',$this->getAreaByCityId());

        $this->assign('tags',getLinkMenuCache(13));

        $this->assign('lists',$lists);

        $this->assign('pages',$lists->render());

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

            $where[] = ['timeout','gt',time()];

            $field = 'id,city,title,estate_id,estate_name,fcstatus,img,room,kptime,living_room,toilet,acreage,price,qipai,average_price,address,orientations,renovation,tags,marketprice,fabutime';

            $lists = model('second_house')->where($where)->field($field)->order(['fcstatus' =>'asc','fabutime' =>'desc'])->paginate(10);
            
            foreach ($lists as $key => $value) {
                $estate_id=$lists[$key]['estate_id'];

               $sql=model('estate')->where('id','eq',$estate_id)->alias('years')->find();
               $years=$sql['years'];
               $lists[$key]['years']=$years;



                

           }






       

















            $this->getUserInfo($id);

            $this->assign('lists',$lists);

            $this->assign('pages',$lists->render());

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

            $where[] = ['timeout','gt',time()];

            $field = 'id,title,estate_name,img,room,living_room,toilet,acreage,price,rent_type,address,orientations,renovation,tags';

            $lists = model('rental')->where($where)->field($field)->order('create_time desc')->paginate(10);



            $this->getUserInfo($id);

            $this->assign('lists',$lists);

            $this->assign('pages',$lists->render());

        }else{

            return $this->fetch('public/404');

        }

        return $this->fetch();

    }

    /**

     * @return mixed

     * 写字楼出售

     */

    public function office()

    {

        $id = input('param.id/d',0);

        if($id)

        {

            $where['broker_id'] = $id;

            $where['status']    = 1;

            $where[] = ['timeout','gt',time()];

            $field = 'id,title,estate_name,img,acreage,price,type,address,renovation,tags';

            $lists = model('office')->where($where)->field($field)->order('create_time desc')->paginate(10);



            $this->getUserInfo($id);

            $this->assign('lists',$lists);

            $this->assign('pages',$lists->render());

        }else{

            return $this->fetch('public/404');

        }

        return $this->fetch();

    }

    /**

     * @return mixed

     * 写字楼出

     */

    public function officeRental()

    {

        $id = input('param.id/d',0);

        if($id)

        {

            $where['broker_id'] = $id;

            $where['status']    = 1;

            $where[] = ['timeout','gt',time()];

            $field = 'id,title,estate_name,img,acreage,price,type,address,renovation,tags';

            $lists = model('office_rental')->where($where)->field($field)->order('create_time desc')->paginate(10);



            $this->getUserInfo($id);

            $this->assign('lists',$lists);

            $this->assign('pages',$lists->render());

        }else{

            return $this->fetch('public/404');

        }

        return $this->fetch();

    }

    /**

     * @return mixed

     * 商铺出售

     */

    public function shops()

    {

        $id = input('param.id/d',0);

        if($id)

        {

            $where['broker_id'] = $id;

            $where['status']    = 1;

            $where[] = ['timeout','gt',time()];

            $field = 'id,title,estate_name,img,acreage,price,type,address,renovation,tags';

            $lists = model('shops')->where($where)->field($field)->order('create_time desc')->paginate(10);



            $this->getUserInfo($id);

            $this->assign('lists',$lists);

            $this->assign('pages',$lists->render());

        }else{

            return $this->fetch('public/404');

        }

        return $this->fetch();

    }

    /**

     * @return mixed

     * 商铺出租

     */

    public function shopsRental()

    {

        $id = input('param.id/d',0);

        if($id)

        {

            $where['broker_id'] = $id;

            $where['status']    = 1;

            $where[] = ['timeout','gt',time()];

            $field = 'id,title,estate_name,img,acreage,price,type,address,renovation,tags';

            $lists = model('shops_rental')->where($where)->field($field)->order('create_time desc')->paginate(10);



            $this->getUserInfo($id);

            $this->assign('lists',$lists);

            $this->assign('pages',$lists->render());

        }else{

            return $this->fetch('public/404');

        }

        return $this->fetch();

    }

    /**

     * @return mixed

     * 点评

     */

    public function comment()

    {

        $id = input('param.id/d',0);

        if($id)

        {

            $where['broker_id'] = $id;

            $where['status']    = 1;

            $field = 'id,user_id,user_name,content,point,good,bad,tags,create_time';

            $lists = model('user_comment')->where($where)->field($field)->order('create_time desc')->paginate(10);



            $this->getUserInfo($id);

            $this->assign('impression',getLinkMenuCache(13));

            $this->assign('lists',$lists);

            $this->assign('pages',$lists->render());

        }else{

            return $this->fetch('public/404');

        }

        return $this->fetch();

    }



    /**

     * @param $id

     * 获取用户信息

     */

    private function getUserInfo($id)

    {

        $where['id'] = $id;

        $info = model('user')->where($where)->find();

        $this->assign('brokerInfo',$info);

    }



    /**

     * @return array

     * 搜索条件

     */

    private function search()

    {

        $param['area']     = input('param.area/d', $this->cityInfo['id']);

        $param['rading']   = 0;

        $param['tags']     = input('param.tags');

        $param['sort']     = input('param.sort/d',0);

        $data['u.status']  = 1;

        $param['area'] == 0 && $param['area'] = $this->cityInfo['id'];

        $keyword = input('get.keyword');

        if($keyword)

        {

            $param['keyword'] = $keyword;

            $data[] = ['u.nick_name','like','%'.$keyword.'%'];

        }

        if(!empty($param['area']))

        {

            $city   = $this->getCityChild($param['area']);

            $orWhere = '';

            foreach($city as $v)

            {

                $orWhere .= " or find_in_set({$v},d.service_area)";

            }

            $data[] = ['','exp',\think\Db::raw(trim($orWhere,' or '))];

            $rading = $this->getRadingByAreaId($param['area']);

            //读取商圈

            if($rading && array_key_exists($param['area'],$rading))

            {

                $param['rading']  = $param['area'];

                $param['area']    = $rading[$param['area']]['pid'];

            }

            $this->assign('rading',$rading);

        }

        if($param['tags'])

        {

            $tags = array_filter(explode(',',$param['tags']));

            $orWhere = '';

            foreach($tags as $v)

            {

                $orWhere .= " or ".\think\Db::raw("find_in_set('{$v}',d.tags)");

            }

            $orWhere = trim($orWhere,' or ');

            $data[] = ['','exp',\think\Db::raw($orWhere)];

            $param['tags'] = $tags;

        }

        $data[]   = ['u.model','neq',1];

        $search = $param;

        unset($param['rading'],$param['tags']);

        $this->assign('search',$search);

        $this->assign('param',$param);

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
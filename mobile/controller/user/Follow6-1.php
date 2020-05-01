<?php



namespace app\mobile\controller\user;

class Follow extends UserBase

{

    public function initialize()

    {

        parent::initialize();

        $this->assign('title','我的关注');

    }

    /**

     * @return mixed

     * 关注新房列表

     */

    public function index()

    {

        $field = 'h.id,h.title,h.img,h.sale_status,h.city,h.address,h.tags_id,h.price,h.unit,s.min_type,s.max_type,s.min_acreage,s.max_acreage';

        $lists = $this->getLists('house',$field);

        $this->assign('lists',$lists);

        $this->assign('pages',$lists->render());

        return $this->fetch();

    }



    /**

     * @return mixed

     * 关注二手房列表

     */

    public function second()

    {

        $field = "h.id,h.title,h.estate_name,h.img,h.city,h.room,h.living_room,h.toilet,h.price,h.average_price,h.tags,h.address,h.acreage,h.orientations,h.renovation";

        $lists = $this->getLists('second_house',$field);

        $this->assign('lists',$lists);

        $this->assign('pages',$lists->render());

        return $this->fetch();

    }



    /**

     * @return mixed

     * 关注出租房列表

     */

    public function rental()

    {

        $field = "h.id,h.title,h.estate_name,h.city,h.img,h.room,h.living_room,h.toilet,h.price,h.rent_type,h.tags,h.address,h.acreage,h.orientations,h.renovation";

        $lists = $this->getLists('rental',$field);

        $this->assign('lists',$lists);

        $this->assign('pages',$lists->render());

        return $this->fetch();

    }

    /**

     * @return mixed

     * 关注写字楼出售 列表

     */

    public function office()

    {

        $field = "h.id,h.title,h.estate_name,h.img,h.city,h.price,h.average_price,h.address,h.acreage,h.type,h.renovation";

        $lists = $this->getLists('office',$field);

        $this->assign('lists',$lists);

        $this->assign('pages',$lists->render());

        return $this->fetch();

    }

    /**

     * @return mixed

     * 关注写字楼出租 列表

     */

    public function officeRental()

    {

        $field = "h.id,h.title,h.estate_name,h.img,h.city,h.price,h.average_price,h.address,h.acreage,h.type,h.renovation";

        $lists = $this->getLists('office_rental',$field);

        $this->assign('lists',$lists);

        $this->assign('pages',$lists->render());

        return $this->fetch();

    }

    /**

     * @return mixed

     * 关注商铺出售 列表

     */

    public function shops()

    {

        $field = "h.id,h.title,h.estate_name,h.img,h.city,h.price,h.average_price,h.address,h.acreage,h.type,h.renovation";

        $lists = $this->getLists('shops',$field);

        $this->assign('lists',$lists);

        $this->assign('pages',$lists->render());

        return $this->fetch();

    }

    /**

     * @return mixed

     * 关注商铺出租 列表

     */

    public function shopsRental()

    {

        $field = "h.id,h.title,h.estate_name,h.img,h.city,h.price,h.average_price,h.address,h.acreage,h.type,h.renovation";

        $lists = $this->getLists('shops_rental',$field);

        $this->assign('lists',$lists);

        $this->assign('pages',$lists->render());

        return $this->fetch();

    }

    /**

     * @return mixed

     * 关注小区列表

     */

    public function estate()

    {

        $field = 'h.id,h.title,h.city,h.img,h.house_type,h.years,h.address,h.price,h.complate_num';

        //统计二手房和出租房数量 where条件里需要替换estate_id为estate表每条记录的id, 不能用字符(会被转成 0)所以用9999代替替换

        $second_sql = model('second_house')->where('estate_id','9999')->where('status',1)->field('count(id) as second_total')->buildSql();

        $rental_sql = model('rental')->where('estate_id','9999')->where('status',1)->field('count(id) as second_total')->buildSql();

        $field .= ','.$second_sql.' as second_total,'.$rental_sql.' as rental_total';

        $field  = str_replace('9999','h.id',$field);

        $lists = $this->getLists('estate',$field);

        $this->assign('lists',$lists);

        $this->assign('pages',$lists->render());

        return $this->fetch();

    }

    /**

     * @param $model

     * @param $field

     * @return \think\Paginator

     * 列表

     */

    private function getLists($model,$field)

    {

        $obj = model('follow');

        $where['f.user_id'] = $this->userInfo['id'];

        $where['f.model']   = $model;

        $join = [[$model.' h','f.house_id=h.id']];

        $field .= ',f.create_time';

        if($model == 'house')

        {

            $join = [

                [$model.' h','f.house_id=h.id'],

                ['house_search s','h.id = s.house_id','left']

            ];

        }

        $lists = $obj->alias('f')

            ->where($where)

            ->join($join)

            ->field($field)
            ->group('house_id')

            ->order('f.create_time','desc')

            ->paginate(10);

        return $lists;

    }

}
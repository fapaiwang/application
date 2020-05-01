<?php



namespace app\mobile\controller\user;

class Zuji extends UserBase

{

    public function initialize()

    {

        parent::initialize();

        $this->assign('title','浏览足迹');

    }

    /**

     * @return mixed

     * 新房列表

     */

   
    public function index()

    {

        $field = "distinct(h.id),h.title,h.estate_name,h.img,h.city,h.room,h.living_room,h.toilet,h.price,h.average_price,h.tags,h.address,h.acreage,h.orientations,h.renovation";

        $lists = $this->getLists('second_house',$field);
// print_r($lists);
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

        $obj = model('zuji');

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
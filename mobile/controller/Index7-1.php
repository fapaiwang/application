<?php


namespace app\mobile\controller;

use app\common\controller\MobileBase;

class Index extends MobileBase

{

    public function index()

    {

        $full_screen = db('module')->field('id')->where('type',1)->where('status',1)->where('terminal',2)->order('ordid asc')->select();

        $this->assign('module',$full_screen);

        //正在进行
        $zzjx = model('second_house')->where('fcstatus',169)->count();
        $this->assign('zzjx',$zzjx);
        //即将拍卖
        $jjpm = model('second_house')->where('fcstatus',170)->count();
        $this->assign('jjpm',$jjpm);



        $this->assign('news',$this->getTopNews());

        $this->assign('group',$this->getNewGroup(5));

        $this->assign('house',$this->getReconnonHouse(1));

        $this->assign('time',time());

        $this->assign('city',$this->getCity());
		
		
	
		
		

        return $this->fetch();

    }



    /**

     * @param int $num

     * @return array|\PDOStatement|string|\think\Collection

     * 资讯

     */

    private function getTopNews($num = 5)

    {

        $where['status'] = 1;

        $where[] = ['city','eq',$this->cityInfo['id']];

        $lists = model('article')->where($where)->order(['ordid'=>'asc','id'=>'desc'])->limit($num)->select();

        return $lists;

    }



    /**

     * @param $cate_id

     * @param int $num

     * @return array|\PDOStatement|string|\think\Collection

     * 推荐新房

     */

    private function getReconnonHouse($cate_id,$num=5)

    {

        $where['p.status']  = 1;

        $where['p.cate_id'] = $cate_id;

        $where['h.status']  = 1;

        $where['p.model']   = 'house';

        $this->getCityChild() && $where[] = ['h.city','in',$this->getCityChild()];

        $join  = [

            ['house h','p.house_id = h.id'],

            ['house_search s','s.house_id = h.id','left']

        ];

        $field = 'h.id,h.is_discount,h.discount,h.img,h.title,h.tags_id,h.price,h.unit,h.city,s.min_type,s.max_type,s.min_acreage,s.max_acreage';

        $lists = model('position')->alias('p')

            ->join($join)

            ->where($where)

            ->field($field)

            ->order( ['h.ordid'=>'asc','h.id'=>'desc'])

            ->limit($num)

            ->select();

        return $lists;

    }



    /**

     * @param int $num

     * @return array|\PDOStatement|string|\think\Collection

     * 最新团购

     */

    private function getNewGroup($num = 4)

    {

        $time            = time();

        $where['status'] = 1;

        $where[]         = ['begin_time','lt',$time];

        $where[]         = ['end_time','gt',$time];

        $this->getCityChild() && $where[] = ['city','in',$this->getCityChild()];

        $lists = model('group')->where($where)->field('id,city,title,end_time,price,discount,img')->order(['ordid'=>'asc','id'=>'desc'])->limit($num)->select();

        return $lists;

    }

    /**

     * @return array

     * 按字母顺序排列的全部城市

     */

    private function getCity()

    {

        $city = getCity();

        $hot  = [];

        $city_arr = [];

        foreach($city as $v)

        {

            if($v['is_hot'] == 1)

            {

                $hot[] = $v;

            }

            $first = strtoupper(substr($v['alias'],0,1));

            $city_arr[$first][] = $v;

        }

        ksort($city_arr);

        return ['hot'=>$hot,'city'=>$city_arr];

    }

}
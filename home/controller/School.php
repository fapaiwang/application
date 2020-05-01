<?php

namespace app\home\controller;
use app\common\controller\HomeBase;
class School extends HomeBase
{
    public function initialize()
    {
        $this->cur_url = 'Second';
        parent::initialize();
    }

    /**
     * @return mixed
     * 列表
     */
    public function index()
    {
        $where   = $this->search();
        $keyword = input('get.keyword');
        $lists   = model('school')->where($where)->field('id,title,city,img,type,address')->order(['ordid'=>'asc','id'=>'desc'])->paginate(20,false,['query'=>['keyword'=>$keyword]]);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('area',$this->getAreaByCityId());
        $this->assign('type',getLinkMenuCache(22));//类型
        $this->assign('school_house',$this->getSchoolHouse());
        return $this->fetch();
    }

    /**
     * @return mixed
     * 详情页
     */
    public function detail()
    {
        $id = input('param.id/d',0);
        if($id)
        {
            $where['id'] = $id;
            $where['status'] = 1;
            $info = model('school')->where($where)->find();
            updateHits($id,'school');
            $this->setSeo($info);
            $this->assign('count',$this->countSchoolHouse($id));
            $this->assign('new_house',$this->getSchoolHouse('house',10));
            $this->assign('second_house',$this->getSchoolHouse('second_house',10));
            $this->assign('rental_house',$this->getSchoolHouse('rental',10));
            $this->assign('info',$info);
        }else{
            return $this->fetch('public/404');
        }
        return $this->fetch();
    }
    /**
     * @return array
     * 搜索条件
     */
    private function search()
    {
        $param['area']       = input('param.area/d', $this->cityInfo['id']);
        $param['type']       = input('param.type',0);//类型
        $keyword             = input('param.keyword');
        $param['area'] == 0 && $param['area'] = $this->cityInfo['id'];
        $data['status'] = 1;
        if(!empty($param['type']))
        {
            $data['type'] = $param['type'];
        }
        if(!empty($param['area']))
        {
            $data[] = ['city','in',$this->getCityChild($param['area'])];
        }
        if($keyword)
        {
            $param['keyword'] = $keyword;
            $data[] = ['title','like','%'.$keyword.'%'];
        }
        $search = $param;
        $this->assign('search',$search);
        $this->assign('param',$param);
        return $data;
    }

    /**
     * @param $school_id
     * @return array|null|\PDOStatement|string|\think\Model
     * 统计学区房数量
     */
    private function countSchoolHouse($school_id)
    {
        $field  = "count(case when model='house' then model end) as house_total,";
        $field .= "count(case when model='second_house' then model end) as second_house_total,";
        $field .= "count(case when model='rental' then model end) as rental_total";
        $info   = model('school_relation')->where('school_id',$school_id)->field($field)->find();
        return $info;
    }
    /**
     * @return array|\PDOStatement|string|\think\Collection
     * 学区房
     */
    private function getSchoolHouse($model = 'second_house',$limit = 5)
    {
        $page = input('get.page/d',1);
        $obj = model('school_relation');
        $join = [[$model.' h','h.id = s.house_id']];
        $where = [
            'h.status' => 1,
            's.model' => $model
        ];
        if($model == 'house')
        {
            $field = 'h.id,h.title,h.address,h.price,h.unit,h.type_id';
        }else{
            $field = 'h.id,h.title,h.address,h.estate_name,h.img,h.price,h.room,h.living_room,h.acreage';
            $where[] = ['h.timeout','gt',time()];
        }

        $lists = $obj->alias('s')
                    ->join($join)
                    ->field($field)
                    ->where($where)
                    ->page($page)
                    ->limit($limit)
                    ->order('h.id','desc')
                    ->select();
        return $lists;
    }
}
<?php

namespace app\common\service;


class Position
{
    public $where = [];
    public $field;
    public $model = 'house';
    public $cate_id;
    public $order = ['p.ordid'=>'asc','h.id'=>'desc'];
    public $city  = 0;
    public $num   = 6;//读取数据条数
    private $time_limit = ['second_house','rental','office','office_rental','shops','shops_rental'];
    public function lists()
    {
        if(strpos($this->field,'h.price')!==FALSE)
        {
            $replace = "(case h.price when 0 then '待定' else h.price end) as price";
            $this->field = str_replace('h.price',$replace,$this->field);
        }
        if(empty($this->where))
        {
            $where['p.status']  = 1;
            $where['p.cate_id'] = $this->cate_id;
            $where['h.status']  = 1;
            $where['p.model']   = $this->model;
            $this->city && $where[] = ['h.city','in',$this->city];
            if(in_array($this->model,$this->time_limit))
            {
                $where[] = ['timeout','gt',time()];
            }
            $this->where = $where;
        }

        $join  = [[$this->model.' h','p.house_id = h.id']];
        $lists = model('position')->alias('p')
            ->join($join)
            ->where($this->where)
            ->field($this->field)
            ->order($this->order)
            ->limit($this->num)
            ->select();
        $this->where = [];
        return $lists;
    }
}
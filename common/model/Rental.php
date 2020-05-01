<?php

namespace app\common\model;


class Rental extends \think\Model
{
    protected $autoWriteTimestamp = true;
    protected $type = [
        'contacts' => 'json'
    ];
    protected function setTagsAttr($value)
    {
        $value = str_replace('，',',',$value);
        return $value;
    }
    protected function rentalData(){
        return $this->hasOne('rental_data','house_id','id')->joinType('left');
    }
    protected function getPriceAttr($value)
    {
        if(!$value){
            return '面议';
        }else{
            return $value."<i>".config('filter.rental_price_unit')."</i>";
        }
    }
    protected function setTimeoutAttr($value)
    {
        if($value)
        {
            $value = $value == -1 ? '2147483647' : strtotime("+ ".$value." days");
        }
        return $value;
    }
    public function checkTitleExists($data)
    {
        $data['estate_id'] && $where['estate_id'] = $data['estate_id'];
        $where['room']        = $data['room'];
        $where['living_room'] = $data['living_room'];
        $data['toilet'] && $where['toilet']      = $data['toilet'];
        isset($data['id']) && $where[]       = ['id','neq',$data['id']];
        $count = $this->where($where)->count();
        return $count;
    }
}
<?php




namespace app\common\model;





class SecondHouse extends \think\Model

{

    protected $autoWriteTimestamp = true;

    protected $type = [

        'contacts' => 'json'

    ];

    protected function secondHouseData(){

        return $this->hasOne('second_house_data','house_id')->joinType('left');

    }

    protected function getPriceAttr($value)

    {

        if(!$value){

            return '面议';

        }else{

            return $value;

        }

    }

    protected function getAveragePriceAttr($value)

    {

        if(!$value){

            return '';

        }

        return $value.'<i>'.config('filter.second_price_unit').'</i>';

    }

    protected function setTimeoutAttr($value)

    {

        if($value)

        {

            $value = $value == -1 ? '2147483647' : strtotime("+ ".$value." days");

        }

        return $value;

    }

    protected function setTagsAttr($value)

    {

        $value = str_replace('，',',',$value);

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
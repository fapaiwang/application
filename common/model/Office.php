<?php

namespace app\common\model;


class Office extends \think\Model
{
    protected $autoWriteTimestamp = true;
    protected function getPriceAttr($value)
    {
        if(!$value){
            return '面议';
        }else{
            return $value.'<i>万</i>';
        }
    }
    protected function getAveragePriceattr($value)
    {
        if(!$value)
        {
            return '';
        }else{
            return $value.config('filter.second_price_unit');
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
}
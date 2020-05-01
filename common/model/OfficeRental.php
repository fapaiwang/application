<?php


namespace app\common\model;


class OfficeRental extends \think\Model
{
    protected $autoWriteTimestamp = true;
    protected function setTimeoutAttr($value)
    {
        if($value)
        {
            $value = $value == -1 ? '2147483647' : strtotime("+ ".$value." days");
        }
        return $value;
    }
    protected function getPriceAttr($value)
    {
        if(!$value){
            return '面议';
        }else{
            return $value.'<i>'.config('filter.rental_price_unit').'</i>';
        }
    }
    protected function getAveragePriceAttr($value)
    {
        if(!$value){
            return '';
        }
        return $value.config('filter.office_rental_unit');
    }
}
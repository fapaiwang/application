<?php

namespace app\mobile\controller;


use app\common\controller\MobileBase;

class Tools extends MobileBase
{
    public function index()
    {
        $this->assign('title','房贷计算器');
        return $this->fetch();
    }
    public function houseTrend()
    {
        $this->assign('ups_downs_house',$this->getUpsAndDownsHouse());//新盘涨幅
        $this->assign('ups_downs_second_house',$this->getUpsAndDownsSecondHouse());//二手房涨幅
        $this->assign('title','查房价');
        return $this->fetch();
    }
    /**
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 新盘涨幅
     */
    private function getUpsAndDownsHouse($num = 8)
    {
        $where['status'] = 1;
        $where[] = ['ratio','<>',0];
        $this->getCityChild() && $where[] = ['city','in',$this->getCityChild()];
        $field = 'id,title,ratio,price,unit';
        $order = ['ordid'=>'asc','id'=>'desc'];
        $lists = model('house')->where($where)->field($field)->order($order)->limit($num)->select();
        return $lists;
    }

    /**
     * @param int $num
     * @return array|\PDOStatement|string|\think\Collection
     * 二手房涨幅
     */
    private function getUpsAndDownsSecondHouse($num = 8)
    {
        $where['status'] = 1;
        $where[] = ['ratio','<>',0];
        $this->getCityChild() && $where[] = ['city','in',$this->getCityChild()];
        $field = 'id,title,ratio,average_price';
        $order = ['ordid'=>'asc','id'=>'desc'];
        $lists = model('second_house')->where($where)->field($field)->order($order)->limit($num)->select();
        return $lists;
    }
}
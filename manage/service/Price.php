<?php

namespace app\manage\service;
class Price
{
    /**
     * @param $estate_id
     * 计算该 小区二手房均价
     */
    public static function calculationPrice($estate_id = 0)
    {
        $field = "count(id) as total,sum(average_price) as total_price";
        $where['estate_id'] = $estate_id;
        $where[] = ['price','gt',0];
        $info    = model('second_house')->where($where)->field($field)->find();
        if($info)
        {
            $obj = model('estate');
            if($info['total_price'] > 0)
            {
                $average_price = ceil($info['total_price'] / $info['total']);
                //得到该小区上一次均价
                $prev_price = $obj->where(['id'=>$estate_id])->value('price');
                $ratio = 0;
                if($prev_price > 0)
                {
                    //计算涨幅比
                    $ratio = number_format(($average_price - $prev_price) / $prev_price * 100,1);
                }
                $data['price'] = $average_price;
                $data['ratio'] = $ratio;
                $obj->save($data,['id'=>$estate_id]);
            }

        }

    }
}
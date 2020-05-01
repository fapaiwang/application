<?php

namespace app\common\model;
class Province extends \think\Model
{
    public function getLists()
    {
        $where['status'] = 1;
        $lists = $this->where($where)->field('id,name')->order(['ordid'=>'asc','id'=>'desc'])->select();
        return $lists;
    }
}
<?php

namespace app\common\model;


class SecondHouseExtension extends \think\Model{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time'; //指定时间字段
    protected $updateTime = false;
}
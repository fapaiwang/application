<?php


namespace app\common\model;


class Group extends \think\Model
{
    protected $type = [
      'file' => 'json'
    ];
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
}
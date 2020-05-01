<?php
namespace app\common\model;


class Comment extends \think\Model
{
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    protected $type = [
        'score' => 'json'
    ];
}
<?php



namespace app\common\model;



class Community extends \think\Model

{

    // 设置单独的数据库连接 todo 数据库单独连接
     protected $connection = [
        'type' => 'mysql',
        'hostname' => 'rm-m5e68c32plrc3m174lo.mysql.rds.aliyuncs.com',
        'database' => 'www_fapaiwang_cn',
        'username' => 'www_fangpaiwang',
        'password' => 'zEHfdrxJ6nXMzamr',
        /*'hostname' => '127.0.0.1',
        'database' => 'www_fapaiwang_cn',
        'username' => 'root',
        'password' => 'root',*/
        'hostport' => '',
        'params' => [],
        'charset' => 'utf8',
        'prefix' => 'web_',
        'debug' => true,
    ];
    protected $autoWriteTimestamp = false;

    protected $updateTime = false;

}
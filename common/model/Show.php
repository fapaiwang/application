<?php

/**

 * 海南俱进科技有限公司

 * TPHOUSE房产系统

 * User: junyv QQ:1074857902

 * Date: 2018/1/11

 * Time: 19:20

 * Subscribe.php

 * version:v1.0.0

 */



namespace app\common\model;





class Show extends \think\Model
{
    // 设置单独的数据库连接
     protected $connection = [
        'type' => 'mysql',
        'hostname' => 'rm-m5e68c32plrc3m174lo.mysql.rds.aliyuncs.com',
        'database' => 'www_fapaiwang_cn',
        'username' => 'www_fangpaiwang',
        'password' => 'zEHfdrxJ6nXMzamr',
        /*'hostname'        => '127.0.0.1',
        'database'        => 'www_fapaiwang_cn',
        'username' => 'root',
        'password'        => 'root',*/
        'hostport' => '',
        'params' => [],
        'charset' => 'utf8',
        'prefix' => 'web_',
        'debug' => true,
    ];

    protected $autoWriteTimestamp = false;

    protected $updateTime = false;

}
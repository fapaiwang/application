<?php


namespace app\common\service;


class Account
{
    /**
     * @param $user_id
     * @param $data
     * @param int $op
     * 余额变动验证
     */
    public static function optionMoney($user_id,$data,$op = 1)
    {
        $obj  = model('user');
        $info = $obj->where('id',$user_id)->field('model,money,timeout')->find();
        $price = $data['price'];
        $return['code'] = 0;
        if($info)
        {
            try{
            //充值
                if($op == 1)
                {
                    $edit['money'] = \think\Db::raw('money+'.$price);
                    $record['user_id'] = $user_id;
                    $record['money']   = $price;
                    $record['memo']    = $data['memo'];
                    $record['op']      = $op;
                    if($obj->save($edit,['id'=>$user_id])){
                        self::record($record);
                        $return['code'] = 1;
                    }else{
                        $return['msg'] = '系统异常！';
                    }
                }else{
                    if($info['money'] < $price)
                    {
                        $return['msg']  = '余额不足，请先充值';
                    }else{
                        $edit['money']     = \think\Db::raw('money-'.$price);
                        $record['user_id'] = $user_id;
                        $record['money']   = $price;
                        $record['memo']    = $data['memo'];
                        $record['op']      = $op;
                        if($obj->save($edit,['id'=>$user_id])){
                            self::record($record);
                            $return['code'] = 1;
                        }else{
                            $return['msg'] = '系统异常！';
                        }
                    }
                }
            }catch(\Exception $e){
                \think\facade\Log::write('余额变动出错:'.$e->getMessage().$e->getFile().$e->getLine());
                $return['msg'] = $e->getMessage();
            }
        }else{
            $return['msg'] = '用户不存在';
        }
        return $return;
    }

    /**
     * @param $data
     * 添加变动记录
     */
    public static function record($data)
    {
        $data['create_time'] = time();
        $data['ip']  = request()->ip();
        model('blance_record')->allowField(true)->isUpdate(false)->save($data);
    }

    /**
     * @param $data
     * 登录日志
     */
    public static function log($data)
    {
        $data['login_ip'] = request()->ip();
        $data['login_time'] = time();
        $city = \util\Ip::find($data['login_ip']);
        $data['city'] = $city[1].$city[2];
        model('user_log')->save($data);
    }
}
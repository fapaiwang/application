<?php

namespace app\api\controller;

use \think\Request;
class Subscribe extends \think\Controller
{
    /**
     * @return \think\response\Json
     * 获取楼盘信息
     */
    public function index()
    {
        $id = input('get.id/d',0);
        $return['code'] = 0;
        if($id)
        {
            $info = model('house')->where('id',$id)->where('status',1)->field('id,title')->find();
            if($info)
            {
                $return['code'] = 200;
            }
            $token = sha1(codestr(10));
            cache('api_token',$token,300);
            $return['data']     = $info;
            $return['send_sms'] = getSettingCache('user','subscribe_sms');
            $return['token']    = $token;
        }else{
            $return['msg'] = '参数错误';
        }
        return json($return);
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     * 保存提交信息
     */
    public function save(Request $request)
    {
        $token = request()->header('token');
        $data['house_id']  = $request->house_id;
        $data['user_name'] = $request->user_name;
        $data['mobile']    = $request->mobile;
        $data['type']      = $request->type;
        $data['house_name'] = $request->title;
        $data['broker_id']  = $this->getBrokerId($data['house_id']);
        $data['model']      = 'house';
        $code = $request->code;
        $return['code'] = 0;
        if($token != cache('api_token'))
        {
            $return['msg'] = '数据校验失败';
        }elseif(!is_mobile($data['mobile']))
        {
            $return['msg'] = '手机号码格式错误！';
        }elseif(getSettingCache('user','subscribe_sms') == 1 && (empty($code) || cache($data['mobile']) != $code)){
            $return['msg'] = '手机验证码错误';
        }else{
            if(model('subscribe')->allowField(true)->save($data))
            {
                if($data['type'] == 4)
                {
                    model('group')->where('house_id',$data['house_id'])->where('status',1)->setInc('sign_num');
                }
                cache('api_token',null);
                action('home/Sms/sendNoticeSms',['data'=>$data]);
                $return['code'] = 200;
                $return['msg']  = '提交成功';
                cache($data['mobile'],null);
            }else{
                $return['msg']  = '提交失败';
            }
        }
        return json($return);
    }
    private function getBrokerId($id)
    {
        $broker_id        = 0;
        $id && $broker_id = model('house')->where('id',$id)->value('broker_id');
        return $broker_id;
    }
}
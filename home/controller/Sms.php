<?php
namespace app\home\controller;

use think\facade\Log;

class Sms
{
    /**
     * @param $config
     * @param $mobile
     * @param $contentParam
     * @return mixed
     * 云信
     */
    public function yunXinSms($config,$mobile,$contentParam){
        $api = new \org\Sms();
        //变量模板ID
        $template = $config['verify'];
        //发送变量模板短信
        Log::write('-======短信通道-云信======='.\GuzzleHttp\json_encode($mobile));
        Log::write('-======短信通道-云信======='.\GuzzleHttp\json_encode($contentParam));
        Log::write('-======短信通道-云信======='.\GuzzleHttp\json_encode($template));
        $result = $api->send($mobile,$contentParam,$template);
        if($result['stat']=='100'){
            $return['code'] = 1;
            $return['data'] = $contentParam;
            $return['msg']  = '验证码发送成功，请注意查收！';
        }else{
            //echo '发送失败:'.$result['stat'].'('.$result['message'].')';
            $return['data'] = $result;
            $return['msg']  = '短信发送失败';
        }
        return $return;
    }



    /**

     * @param $mobile

     * @param $data

     * @return mixed

     * 阿里云短信

     */

    private function aliSms($mobile,$data)

    {
        Log::write('-======短信通道-aly======='.\GuzzleHttp\json_encode($mobile));
        Log::write('-======短信通道-aly======='.\GuzzleHttp\json_encode($data));

        $result       = \org\AliSms::sendSms($mobile,$data);

        if($result->Code == 'OK')

        {

            $return['code'] = 1;

            $return['data'] = $data;

            $return['msg']  = '验证码发送成功，请注意查收！';

        }else{

            $return['data'] = $result;

            $return['msg']  = '短信发送失败';

        }

        return $return;

    }

    /**
     * @return \think\response\Json
     * 发送短信验证码
     */
    public function sendSms(){
        $mobile = input('param.mobile');
        $exists = input('param.exists/d',0);
        $token = input('param.token');
        if(empty($this->set_sms_ip($mobile))){
            return "";
        }
        $return['code'] = 0;
        if(session('__token__')!==$token){
            $return['msg'] = '操作失败T';
        }
        if(!is_mobile($mobile)){
            $return['msg'] = '手机号码格式不正确！';
        }elseif($exists == 2 && !checkMobileIsExists($mobile)){
            $return['msg'] = '该用户不存在！';
        }else{
            $code = codestr(6,1);
            cache($mobile,$code,300);
            $data['code'] = $code;
            try{
                $smsConfig = getSettingCache('sms');
                if($smsConfig['sms_type'] == 1){
                    //阿里云
                    $return = $this->aliSms($mobile,$data);
                }else{
                    //云信
                    $return = $this->yunXinSms($smsConfig,$mobile,$data);
                }
            }catch(\Exception $e){
                $return['code'] = 0;
                $return['msg']  = $e->getMessage();
            }
        }
        return  json($return);
    }

    /**
     * 防盗刷短信
     * @param mixed
     * @return \think\response\Json
     * @author: al
     */
    public function set_sms_ip($mobile){
        $ip_01 = request()->ip();
        $ip = $_SERVER["REMOTE_ADDR"];
        $badip = model("badip")->where("ip",$ip_01)->count();
        if ($badip){
            return 0;
        }
        $sms_recode =model("sms_recode")->where("ip_01",$ip_01)->count();
        if ($sms_recode >= 5){
            model("badip")->insert(["ip"=>$ip_01]);
            return 0;
        }
        $info = [
            "model" =>$mobile,
            "info" => json_encode($_SERVER),
            "ip" => $ip,
            "ip_01" => $ip_01,
        ];
        return model("sms_recode")->insert($info);
    }

    public function sendSmsGet(){
        $mobile = input('get.mobile');
        $exists = input('get.exists/d',0);
        $return['code'] = 0;
        if(!is_mobile($mobile)){
            $return['msg'] = '手机号码格式不正确！';
        }elseif($exists == 2 && !checkMobileIsExists($mobile)){
            $return['msg'] = '该用户不存在！';
        }else{
            $code = codestr(6,1);
            cache($mobile,$code,300);
            $data['code'] = $code;
            try{
                $smsConfig = getSettingCache('sms');
                if($smsConfig['sms_type'] == 1){
                    //阿里云
                    $return = $this->aliSms($mobile,$data);
                }else{
                    //云信
                    $return = $this->yunXinSms($smsConfig,$mobile,$data);
                }
            }catch(\Exception $e){
                $return['code'] = 0;
                $return['msg']  = $e->getMessage();
            }
        }
        return  json($return);
    }

    /**

     * @param array $data

     * 预约通知短信

     */

    public function sendNoticeSms($data = [])

    {

        $config = getSettingCache('sms');

        $model  = $data['model'];//input('post.model');

        $mobile = $data['mobile'];//input('post.mobile');

        $type   = $data['type'];//input('post.type/d',1);

        $id     = $data['house_id'];//input('post.house_id/d',0);

        if($config['sms_type'] == 2 && $config['notice'])

        {

            try{

                $field = 'title';

                if($model == 'second_house' || $model == 'rental')

                {

                    $field .= ',contacts';

                }

                $info = model($model)->where('id',$id)->field($field)->find();

                $param = [

                    'user' => $mobile,

                    'type' => subscribeType($type),

                    'name' => $info['title']

                ];

                if(!isset($info['contacts']))

                {

                    $send_mobile = $config['notice_mobile'];

                }else{

                    $send_mobile = $info['contacts']['contact_phone'];

                }

                $api = new \org\Sms();

                //变量模板ID

                $template = $config['notice'];

                //发送变量模板短信

                $result = $api->send($send_mobile,$param,$template);

            }catch (\Exception $e){

                \think\facade\Log::write('预约通知发送失败：'.$e->getMessage(),'error');

            }

        }

    }



}
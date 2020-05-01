<?php


namespace app\manage\controller;


class GetOssToken
{
    public function index()
    {
        $type = input('get.type');
        $setting = getSettingCache('storage');
        if($setting['open'] == 0)
        {
            return json(['code'=>0,'msg'=>'请先开启云存储再上传']);
        }
        if($type == 'alioss')
        {
           return $this->alioss($setting);
        }elseif($type == 'qiniu'){
           return $this->qiniu($setting);
        }else{
            return json(['code'=>0,'msg'=>'存储类型错误']);
        }
    }
    private function alioss($config)
    {
        $id   = $config['access_key'];          // 请填写您的AccessKeyId。
        $key  = $config['secret_key'];     // 请填写您的AccessKeySecret。
        // $host的格式为 bucketname.endpoint，请替换为您的真实信息。
        $host = $config['domain'];
        // $callbackUrl为上传回调服务器的URL，请将下面的IP和Port配置为您自己的真实URL信息。
        $callbackUrl = 'http://www.'.config('url_domain_root').'/notify/alioss.html';
        $dir = 'video/'.date('Y-m-d').'/';          // 用户上传文件时指定的前缀。

        $callback_param = array('callbackUrl'=>$callbackUrl,
            'callbackBody'=>'filename=${object}&size=${size}&mimeType=${mimeType}',
            'callbackBodyType'=>"application/x-www-form-urlencoded");
        $callback_string = json_encode($callback_param);

        $base64_callback_body = base64_encode($callback_string);
        $now    = time();
        $expire = 30;  //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问。
        $end    = $now + $expire;
        $expiration = $this->gmt_iso8601($end);


        //最大文件大小.用户可以自己设置
        $condition    = [0=>'content-length-range', 1=>0, 2=>1048576000];
        $conditions[] = $condition;

        // 表示用户上传的数据，必须是以$dir开始，不然上传会失败，这一步不是必须项，只是为了安全起见，防止用户通过policy上传到别人的目录。
        $start        = [0=>'starts-with', 1=>'$key', 2=>$dir];
        $conditions[] = $start;


        $arr    = ['expiration'=>$expiration,'conditions'=>$conditions];
        $policy = json_encode($arr);
        $base64_policy  = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

        $response = [];
        $response['accessid']  = $id;
        $response['host']      = $host;
        $response['policy']    = $base64_policy;
        $response['signature'] = $signature;
        $response['expire']    = $end;
        $response['callback']  = $base64_callback_body;
        $response['dir']       = $dir;  // 这个参数是设置用户上传文件时指定的前缀。
        return json(['code'=>1,'data'=>$response]);
    }
    private function qiniu($config)
    {
        $auth = new \Qiniu\Auth($config['access_key'], $config['secret_key']);
        $filename = "video/".date("Y-m-d").'/'.codestr(32);
        $pro  = ['saveKey'=>$filename];
        $upToken = $auth->uploadToken($config['bucket'], null, 3600, $pro, true);
        $data['uptoken'] = $upToken;
        $data['domain']  = $config['domain'];
        $data['filename'] = $filename;
        return json(['code'=>1,'data'=>$data]);
    }
    private function gmt_iso8601($time) {
        $dtStr = date("c", $time);
        $mydatetime = new \DateTime($dtStr);
        $expiration = $mydatetime->format(\DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration."Z";
    }
}
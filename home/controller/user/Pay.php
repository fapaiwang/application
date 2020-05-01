<?php

namespace app\home\controller\user;
use app\common\controller\UserBase;
require_once env('vendor_path')."alipay/f2fpay/model/builder/AlipayTradePrecreateContentBuilder.php";
require_once env('vendor_path')."alipay/f2fpay/service/AlipayTradeService.php";
class Pay extends UserBase
{
    public function index()
    {
        return $this->fetch();
    }

    /**
     * @return \think\response\Json
     * 创建订单
     */
    public function createOrder()
    {
        $pay_type = input('post.pay_type');
        $money    = input('post.money');
        $return['code'] = 0;
        if(!$money || !is_numeric($money))
        {
            $return['msg'] = '金额只能为数字';
        }else{
            if($pay_type == 'weixin')
            {
                $return = $this->weixinPay($money);
                $return['qrcode'] = url('createCode').'?data='.$return['weiurl'];
            }else{
                $return = $this->aliPay($money);
            }
        }
        if($return['code'] == 1)
        {
            $data = $return['data'];
            $data['pay_type'] = $pay_type;
            $data['money']    = $money;
            $this->saveOrder($data);
        }
        return json($return);
    }

    /**
     * @return \think\response\Json
     * 查询订单
     */
    public function queryOrder()
    {
        $return['code'] = 0;
        $order_no = input('get.order_no');
        $info = model('user_pay')->where('order_no',$order_no)->where('user_id',$this->userInfo['id'])->where('pay_status',0)->find();
        if($info)
        {
            if($info['pay_type'] == 'weixin')
            {
                $return = $this->queryWeixinPay($order_no);
            }elseif($info['pay_type'] == 'alipay'){
                $return = $this->queryAliPay($order_no);
            }else{
                $return['msg'] = '不存在的支付方式';
            }
        }else{
            $return['msg'] = '订单不存在或已支付';
        }
        if($return['code'] == 1 && $return['data']['total_fee'] == $info['money'])
        {
            \think\Db::startTrans();
            try{
                $data['trade_num']  = $return['data']['transaction_id'];
                $data['pay_time']   = time();
                $data['pay_status'] = 1;
               if(model('user_pay')->save($data,['order_no'=>$order_no,'pay_status'=>0]))
               {
                   $options = [
                       'price' => $info['money'],
                       'memo'  => '账户充值'
                   ];
                   \app\common\service\Account::optionMoney($this->userInfo['id'],$options);
               }else{
                   $return['code'] = 0;
               }
                \think\Db::commit();
            }catch (\Exception $e){
                \think\facade\Log::write('账户异常：'.$e->getMessage());
                $return['msg'] = $e->getMessage();
                \think\Db::rollback();
            }
        }
        return json($return);
    }

    /**
     * @param $order_no
     * @return mixed
     * 查询微信支付
     */
    private function queryWeixinPay($order_no)
    {
        $return['code'] = 0;
        try{
            $setting = getSettingCache('pay');
            $config  = $setting['weixin'];
            $wechat = new \WeChat\Pay($config);
            $options = [
                'out_trade_no' => $order_no
            ];
            $result = $wechat->queryOrder($options);
            if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS' && $result['trade_state'] == 'SUCCESS')
            {
                $return['code'] = 1;
                $return['data']['total_fee']       = $result['total_fee'] / 100;
                $return['data']['transaction_id']  = $result['transaction_id'];
            }else{
                $return['msg'] = '查询失败';
            }
        }catch(\Exception $e){
            \think\facade\Log::write('查询微信订单出错：'.$e->getMessage(),'error');
            $return['code']  = 0;
            $return['msg'] = $e->getMessage();
        }
        return $return;
    }

    /**
     * @param $order_no
     * @return mixed
     * 查询支付宝支付
     */
    private function queryAliPay($order_no)
    {
        $return['code'] = 0;
        try{
            $config = $this->getConfig('alipay');
            $queryContentBuilder = new \AlipayTradeQueryContentBuilder();
            $queryContentBuilder->setOutTradeNo($order_no);
            //初始化类对象，调用queryTradeResult方法获取查询应答
            $queryResponse = new \AlipayTradeService($config);
            $queryResult = $queryResponse->queryTradeResult($queryContentBuilder);
            if($queryResult->getTradeStatus() == 'SUCCESS')
            {
                $return['code'] = 1;
                $return['msg']  = 'success';
                $result = $queryResult->getResponse();
                $return['data']['total_fee']       = $result->total_amount;
                $return['data']['transaction_id']  = $result->trade_no;
            }else{
                $return['msg'] = $queryResult->getTradeStatus();
            }
        }catch(\Exception $e){
            \think\facade\Log::write('查询支付宝支付出错：'.$e->getMessage(),'error');
            $return['code'] = 0;
            $return['msg']  = '查询失败';
        }
        return $return;
    }
    /**
     * @param $money
     * @return mixed
     * 微信支付
     */
    private function weixinPay($money)
    {
        $return['code'] = 0;
        try{
            $config  = $this->getConfig();
            $order_no = $this->createOrderNo();
            $wechat = new \WeChat\Pay($config);
            $options = [
                'body'             => '账户充值',
                'out_trade_no'     => $order_no,
                'total_fee'        => $money*100,
                'trade_type'       => 'NATIVE',
                'notify_url'       => url('Notify/weixin'),
                'spbill_create_ip' => request()->ip(),
            ];
            // 生成预支付码
            $result = $wechat->createOrder($options);
            if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS')
            {
                $return['code'] = 1;
                $return['data']['order_no']  = $order_no;
                $return['data']['prepay_id'] = $result['prepay_id'];
                $return['weiurl'] = $result['code_url'];
            }else{
                $return['code'] = 0;
                $return['msg']  = $result['return_msg'];
            }
        }catch(\Exception $e){
            \think\facade\Log::write('微信支付出错:'.$e->getMessage(),'error');
            $return['msg'] = $e->getMessage();
        }
        return $return;
    }

    /**
     * @param $money
     * @return mixed
     * 支付宝支付
     */
    private function aliPay($money)
    {
        $return['code'] = 0;
        try{
            $config = $this->getConfig('alipay');
            $order_no = $this->createOrderNo();
            $timeExpress = "5m";
            $qrPayRequestBuilder = new \AlipayTradePrecreateContentBuilder();
            $qrPayRequestBuilder->setOutTradeNo($order_no);
            $qrPayRequestBuilder->setTotalAmount($money);
            $qrPayRequestBuilder->setTimeExpress($timeExpress);
            $qrPayRequestBuilder->setSubject('账户充值');
            $qrPay = new \AlipayTradeService($config);
            $qrPayResult = $qrPay->qrPay($qrPayRequestBuilder);
            if ($qrPayResult->getTradeStatus() == 'SUCCESS') {
                $response = $qrPayResult->getResponse();
                $return['code']  = 1;
                $return['qrcode'] = url('createCode').'?data='.$response->qr_code;
                $return['data']['order_no']   = $order_no;
            } else {
                \think\facade\Log::write($qrPayResult->getResponse(),'error');
                \think\facade\Log::write('生成支付宝二维码出错：'.$qrPayResult->getResponse()->sub_msg, 'error');
                $return['code'] = 0;
                $return['msg']  = $qrPayResult->getResponse()->sub_msg;
            }
        }catch(\Exception $e){
            \think\facade\Log::write('生成支付宝二维码异常：'.$e->getFile().$e->getLine().$e->getMessage(),'error');
            $return['msg'] = $e->getMessage();
        }
        return $return;
    }
    /**
     * @return string
     * 生成订单号
     */
    private function createOrderNo()
    {
        $str = date("YmdHis").codestr(6,1);
        return $str;
    }

    /**
     * @param $data
     * 生成订单
     */
    private function saveOrder($data)
    {
        $data['create_time'] = time();
        $data['user_id']     = $this->userInfo['id'];
        $data['memo']        = '账户充值';
        model('user_pay')->save($data);
    }
    //生成二维码
    public function createCode(){
        $url = input('get.data');
        if($url){
            require_once env('vendor_path')."phpqrcode/phpqrcode.php";
            \QRcode::png($url);
        }else{
            echo '';
        }
    }

    /**
     * @param string $key
     * @return mixed
     * 获取支付配置
     */
    private function getConfig($key = 'weixin')
    {
        $setting = getSettingCache('pay');
        $config  = $setting[$key];
        if($key == 'alipay')
        {
            $config['gatewayUrl'] = "https://openapi.alipay.com/gateway.do";
            $config['charset']    = "UTF-8";
            $config['sign_type']  = "RSA2";
            $config['notify_url'] = url('Notify/alipay');
            $config['MaxQueryRetry'] = "10";
            $config['QueryDuration'] = 3;
        }
        return $config;
    }
}
<?php

namespace app\home\controller\user;
use app\common\controller\UserBase;
use app\common\service\PublishCount;
class Index extends UserBase
{
    public function index()
    {
        $info = $this->userInfo();
        $this->assign('model',$info['model_name']);
        return $this->fetch();
    }

    /**
     * @return mixed
     * 用户中心首页
     */
    public function pannel()
    {
        $count = PublishCount::count($this->userInfo['id']);//房源发布统计
        $info  = model('pages')->where('cate_id',4)->find();//会员权益

        $userInfo = $this->userInfo();
        $level = model('user_cate')->where('id',$userInfo['model'])->value('ordid');
        $check = [0=>'是',1=>'否'];
        $this->assign('user_cate',getUserCate());
        $this->assign('check',$check);
        $this->assign('info',$info);
        $this->assign('userInfo',$userInfo);
        $this->assign('count',$count);
        $this->assign('level',$level);
        return $this->fetch();
    }

    /**
     * @return mixed
     * 登录日志
     */
    public function log()
    {
        $where['user_id'] = $this->userInfo['id'];
        $lists = model('user_log')->where($where)->paginate(15);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }

    /**
     * @return \think\response\Json
     * 会员升级
     */
    public function upgrade()
    {
        $return['code'] = 0;
        //\think\Db::startTrans();
        try{
            $level = input('get.id/d',0);//升级级别
            $obj   = model('user_cate');
            $userInfo = $this->userInfo();
            $cur_level = $obj->where('id',$userInfo['model'])->value('ordid');
            $up_level  = $obj->where('id',$level)->value('ordid');
            if($cur_level >= $up_level)
            {
                $return['msg'] = '升级失败，当前级别大于待升级级别';
            }else{
                $setting = getUserCate();
                $setting = $setting[$level];
                $price   = $setting['fee'];
                $result = \app\common\service\Account::optionMoney($this->userInfo['id'],['price'=>$price,'memo'=>'升级扣除金额'],-1);
                if($result['code'] == 1)
                {
                    db('user')->where(['id'=>$this->userInfo['id']])->update(['model'=>$level]);
                    $return['code'] = 1;
                    $return['msg']  = '升级成功';
                }else{
                    $return['code'] = 0;
                    $return['msg'] = $result['msg'];
                }
            }
            //\think\Db::commit();
        }catch(\Exception $e){
            \think\facade\Log::write('升级出错：'.$e->getFile().$e->getLine().$e->getMessage(),'error');
            $return['code'] = 0;
            $return['msg']  = $e->getMessage();
            //\think\Db::rollback();
        }
        return json($return);
    }
    /**
     * @return array|null|\PDOStatement|string|\think\Model
     * 获取用户信息
     */
    private function userInfo()
    {
        $where['id'] = $this->userInfo['id'];
        $info = model('user')->where($where)->field('id,nick_name,model,money,login_time,login_ip')->find();
        $user_cate = getUserCate();//会员等级
        $model_name     = isset($user_cate[$info['model']])?$user_cate[$info['model']]['name']:'';
        $city    = \util\Ip::find($info['login_ip']);
        $info['city']     = $city[1].$city[2];
        $info['model_name'] = $model_name;
        return $info;
    }
}
<?php

namespace app\manage\controller;

use app\home\service\UserService;
use app\manage\service\ShareService;
use think\facade\Log;

class Share extends  \think\Controller{
    protected $share_service;

    public function __construct(ShareService $ss)
    {
        parent::__construct();
        $this->share_service=$ss;
    }

    /**
     * 首页
     * @param mixed
     * @return mixed
     * @author: al
     */
    public function index(){
        $user =  model('user')->field('share_img,user_name,mobile')->where([['model','=',4]])->select();

        $this->assign('user',$user);

        return $this->fetch('share/index');
    }


    /**
     * 每日新增
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author: al
     */
    public function add(){
        $data = input('get.');
        //获取用户信息
        $res = $this->share_service->get_user($data);

        $time_start = date('Y-m-d').' '.'00:00:00';
        $time_end = date('Y-m-d').' '.'23:59:59';

        $lists = model('second_house')->field('id,title,acreage,qipai,price,cjprice,types')
            ->where([['fabutime','>',$time_start],['fabutime','<',$time_end]])->select();
        //计算lists中的数据
        if ($lists){
            $lists = $this->share_service->im_list($lists);
        }

        $this->assign('list',$lists);
        $this->assign('res',$res);
        return $this->fetch('share/add');
    }

    /**
     * 每日成交
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author: al
     */
    public function deal(){
        $data = input('get.');
        //获取用户信息
        $res = $this->share_service->get_user($data);

        $time_start = date('Y-m-d').' '.'00:00:00';
        $time_end = date('Y-m-d').' '.'23:59:59';
        $lists = model('second_house')->field('id,title,acreage,qipai,price,cjprice,types')
            ->where([['fcstatus','=',175],['endtime','>',$time_start],['endtime','<',$time_end]])->select();
        if ($lists){
            $lists = $this->share_service->im_list($lists);
        }
        $this->assign('list',$lists);
        $this->assign('res',$res);
        return $this->fetch('share/deal');
    }

    /**
     * 周推荐
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author: al
     */
    public function week_recommended(){
        $data = input('get.');
        //获取用户信息
        $res = $this->share_service->get_user($data);

        $lists = model('second_house')->field('id,title,acreage,qipai,price,cjprice,types')
            ->where([['rec_position','=',1]])->select();
        //计算lists中的数据

        if ($lists){
            $lists = $this->share_service->im_list($lists);
        }
        $this->assign('list',$lists);
        $this->assign('res',$res);
        return $this->fetch('share/week_recommended');
    }
    /**
     * 周自由购
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author: al
     */
    public function week_free(){
        $data = input('get.');
        //获取用户信息
        $res = $this->share_service->get_user($data);
        //上周开始时间
         $time_start =  strtotime('-2 monday', time());
         $time_end =  strtotime('-1 monday', time());

        $lists = model('second_house')->field('id,title,acreage,qipai,price,cjprice,types')
            ->where([['is_free','=',1],['fabutimes','>',$time_start],['fabutimes','<',$time_end]])->select();
        //计算lists中的数据
        if ($lists){
            $lists = $this->share_service->im_list($lists);
        }
        $this->assign('list',$lists);
        $this->assign('res',$res);
        return $this->fetch('share/week_free');
    }

    /**
     * 周商业
     * @param mixed
     * @return mixed
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     */
    public function week_business(){
        $data = input('get.');
        //获取用户信息
        $res = $this->share_service->get_user($data);
        //上周开始时间-结束时间
        $time_start =  strtotime('-2 monday', time());
        $time_end =  strtotime('-1 monday', time());
//
        $lists = model('second_house')->field('id,title,acreage,qipai,price,cjprice,types,tags')
            ->where([['types','=',18333],['fabutimes','>',$time_start],['fabutimes','<',$time_end]])->limit(20)->select()->toArray();
        //计算lists中的数据
        if ($lists){
            $lists = $this->share_service->im_list($lists);
        }
        $this->assign('list',$lists);
        $this->assign('res',$res);
        return $this->fetch('share/week_business');
    }

    public function test(){
        $user_ser = new UserService();
        dd($user_ser->getUserInfo());
    }



}
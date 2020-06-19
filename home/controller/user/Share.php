<?php

namespace app\home\controller\user;

use app\common\controller\UserBase;
use app\home\service\IndexServer;
use app\home\service\UserService;
use app\manage\service\ShareService;
use think\Db;
use think\facade\Log;

class Share extends  UserBase {
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
        $id= $this->userInfo['id'];
        if ($id == 550){
            $id =0;
        }
        $data['name'] =$id;
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
        return $this->fetch('user/share/add');
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
        $id= $this->userInfo['id'];
        if ($id == 550){
            $id =0;
        }
        $data['name'] =$id;
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
        return $this->fetch('user/share/deal');
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
        $id= $this->userInfo['id'];
        if ($id == 550){
            $id =0;
        }
        $data['name'] =$id;
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
        return $this->fetch('user/share/week_recommended');
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
        $id= $this->userInfo['id'];
        if ($id == 550){
            $id =0;
        }
        $data['name'] =$id;
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
        return $this->fetch('user/share/week_free');
    }

    /**
     * 周商业
     * @param mixed
     * @return mixed
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNoFtoundException
     * @throws \think\exception\DbException
     *
     */
    public function week_business(){
        $id= $this->userInfo['id'];
        if ($id == 550){
            $id =0;
        }
        $data['name'] =$id;
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
        return $this->fetch('user/share/week_business');
    }

    public function test(){
        $user_ser = new UserService();
        dd($user_ser->getUserInfo());
    }

    /**
     * 每周公司分享
     * @param mixed
     * @return mixed|string
     * @author: al
     */
    public function company_week_recommended(){
        $IndexServer = new IndexServer();
        //时间
        $time["year"] = date("Y");
        $time['s_time']=date('m/d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600));
        $time['e_time']=date('m/d', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));

        //查询数据
        $recommended =model("a_zhoutuijian")->select();
        $res = Db::query('select max(pid) as num from fang_a_zhoutuijian');
        if (empty($res[0]["num"])){
            return "统计房源城市pid错误";
        }
        //颜色
        $color["background"] ="#77bec9";
        $color["city"] ="#8ec7d0";
        $color["one_line"] ="#bdd9dd";
        $color["double_row"] ="#FFFFFF";
        //背景图
        $back_img = $IndexServer->get_home_banner_x(28,1);
//dd($back_img->setting["fileurl"]);
        $arr = array_range(1, $res[0]["num"], 1);
        foreach ($arr as $key=>$val){
            $num =0;
            $last_num = 0;
            foreach ($recommended as $k=>$v){
                if (!empty($v->city)){
                    $last_num = $k;
                }
                if ($val == $v->pid){
                    $num++;
                    $recommended[$k]["num"] =$num;
                    $recommended[$last_num]["num"] =$num;
                }
            }
        }
        $this->assign('time',$time);
        $this->assign('color',$color);
        $this->assign('arr',$arr);
        $this->assign('site',getSettingCache('site'));
        $this->assign('back_img',$back_img);
        $this->assign('recommended',$recommended);
        return $this->fetch();
    }



}
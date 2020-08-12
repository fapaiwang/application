<?php
namespace app\home\controller\user;
use app\common\controller\UserBase;
use app\home\controller\Api;
use app\home\service\SecondServer;
use app\home\service\ToolsServer;
use app\server;
use think\Db;

class Second extends UserBase

{

    private $queryData;



    /**

     * @return mixed

     * 二手房列表

     */

    public function index()

    {

        $where = $this->search();

        $field = "id,title,estate_name,img,room,living_room,price,acreage,status,update_time,top_time,timeout,audit_status";

        $lists = model('second_house')->where($where)->field($field)->order(['top_time'=>'desc','id'=>'desc'])->paginate(15,false,['query'=>$this->queryData]);

        $this->assign('list',$lists);

        $this->assign('pages',$lists->render());

        return $this->fetch();

    }



    /**

     * @return mixed

     * 添加二手房

     */

    public function add()

    {

        return $this->fetch();

    }



    /**

     * @return mixed

     * 编辑二手房

     */

    public function edit()
    {
        $userInfo = $this->userInfo;
        if($userInfo['model']!=4){
            return $this->fetch('public/404');
        }
        $ToolsServer= new ToolsServer();
        $id  = input('param.id/d',0);
        if(!$id)
        {
            $this->error('参数错误');
        }
        $url   = request()->server('HTTP_REFERER');
        $data = model('second_house_data')->where(['house_id'=>$id])->find();
        if(empty($data)){
            model('second_house_data')->insert(array('house_id'=>$id,'update_time'=>time()));
            $data = model('second_house_data')->where(['house_id'=>$id])->find();
        }
        $position_lists = model('position_cate')->where(['model'=>"second_house"])->field('id,title')->select();
        $house_position_cate_id = model('position')->where(['house_id' => $id, 'model' => 'second_house'])->column('cate_id');//获取该楼盘 所属的推荐位id
        $info = model('second_house')->find($id);
        $estate = model('estate')->where(['id'=>$info['estate_id']])->field("years,data")->find();
        if($data['bidding_cycle']==0||$data['bidding_cycle']==''){
            $data['bidding_cycle']=1;
        }
        if($data['decoration']==0||$data['decoration']==''){
            $data['decoration']=2;
        }
        if($info['qianfei']==''){
            $info['qianfei']='无';
        }
        if($info['lease']==''){
            $info['lease']='无';
        }
        if($data['gas_cost']==0||$data['gas_cost']==''){
            $data['gas_cost']='2.63元/立方米';
        }
        if($data['influence_factor']==''){
            $data['influence_factor']='无';
        }
        if($data['parking_information']==''){
            $data['parking_information']=$estate['data']['parking_space'];
        }
        if($info['years']==''){
            $info['years']=$estate['years'];
        }
        if($data['property_fee']==''){
            $data['property_fee']=$estate['data']['property_fee'];
        }
        $jiage  = 0;
        $mianji = $info['acreage'];
        if($info['ckprice']>0){
            $jiage = $info['ckprice'];
        }elseif($info['price']>0){
            $jiage = $info['price'];
        }
        if(empty($data['deed_tax'])&&!empty($info['qipai'])){
            $data['deed_tax'] = $ToolsServer->deen_tax($info['qipai'],$mianji);
        }
        if(empty($data['first_suite'])&&!empty($jiage)){
            $data['first_suite'] = $ToolsServer->deen_tax($jiage,$mianji);
        }
        if(empty($data['second_suite'])&&!empty($jiage)){
            $data['second_suite'] = $ToolsServer->deen_tax($jiage,$mianji,501);
        }
        if(empty($data['lending_rate'])){
            $lending_rate = getSettingCache('tools');
            $data['lending_rate'] = $lending_rate['lilv'];
        }
        if(empty($data['bidding_rules'])){
            $data['bidding_rules'] = '至少一人报名且出价不低于变卖价，方可成交。';
        }
        //查询已编辑次数
        $broker_id = $this->userInfo['id'];
        $where['operator'] = $broker_id;
        $where['house_id'] = $id;
        $where['type'] = 2;
        $edit_number = model('operatio_log')->where($where)->count();
        $info['audit_notes2'] = str_replace("\r\n","<br>",$info['audit_notes']);
        //判断是否为未提交和驳回状态
        //判断是否有草稿
        if(($info['audit_status']==0 || $info['audit_status']==2) && $edit_number<3 ){
            $data_draft = Db::name('second_house_data_draft')->where(['houseid'=>$id])->find();
            if($data_draft){
                $data_draft =json_decode($data_draft['draft'],true);
                $info['toilet'] = $data_draft['toilet'];
                $info['acreage'] = $data_draft['acreage'];
                $info['price'] = $data_draft['price'];
                $info['ckprice'] = $data_draft['ckprice'];
                $info['floor'] = $data_draft['floor'];
                $info['total_floor'] = $data_draft['total_floor'];
                $info['orientations'] = $data_draft['orientations'];
                $info['elevator_status'] = $data_draft['elevator_status'];
                $info['elevator'] = $data_draft['elevator'];
                $info['land_purpose'] = $data_draft['land_purpose'];
                $info['xiaci_status'] = $data_draft['xiaci_status'];
                $info['xiaci'] = $data_draft['xiaci'];
                $info['property_no'] = $data_draft['property_no'];
                $info['qianfei_status'] = $data_draft['qianfei_status'];
                $info['qianfei'] = $data_draft['qianfei'];
                $info['management'] = $data_draft['management'];
                $info['sequestration'] = $data_draft['sequestration'];
                $info['lease'] = $data_draft['lease'];
                $info['mortgage'] = $data_draft['mortgage'];
                $info['vacate'] = $data_draft['vacate'];
                $info['land_certificate'] = $data_draft['land_certificate'];
                $info['years'] = $data_draft['years'];
                $data['enforcement'] = $data_draft['enforcement'];
                $data['traffic'] = $data_draft['traffic'];
                $data['lease_term'] = $data_draft['lease_term'];
                $data['education'] = $data_draft['education'];
                $data['medical_care'] = $data_draft['medical_care'];
                $data['shangchao'] = $data_draft['shangchao'];
                $data['heating_cost'] = $data_draft['heating_cost'];
                $data['kerbside'] = $data_draft['kerbside'];
                $data['shuangqi'] = $data_draft['shuangqi'];
                $data['structure'] = $data_draft['structure'];
                $data['nature'] = $data_draft['nature'];
                $data['building_type'] = $data_draft['building_type'];
                $data['heating_mode'] = $data_draft['heating_mode'];
                $data['electricity_type'] = $data_draft['electricity_type'];
                $data['gas_cost'] = $data_draft['gas_cost'];
                $data['water_type'] = $data_draft['water_type'];
                $data['occlusion'] = $data_draft['occlusion'];
                $data['haunted_house'] = $data_draft['haunted_house'];
                $data['decoration'] = $data_draft['decoration'];
                $data['influence_factor'] = $data_draft['influence_factor'];
                $data['enrollment_quota'] = $data_draft['enrollment_quota'];
                $data['price_increase_range'] = $data_draft['price_increase_range'];
                $data['bidding_cycle'] = $data_draft['bidding_cycle'];
                $data['auction_website'] = $data_draft['auction_website'];
                $data['parking_information'] = $data_draft['parking_information'];
                $data['bidding_rules'] = $data_draft['bidding_rules'];
                $data['court_execution'] = $data_draft['court_execution'];
                $data['reason'] = $data_draft['reason'];
                $data['property_owner'] = $data_draft['property_owner'];
                $data['preemptive_right'] = $data_draft['preemptive_right'];
                $data['waiver'] = $data_draft['waiver'];
                $data['donation'] = $data_draft['donation'];
                $data['grant_share'] = $data_draft['grant_share'];
                $data['co_ownership'] = $data_draft['co_ownership'];
                $data['mortgages'] = $data_draft['mortgages'];
                $data['mortgagee'] = $data_draft['mortgagee'];
                $data['adult_or_not'] = $data_draft['adult_or_not'];
                $data['registration_unit'] = $data_draft['registration_unit'];
                $data['housing_loan'] = $data_draft['housing_loan'];
                $data['lending_rate'] = $data_draft['lending_rate'];
                $data['maximum_loan_life'] = $data_draft['maximum_loan_life'];
                $data['deed_tax'] = $data_draft['deed_tax'];
                $data['first_suite'] = $data_draft['first_suite'];
                $data['second_suite'] = $data_draft['second_suite'];
                $data['payment_period'] = $data_draft['payment_period'];
                $data['account_name'] = $data_draft['account_name'];
                $data['bank_of_deposit'] = $data_draft['bank_of_deposit'];
                $data['account_number'] = $data_draft['account_number'];
                $data['property_fee'] = $data_draft['property_fee'];
                $data['txqzsfyjj'] = $data_draft['txqzsfyjj'];
                $data['thxzsfyjj'] = $data_draft['thxzsfyjj'];
                $data['zsfyzgj'] = $data_draft['zsfyzgj'];
                $data['tddxqfwjj'] = $data_draft['tddxqfwjj'];
                $data['tddpmfwcjjj'] = $data_draft['tddpmfwcjjj'];
                $data['bidding_period'] = $data_draft['bidding_period'];
            }
        }
        $this->assign('edit_number',$edit_number);
        $this->assign('developer',$estate['data']['property_company']);
        $this->assign('back_url',$url);
        $this->assign('info', $info);
        $this->assign('position_lists',$position_lists);
        $this->assign('position_cate_id',$house_position_cate_id);
        $this->assign('data',$data);
        return $this->fetch();
    }
    /**
    *   修改面积和市场价更新契税第一套和第二套契税
     */
    function deen_tax(){
        $acreage  = input('post.acreage');
        $jiage = input('post.jiage');
        $qipai = input('post.qipai');
        $ToolsServer= new ToolsServer();
        $data['deed_tax'] = $ToolsServer->deen_tax($qipai,$acreage);
        $data['first_suite'] = $ToolsServer->deen_tax($jiage,$acreage);
        $data['second_suite'] = $ToolsServer->deen_tax($jiage,$acreage,501);
        return $data;
    }

    /**

     * @return array

     * 搜索条件

     */

    private function search()
    {
        $status  = input('get.status');
        $keyword = input('get.keyword');
        $audit_status = input('get.audit_status');
        $where   = [];
        $where['broker_id'] = $this->userInfo['id'];
        is_numeric($status) && $where['status'] = $status;
        $keyword && $where[] = ['title','like','%'.$keyword.'%'];
        is_numeric($audit_status) && $where['audit_status'] = $audit_status;
        $data = [
            'status' => $status,
            'keyword'=> $keyword,
            'audit_status'=> $audit_status
        ];
        $this->queryData = $data;
        $this->assign('search',$data);
        return $where;
    }
    /**
     *  尽调报告
     */
    function report(){
        $userInfo = $this->userInfo;
        if($userInfo['model']!=4){
            return $this->fetch('public/404');
        }
        $id = input('param.id/d',0);
        if($id){
            $data = model('second_house_data')->where(['house_id'=>$id])->find();
            if(empty($data)){
                return $this->fetch('public/404');
            }
            $info = model('second_house')->find($id);
            if($info['audit_status']==0 or $info['audit_status']==2){
                return $this->fetch('public/404');
            }
            $estate = model('estate')->where(['id'=>$info['estate_id']])->field("years,data")->find();
            $info['orientations'] = model('linkmenu')->where(['id'=>$info['orientations']])->value("name");
            $info['toilet'] = model('linkmenu')->where(['id'=>$info['toilet']])->value("name");
            $info['types'] = model('linkmenu')->where(['id'=>$info['types']])->value("name");
            if (!empty($info['qipai']) && $info['acreage']){
                $info['junjia']=sprintf("%.2f",intval($info['qipai'])/intval($info['acreage'])*10000);
            }
            $this->assign('info',$info);
            $this->assign('data',$data);
            $this->assign('estate',$estate);
            $system_name = 'DESKTOP-R6JHGA8\239801';
            $system_name = MD5($system_name);
            $this->assign('system_name',$system_name);
            return $this->fetch();
        }else{
            return $this->fetch('public/404');
        }
    }
    function draft_data(){
        $draft  = input('post.from_data');
        $data = array();
        foreach($draft as $k=>$v){
            $data[$v['name']] = $v['value'];
        }
        $houseid = $data['id'];
        unset($data['back_url']);unset($data['qipai']);unset($data['developer']);unset($data['id']);
        $data = json_encode($data);
        $is_have = Db::name('second_house_data_draft')->where(['houseid'=>$houseid])->find();
        if($is_have){
            $code = Db::name('second_house_data_draft')->where(['houseid'=>$houseid])->update(array('houseid'=>$houseid,'draft'=>$data,'update_time'=>time()));
        }else{
            $code = Db::name('second_house_data_draft')->insert(array('houseid'=>$houseid,'draft'=>$data,'update_time'=>time()));
        }
        if($code){
            return 1;
        }else{
            return 2;
        }
    }

}
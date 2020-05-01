<?php




namespace app\mobile\controller\user;

class Wddz extends UserBase

{

    public function initialize()

    {

        parent::initialize();

        $this->assign('title','我的定制');

    }

    /**

     * @return mixed

     * 新房列表

     */

   
    public function index()

    {
        
        $info = cookie('userInfo');
        $info = \org\Crypt::decrypt($info);
        $user_id[]=['user_id','eq',$info['id']];
        $yjdzs = db('yjdz')->where($user_id)->order('zonge','desc')->find();
        
        if(!empty($yjdzs)){
            $zonges=$yjdzs['zonge'];


$jiage[]=['qipai','elt',$zonges];
// $jiage[]=['fcstatus','egt',172];
// $jiage[]=['fcstatus','elt',174];
$jiage[]=['fcstatus','eq',170];
$ye = $_GET['param'];
        $this->assign('ye',$ye);
if($ye==0){
    $lists = db('second_house')->where($jiage)->order('fabutimes','desc')->limit(20)->select();
}else{

    $start=$ye*20+1;
    $end=($ye+1)*20;
    $lists = db('second_house')->where($jiage)->order('qipai','desc')->limit($start,$end)->select();
}
// $lists = db('second_house')->where($jiage)->order('qipai','desc')->limit(20)->select();
        
        $shuliang = model('second_house')->where($jiage)->order('qipai','desc')->count();
        model('yjdz')->where(['id'=>$yjdzs['id']])->update(['shuliang'=>$shuliang]);

    }else{
        $lists=array();
    }
        







        // $field = "distinct(h.id),h.title,h.estate_name,h.img,h.city,h.room,h.living_room,h.toilet,h.price,h.average_price,h.tags,h.address,h.acreage,h.orientations,h.renovation";

        // $lists = $this->getLists('second_house',$field);
// print_r($lists);
        $this->assign('lists',$lists);


        return $this->fetch();

    }

  public function seconddata()

    {
    
        //$id = input('param.id/d',0);
        $start = Input('post.start');
        //echo($start);
        // $nameids=Input('post.nameids');
        // $where['broker_id'] = $nameids;

        $where['status']    = 1;


        $info = cookie('userInfo');
        $info = \org\Crypt::decrypt($info);
        $user_id[]=['user_id','eq',$info['id']];
        $yjdzs = db('yjdz')->where($user_id)->order('zonge','desc')->find();
        
        if(!empty($yjdzs)){
            $zonges=$yjdzs['zonge'];


$jiage[]=['qipai','elt',$zonges];
$jiage[]=['fcstatus','elt',170];


        $lists = db('second_house')->where($jiage)->order('qipai','desc')->limit($start, 20)->select();

    }else{
        $lists=array();
    }













        // $secondObj = model('second_house');
        // $field = 'id,city,title,estate_id,estate_name,fcstatus,img,room,kptime,living_room,toilet,acreage,price,average_price,address,orientations,renovation,tags,marketprice,fabutime';
        // $second_lists = $secondObj->where($where)->field($field)->order(['fcstatus' =>'asc','fabutime' =>'desc'])->limit($start, 20)->select();
        
        // foreach ($second_lists as $key => $value) {
        //         $estate_id=$second_lists[$key]['estate_id'];

        //        $sql=model('estate')->where('id','eq',$estate_id)->alias('years')->find();
        //        $years=$sql['years'];
        //        $second_lists[$key]['years']=$years;



                

        //    }
       
        
        return (array( 'lists'=>$lists,'tags'=>getLinkMenuCache(14), 'msg'=>'获取成功！'));
        
      

    }





 

}
<?php




namespace app\mobile\controller\user;

class Rgtj extends UserBase

{

    public function initialize()

    {

        parent::initialize();

        $this->assign('title','人工推荐');

    }

    /**

     * @return mixed

     * 新房列表

     */

   
    public function index()

    {
    //     $info = cookie('userInfo');
    //     $info = \org\Crypt::decrypt($info);
    //     $user_id[]=['user_id','eq',$info['id']];
    //     $yjdzs = db('yjdz')->where($user_id)->order('zonge','desc')->find();
        
    //     if(!empty($yjdzs)){
    //         $zonges=$yjdzs['zonge'];



    //     $shuliang = model('second_house')->where($jiage)->order('qipai','desc')->count();
    //     model('yjdz')->where(['id'=>$yjdzs['id']])->update(['shuliang'=>$shuliang]);

    // }else{
    //     $lists=array();
    // }
        $jiage[]=['tuisong','eq',1];


        $lists = db('second_house')->where($jiage)->order('qipai','desc')->select();







        // $field = "distinct(h.id),h.title,h.estate_name,h.img,h.city,h.room,h.living_room,h.toilet,h.price,h.average_price,h.tags,h.address,h.acreage,h.orientations,h.renovation";

        // $lists = $this->getLists('second_house',$field);
// print_r($lists);
        $this->assign('lists',$lists);


        return $this->fetch();

    }

  





 

}
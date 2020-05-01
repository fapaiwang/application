<?php




namespace app\mobile\controller\user;

class Tjfy extends UserBase

{

    public function initialize()

    {

        parent::initialize();

        $this->assign('title','推荐房源');

    }

    /**

     * @return mixed

     * 新房列表

     */

   
    public function index()

    {
        $info = cookie('userInfo');
        $info = \org\Crypt::decrypt($info);
        $jiage[]=['rec_position','eq',1];
        $tjfy = db('second_house')->where($jiage)->count();

        model('user')->where(['id'=>$info['id']])->update(['tjfy'=>$tjfy]);
        



    $this->assign('time',time());
       

        return $this->fetch();

    }

  





 

}
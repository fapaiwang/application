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
        
    $this->assign('time',time());
       

        return $this->fetch();

    }

  





 

}
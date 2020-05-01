<?php



namespace app\mobile\controller\user;

class Jlfy extends UserBase

{

    public function initialize()

    {

        parent::initialize();

        $this->assign('title','捡漏房源');

    }

    /**

     * @return mixed

     * 新房列表

     */

   
    public function index()

    {
        $lists = model('second_house')->where('status','eq',1)->order(['marketprice'=>'desc','fcstatus'=>'asc'])->select();
    

$this->assign('time',time());
$this->assign('lists',$lists);
     
        return $this->fetch();

    }

  





 

}
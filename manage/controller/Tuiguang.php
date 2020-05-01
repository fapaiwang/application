<?php




namespace app\manage\controller;





use app\common\controller\ManageBase;



class Tuiguang extends ManageBase

{

    /**

     * @return mixed

     * 预约列表

     */

    public function index()

    {

     
        $lists = model('tijiao')->select();

        $this->assign('list',$lists);

 

        return $this->fetch();

    }






}
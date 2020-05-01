<?php


namespace app\home\controller\user;

use app\common\controller\UserBase;

class Weituopm extends UserBase

{

    /**

     * @return mixed

     * 新房列表

     */

    public function index()

    {

        
$info = cookie('userInfo');
        $info = \org\Crypt::decrypt($info);
        // print_r($info);
        $user_id[]=['user_id','eq',$info['id']];
//  $user_id[]= $this->userInfo['id'];
// print_r($info['id']);
$lists = db('wtpm')->where($user_id)->select();
        
// print_r($lists);
        $this->assign('lists',$lists);

        

        return $this->fetch();

    }





 

}
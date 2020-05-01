<?php





namespace app\mobile\controller\user;

class Gzxq extends UserBase

{

    public function initialize()

    {

        parent::initialize();

        $this->assign('title','关注小区');

    }

    /**

     * @return mixed

     * 新房列表

     */

   
    public function index()

    {
           $info = cookie('userInfo');
        $info = \org\Crypt::decrypt($info);

        $fields="distinct(house_id),id,user_id,model,create_time,shuliang";
        $user_idss[]=['user_id','eq',$info['id']];
        $user_idss[]=['model','eq','estate'];
        $gzfy = db('follow')->field($fields)->where($user_idss)->order('id','asc')->select();
       // print_r($gzfy);
        $gzxqss=array();
        $lists=array();
        foreach ($gzfy as $key => $value) {
            // print_r($value['house_id']);
            // $user_ids[]=['estate_id','eq',$value['house_id']];
            // print_r($user_ids[]);
            $lists[] = db('second_house')->where(['estate_id'=>$value['house_id']])->select();
            $gzxqss[]= db('second_house')->where(['estate_id'=>$value['house_id']])->count();
           
            
        }
$aaa=0;
if(!empty($gzxqss)){
foreach ($gzxqss as $keys => $values) {
    $aaa+=$values;
}}

        model('user')->where(['id'=>$info['id']])->update(['gzxq'=>$aaa]);
        // print_r($gzxqss);
//print_r($lists);
// $lists=array();
$this->assign('lists',$lists);
            



       






        return $this->fetch();



    }


}
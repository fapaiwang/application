<?php





namespace app\manage\controller;





use app\common\controller\ManageBase;



class Wtpm extends ManageBase

{

    /**

     * @return mixed

     * 预约列表

     */

    public function index()

    {

        // $arr   = ['house','second_house','rental','office','office_rental','shops','shops_rental'];

        // $where = $this->search();

        // $type  = input('param.type','house');

        
$where['type']    = 1;
        // $field = 's.*,u.nick_name,h.title';
// $obj=model('wtpm');
        $lists = model('wtpm')->where($where)->select();
// print_r($lists);
        // $this->_exclude = 'edit';
 //        foreach ($lists as $key => $value) {
 //            print_r($value);
 //        }
 // $list = model('wtpm')->where($where)->find();
        $this->assign('list',$lists);

        // $this->assign('pages',$lists->render());

        // $this->assign('options',$this->check());

        // $this->assign('type',$type);

        return $this->fetch();

    }



    /**

     * @return array

     * 搜索条件

     */

    // public function search(){

    //     $map = [];

    //     $type = input('get.type');

    //     is_numeric($type) && $map['s.type'] = $type;

    //     $data['type'] = $type;

    //     $this->queryData = $data;

    //     $this->assign('search', $data);

    //     return $map;

    // }




}
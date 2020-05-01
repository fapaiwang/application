<?php
namespace app\common\model;

//use traits\model\SoftDelete;
class Menu extends \think\Model
{
    //use SoftDelete;  //软删除
   // protected static $deleteTime = 'delete_time';
    public function adminMenu($pid, $with_self=false) {
        $pid = intval($pid);
        $menus = \org\Role::getSubMenu($pid);
        return $menus;
    }

    public function subMenu($pid = '', $big_menu = false) {
        $array = $this->adminMenu($pid, false);
        $numbers = count($array);
        if ($numbers==0 && !$big_menu) {
            return '';
        }
        return $array;
    }

    public function get_level($id,$array=[],$i=0) {
        foreach($array as $n=>$value){
            if ($value['id'] == $id) {
                if($value['pid']== '0') return $i;
                $i++;
                return $this->get_level($value['pid'],$array,$i);
            }
        }
    }
    public function doCache()
    {
        $adminInfo = \org\Crypt::decrypt(session('adminInfo'));
        $role_id = $adminInfo['role'];
        $lists = $this->order('ordid')->select();
        cache('managemenu'.$role_id,objToArray($lists));
    }
}
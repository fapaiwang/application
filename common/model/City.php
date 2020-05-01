<?php
namespace app\common\model;
class City extends \think\Model{
   
    public function get_spid($id) {
        if (!$id) {
            return 0; 
        }
        $pspid = $this->where(['id'=>$id])->value('spid');
        if ($pspid) {
            $spid = $pspid . $id.'|';
        } else {
            $spid = $id.'|';
        }
        return $spid;
    }
    
    /**
     * 获取分类下面的所有子分类的ID集合
     * 
     * @param int $id
     * @param bool $with_self
     * @return array $array 
     */
    public function get_child_ids($id, $with_self=false) {
        $spid = $this->where(['id'=>$id])->value('spid');
        $spid = $spid ? $spid .= $id .'|' : $id .'|';
        $array = $this->where([['spid','like', $spid.'%']])->column('id');
        $with_self && $array[] = $id;
        return $array;
    }

    /**
     * @param $id
     * @param bool|false $child
     * @return array
     * 获取指定分类的顶级分类下的所有分类或顶级分类id
     */
    public function getTopParentChild($id,$child = false)
    {
        $spid    = $this->where(['id'=>$id])->value('spid');
        $id_arr  = explode('|',$spid);
        $top_pid = $spid == 0 ? $id : $id_arr[0];//顶级分类id
        if($child)
        {
            $spid    = $top_pid .'|';
            $array   = $this->where([['spid','like', $spid.'%']])->column('id');
            $array[] = $top_pid;
            return $array;
        }else{
            return $top_pid;
        }
    }
    /**
     * 检测分类是否存在
     * 
     * @param string $name
     * @param int $pid
     * @param int $id
     * @return bool 
     */
    public function name_exists($name, $pid, $id=0) {
        $where['name'] = $name;
        if($pid){
            $where['pid']  = $pid;
        }
        if($id>0){
            $where[] = ['id','neq',$id];
        }
        $result = $this->where($where)->count('id');
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $area_id
     * @return mixed
     * 获取省份id
     */
    public function getProvinceId($area_id)
    {
        $where['id'] = $area_id;
        $province_id = $this->where($where)->value('province_id');
        return $province_id;
    }
}
<?php


namespace app\common\model;


class Estate extends \think\Model
{
    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;
    protected $type = [
        'data' => 'json',
        'file' => 'json'
    ];
    protected function setTagsAttr($value)
    {
        $value = str_replace('，',',',$value);
        return $value;
    }
    protected function getPriceAttr($value)
    {
        if(!$value){
            return '待定';
        }
        return $value;
    }
    /**
     * @param $title
     * @param int $id
     * @return int|string
     * 验证小区是否存在
     */
    public function checkTitleExists($title,$id = 0)
    {
        $where['title'] = $title;
        $id && $where[] = ['id','neq',$id];
        $count = $this->where($where)->count();
        return $count;
    }
    public function getEstateInfo($where,$field='id,title')
    {
        $info = $this->where($where)->field($field)->find();
        return $info;
    }
}
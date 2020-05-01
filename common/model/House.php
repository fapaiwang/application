<?php


namespace app\common\model;


class House extends \think\Model
{
    protected $autoWriteTimestamp = true;
    protected $type = [
        'sale_phone' => 'json'
    ];
    protected function getPriceAttr($value)
    {
        if(!$value){
            return '待定';
        }
        return $value;
    }
    protected function getUnitAttr($value)
    {
        if($this->getData('price') > 0)
        {
            $data = getUnitData();
            if(isset($data[$value]))
            {
                return $data[$value];
            }
            return '元/㎡';
        }else{
            return '';
        }
    }
    public function getHouseInfo($where = [],$field = 'id,title,city,price,unit')
    {
        $info = $this->where($where)->field($field)->find();
        return $info;
    }
    public function getUnitData()
    {
        return getUnitData();
    }

    /**
     * @param $title
     * @param int $id
     * @return int|string
     * 验证楼盘是否存在
     */
    public function checkTitleExists($title,$id = 0)
    {
        $where['title'] = $title;
        $id && $where[] = ['id','neq',$id];
        $count = $this->where($where)->count();
        return $count;
    }

}
<?php


namespace app\common\model;


class HouseType extends \think\Model
{
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    protected function getPriceAttr($value)
    {
        if($value == 0)
        {
            return '待定';
        }
        return $value;
    }
    /**
     * @param $house_id
     * 获取该楼盘下的最大最小户型、价格、面积
     */
    public function getHouseTypeMinMaxValue($house_id)
    {
        $field = "house_id,min(room) as min_type,max(room) as max_type,min(price) as min_price,max(price) as max_price,";
        $field .= "min(acreage) as min_acreage,max(acreage) as max_acreage";
        $info = model('house_type')->where(['house_id'=>$house_id])->field($field)->find();
        if($info)
        {
                $obj = db('house_search');
                $data = $info->toArray();
                $info = $obj->where(['house_id'=>$data['house_id']])->find();
                if($info)
                {
                    $obj->removeOption()->where(['house_id'=>$data['house_id']])->update($data);
                }else{
                    $obj->insert($data);
                }
        }

    }
}
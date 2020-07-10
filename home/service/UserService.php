<?php

namespace app\home\service;
class UserService
{
    /**
     * 获取用户信息
     * @param mixed
     * @return mixed|string
     * @author: al
     */
    public function getUserInfo()
    {
        $info = cookie('userInfo');
        $info = \org\Crypt::decrypt($info);
        return $info;
    }
    public function followHouse(){
        $field = "distinct(h.id),h.title,h.estate_name,h.img,h.room,h.living_room,h.toilet,h.price,h.average_price,h.tags,h.address,h.acreage,h.orientations,h.renovation";
        $lists = $this->getLists('second_house',$field);
        return $lists;
    }
    private function getLists($model,$field){
        $obj = model('follow');
        $where['f.user_id'] = login_user()["id"];
        $where['f.model']   = $model;
        $join = [[$model.' h','f.house_id=h.id']];
        $field .= ',f.create_time';
        if($model == 'house') {
            $join = [
                [$model.' h','f.house_id=h.id'],
                ['house_search s','h.id = s.house_id','left']
            ];
        }
        $lists = $obj->alias('f')
            ->where($where)
            ->join($join)
            ->field($field)
            ->order('f.create_time','desc')
            ->paginate(10);
        return $lists;
    }
}
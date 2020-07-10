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

    /**
     * 关注的房源
     * @param mixed
     * @return \think\Paginator
     * @author: al
     */
    public function followHouse(){
        $field = "distinct(h.id),h.title,h.estate_name,h.img,h.room,h.living_room,h.toilet,h.price,h.average_price,h.tags,h.address,h.acreage,h.orientations,h.renovation";
        $lists = $this->getFollowLists('second_house',$field);
        return $lists;
    }
    //关注
    private function getFollowLists($model,$field){
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

    /**
     * 我的预约
     * @param mixed
     * @return mixed
     * @author: al
     */
    public function subscribeHouse()
    {
        $field = "h.id,h.title,h.estate_name,h.img,h.room,h.living_room,h.toilet,h.price,h.average_price,h.city,h.address,h.acreage,h.orientations,h.renovation";
        $lists = $this->getSubscribeLists('second_house',$field);
        return $lists;
    }

    private function getSubscribeLists($model,$field){
        $obj = model('subscribe');
        $where['f.user_id'] =login_user()["id"];
        $where['f.model']   = $model;
        //$where[] = ['f.house_id','gt',0];
        $join = [[$model.' h','f.house_id=h.id']];
        $field .= ',f.create_time,f.type,f.house_id,f.user_name,f.mobile,f.house_name';
        $lists = $obj->alias('f')
            ->where($where)
            ->join($join)
            ->field($field)
            ->order('f.create_time','desc')
            ->group('f.house_id')
            ->paginate(10);
        return $lists;
    }

}
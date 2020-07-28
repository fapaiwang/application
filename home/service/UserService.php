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
    public function getFollowHouse($user_id){
        $field = "distinct(h.id),h.title,h.estate_name,h.img,h.room,h.living_room,h.toilet,h.price,h.average_price,h.tags,h.address,h.acreage,h.orientations,h.renovation";
        $lists = $this->getFollowLists('second_house',$field,$user_id);
        return $lists;
    }

    /**
     * 关注的小区
     * @param mixed
     * @return \think\Paginator
     * @author: al
     */
    public function followEstate($user_id){
        $field = 'distinct(h.id),h.title,h.img,h.house_type,h.years,h.address,h.price,h.complate_num';
        //统计二手房和出租房数量 where条件里需要替换estate_id为estate表每条记录的id, 不能用字符(会被转成 0)所以用9999代替替换
        $second_sql = model('second_house')->where('estate_id','9999')->where('status',1)->field('count(id) as second_total')->buildSql();
        $rental_sql = model('rental')->where('estate_id','9999')->where('status',1)->field('count(id) as second_total')->buildSql();
        $field .= ','.$second_sql.' as second_total,'.$rental_sql.' as rental_total';
        $field  = str_replace('9999','h.id',$field);
        $lists = $this->getFollowLists('estate',$field,$user_id);
        return $lists;
    }
    //关注
    private function getFollowLists($model,$field,$user_id){
        $obj = model('follow');
        $where['f.user_id'] = $user_id;
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
    public function subscribeHouse($user_id)
    {
        $field = "h.id,h.title,h.estate_name,h.img,h.room,h.living_room,h.toilet,h.price,h.average_price,h.city,h.address,h.acreage,h.orientations,h.renovation";
        $lists = $this->getSubscribeLists('second_house',$field,$user_id);
        return $lists;
    }

    private function getSubscribeLists($model,$field,$user_id){
        $obj = model('subscribe');
        $where['f.user_id'] = $user_id;
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
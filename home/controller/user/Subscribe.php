<?php

namespace app\home\controller\user;
use app\common\controller\UserBase;
class Subscribe extends UserBase
{
    /**
     * @return mixed
     * 新房列表
     */
    public function index()
    {
        $field = 'h.id,h.title,h.img,h.sale_status,h.city,h.address,h.price,h.unit';
        $lists = $this->getLists('house',$field);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }

    /**
     * @return mixed
     * 二手房列表
     */
    public function second()
    {
        $field = "h.id,h.title,h.estate_name,h.img,h.room,h.living_room,h.toilet,h.price,h.average_price,h.city,h.address,h.acreage,h.orientations,h.renovation";
        $lists = $this->getLists('second_house',$field);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }

    /**
     * @return mixed
     * 出租房列表
     */
    public function rental()
    {
        $field = "h.id,h.title,h.estate_name,h.img,h.room,h.living_room,h.toilet,h.price,h.rent_type,h.city,h.address,h.acreage,h.orientations,h.renovation";
        $lists = $this->getLists('rental',$field);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }
    /**
     * @return mixed
     * 写字楼出售列表
     */
    public function office()
    {
        $field = "h.id,h.title,h.estate_name,h.img,h.city,h.address,h.acreage,h.price";
        $lists = $this->getLists('office',$field);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }
    /**
     * @return mixed
     * 写字楼出租列表
     */
    public function officeRental()
    {
        $field = "h.id,h.title,h.estate_name,h.img,h.city,h.address,h.acreage,h.price";
        $lists = $this->getLists('office_rental',$field);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }
    /**
     * @return mixed
     * 商铺出售列表
     */
    public function shops()
    {
        $field = "h.id,h.title,h.estate_name,h.img,h.city,h.address,h.acreage,h.price";
        $lists = $this->getLists('shops',$field);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }
    /**
     * @return mixed
     * 商铺出售列表
     */
    public function shopsRental()
    {
        $field = "h.id,h.title,h.estate_name,h.img,h.city,h.address,h.acreage,h.price";
        $lists = $this->getLists('shops_rental',$field);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }
    /**
     * @return \think\response\Json
     * 删除
     */
    public function delete()
    {
        $house_id = input('get.house_id/d',0);
        $model    = input('get.model');
        $return['code'] = 0;
        if($house_id)
        {
            $where['house_id'] = $house_id;
            $where['model']    = $model;
            if(model('subscribe')->where($where)->delete())
            {
                $return['code'] = 1;
                $return['msg']  = '删除成功';
            }else{
                $return['msg']  = '删除失败';
            }
        }else{
            $return['msg']      = '参数错误';
        }
        return json($return);
    }
    /**
     * @param $model
     * @param $field
     * @return \think\Paginator
     * 列表
     */
    private function getLists($model,$field)
    {
        $obj = model('subscribe');
        $where['f.user_id'] = $this->userInfo['id'];
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
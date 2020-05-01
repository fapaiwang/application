<?php

namespace app\home\controller\user;
use app\common\controller\UserBase;
class SubscribeManage extends UserBase
{
    public function initialize()
    {
        parent::initialize();
        if($this->userInfo['model'] == 1)
        {
            $this->error('无权限操作');
        }
    }
    /**
     * @return mixed
     * 新房列表
     */
    public function index()
    {
        $field = 'h.id,h.title,h.price,h.unit';
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
        $field = "h.id,h.title,h.price,h.average_price";
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
        $field = "h.id,h.title,h.price";
        $lists = $this->getLists('rental',$field);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
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
        $where['f.broker_id'] = $this->userInfo['id'];
        $where['f.model']   = $model;
        //$where[] = ['f.house_id','gt',0];
        $join = [[$model.' h','f.house_id=h.id']];
        $field .= ',f.id as sid,f.type,f.create_time,f.house_id,f.user_name,f.mobile,f.status,f.house_name';
        $lists = $obj->alias('f')
            ->where($where)
            ->join($join)
            ->field($field)
            ->order('f.create_time','desc')
            ->paginate(10);
        return $lists;
    }
}
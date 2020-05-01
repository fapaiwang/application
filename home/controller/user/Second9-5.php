<?php


namespace app\home\controller\user;


use app\common\controller\UserBase;

class Second extends UserBase
{
    private $queryData;

    /**
     * @return mixed
     * 二手房列表
     */
    public function index()
    {
        $where = $this->search();
        $field = "id,title,estate_name,img,room,living_room,price,acreage,status,update_time,top_time,timeout";
        $lists = model('second_house')->where($where)->field($field)->order(['top_time'=>'desc','id'=>'desc'])->paginate(15,false,['query'=>$this->queryData]);
        $this->assign('list',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }

    /**
     * @return mixed
     * 添加二手房
     */
    public function add()
    {
        return $this->fetch();
    }

    /**
     * @return mixed
     * 编辑二手房
     */
    public function edit()
    {
        $id    = input('param.id/d',0);
        $url   = request()->server('HTTP_REFERER');
        if(!$id){
            $this->error('参数错误');
        }else{
            $where['id']        = $id;
            $where['broker_id'] = $this->userInfo['id'];
            $info = model('second_house')->where($where)->find();
            $this->assign('back_url',$url);
            $this->assign('info',$info);
        }
        return $this->fetch();
    }
    /**
     * @return array
     * 搜索条件
     */
    private function search()
    {
        $status  = input('get.status');
        $keyword = input('get.keyword');
        $where   = [];
        $where['broker_id'] = $this->userInfo['id'];
        is_numeric($status) && $where['status'] = $status;
        $keyword && $where[] = ['title','like','%'.$keyword.'%'];
        $data = [
            'status' => $status,
            'keyword'=> $keyword
        ];
        $this->queryData = $data;
        $this->assign('search',$data);
        return $where;
    }
}
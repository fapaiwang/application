<?php


namespace app\manage\controller;


use app\common\controller\ManageBase;

class Shops extends ManageBase
{
    private $model = 'shops';
    protected $beforeActionList = [
        'beforeEdit' => ['only'=>['edit']],
        'beforeAdd' => ['only'=>['add']],
        'beforeIndex' => ['only'=>['index']]
    ];
    protected function beforeIndex()
    {
        $this->sort = ['ordid'=>'asc','id'=>'desc'];
    }
    /**
     * @return array
     * 搜索条件
     */
    public function search()
    {
        $status  = input('get.status');
        $keyword = input('get.keyword');
        $where   = [];
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
    public function beforeAdd()
    {
        $position_lists = \app\manage\service\Position::lists($this->model);
        $this->assign('position_lists',$position_lists);
    }
    public function beforeEdit()
    {
        $id  = input('param.id/d',0);
        if(!$id)
        {
            $this->error('参数错误');
        }
        $data = model($this->model.'_data')->where(['house_id'=>$id])->find();
        $position_lists = \app\manage\service\Position::lists($this->model);
        $house_position_cate_id = \app\manage\service\Position::getPositionIdByHouseId($id,$this->model);
        $this->assign('position_lists',$position_lists);
        $this->assign('position_cate_id',$house_position_cate_id);
        $this->assign('data',$data);
    }
    /**
     * @return mixed
     * 保存
     */
    public function save()
    {
        $data = input('post.');
        $result = $this->validate($data,'Shops');//调用验证器验证
        $return['code']   = 0;
        $return['msg']    = '';
        $obj = model($this->model);
        $return['url'] = null;
        isset($data['refer']) && $return['url'] = $data['refer'];
        if(true !== $result)
        {
            // 验证失败 输出错误信息
            $this->error($result);
        }else{
            \think\Db::startTrans();
            try{
                !empty($data['map']) && $location = explode(',',$data['map']);
                $data['lng']     = isset($location[0]) ? $location[0] : 0;
                $data['lat']     = isset($location[1]) ? $location[1] : 0;
                $data['tags'] = isset($data['tags']) ? implode(',',$data['tags']) : 0;
                $data['industry'] = isset($data['industry']) ? implode(',',$data['industry']) : 0;
                $data['average_price'] = 0;
                if($data['price']>0 && $data['acreage']>0)
                {
                    //计算均价
                    $data['average_price'] = ceil($data['price'] * 10000 / $data['acreage']);
                }
                if (!isset($data['position'])) {
                    $obj->rec_position = 0;
                } else {
                    $obj->rec_position = 1;
                }
                $data['file'] = $this->getPic();
                (empty($data['img']) && !empty($data['file'])) && $data['img'] = $data['file'][0]['url'];
                if(isset($data['id']))
                {
                    if($obj->allowField(true)->save($data,['id'=>$data['id']]))
                    {
                        $this->optionHouseData($data['id'],$data,true);
                        \app\manage\service\Position::option($data['id'],$this->model);
                        $return['msg']  = '编辑房源信息成功';
                        $return['code'] = 1;
                    }else{
                        $return['msg'] = '编辑房源信息失败！';
                    }
                }else{
                    if($obj->allowField(true)->save($data))
                    {
                        $house_id = $obj->id;
                        $this->optionHouseData($house_id,$data);
                        \app\manage\service\Position::option($house_id,$this->model);
                        $return['msg'] = '添加房源信息成功';
                        $return['code'] = 1;
                    }else{
                        $return['msg'] = '添加房源信息失败！';
                    }
                }
                \think\Db::commit();
            }catch(\Exception $e){
                \think\facade\Log::write('出错信息：'.$e->getFile().$e->getLine().$e->getMessage());
                \think\Db::rollback();
                $return['msg'] = $e->getMessage();
            }
        }
        if($return['code'] == 1)
        {
            $this->success($return['msg'],$return['url']);
        }else{
            $this->error($return['msg'],$return['url']);
        }
    }
    public function delete()
    {
        \app\common\model\Shops::event('after_delete',function($obj){
            //删除扩展数据
            $mod = model($this->model.'_data');
            $where = ['house_id'=>$obj->id];
            $info= $mod->where($where)->find();
            if($mod->where($where)->delete())
            {
                model('attachment')->deleteAttachment($info['info'],$obj->img,$info['file']);
            }
           //删除推荐数据
            action('manage/Position/deleteByHouseId', ['house_id'=>$obj->id,'model'=>$this->model], 'controller');
        });
        parent::delete();
    }
    /**
     * @param $house_id
     * @param $data
     * 添加扩展数据
     */
    private function optionHouseData($house_id,$data,$update = false)
    {
        if (empty($data['seo']['seo_title'])) {
            $info['seo_title'] = $data['title'];
        }else{
            $info['seo_title'] = $data['seo']['seo_title'];
        }
        if(empty($data['seo']['seo_keys']))
        {
            $info['seo_keys'] = $data['estate_name'].'商铺出售';
        }else{
            $info['seo_keys'] = $data['seo']['seo_keys'];
        }
        $info['house_id']  = $house_id;
        $info['info']      = isset($data['info']) ? $data['info'] : '';
        $info['seo_desc']  = $data['seo']['seo_desc'];
        $info['file']      = $data['file'];//$this->getPic();
        $info['mating']    = isset($data['mating'])?implode(',',$data['mating']):'';
        if($update)
        {
            model($this->model.'_data')->allowField(true)->save($info,['house_id'=>$house_id]);
        }else{
            model($this->model.'_data')->allowField(true)->save($info);
        }

    }
    /**
     * @param $obj
     * 添加图片
     */
    private function getPic(){
        $insert = [];
        if(isset($_POST['pic']) && !empty($_POST['pic'])) {
            $images = $_POST['pic'];
            foreach ($images as $key => $v) {
                $insert[] = [
                    'url' => $v['pic'],
                    'title' => $v['alt'],
                ];
            }
        }
        return $insert;
    }
}
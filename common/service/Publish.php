<?php

namespace app\common\service;


class Publish
{
    public static function save($model,$data,$user_id)
    {
        $return['code']   = 0;
        $return['msg']    = '';
        $obj = model($model);
        $return['url'] = null;
        isset($data['back_url']) && $return['url'] = $data['back_url'];
        $status = getSettingCache('user','check_'.$model);
        \think\Db::startTrans();
        try{
            !empty($data['map']) && $location = explode(',',$data['map']);
            !isset($data['lng']) && $data['lng']     = isset($location[0]) ? $location[0] : 0;
            !isset($data['lat']) && $data['lat']     = isset($location[1]) ? $location[1] : 0;
            $data['tags'] = isset($data['tags']) ? implode(',',$data['tags']) : 0;
            $data['status']    = is_numeric($status)?$status:1;
            //$data['average_price'] = 0;
            $img          = self::uploadImg($user_id);
            $data['img']  = $img;
            !isset($data['file']) && $data['file'] = self::getPic();
            (empty($data['img']) && !empty($data['file'])) && $data['img'] = $data['file'][0]['url'];
            if(isset($data['id']))
            {
                if($obj->allowField(true)->save($data,['id'=>$data['id']]))
                {
                    self::optionHouseData($data['id'],$data,$model,true);
                    //关联学校
                    \org\Relation::addSchool($model,$data['lng'],$data['lat'],$data['id'],$data['city']);
                    //关联地铁站
                    \org\Relation::addMetro($model,$data['lng'],$data['lat'],$data['id'],$data['city']);

                    $return['msg'] = '编辑房源信息成功';
                    $return['code'] = 1;
                }else{
                    $return['msg'] = '编辑房源信息失败！';
                }
            }else{
                if($obj->allowField(true)->save($data))
                {
                    $house_id = $obj->id;
                    self::optionHouseData($house_id,$data,$model);
                    //关联学校
                    \org\Relation::addSchool($model,$data['lng'],$data['lat'],$house_id,$data['city']);
                    //关联地铁站
                    \org\Relation::addMetro($model,$data['lng'],$data['lat'],$house_id,$data['city']);

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
        return $return;
    }

    /**
     * @param $user_id
     * @param string $model
     * @return mixed
     * 删除房源
     */
    public static function delete($user_id,$model = 'office')
    {
        $id    = input('param.id',0);
        $return['code'] = 0;
        if(!$id)
        {
            $return['msg'] = '参数错误';
        }else{
            $where[]        = ['id','in',$id];
            $where[] = ['broker_id','eq',$user_id];
            $obj  = model($model);
            $lists = $obj->field('id,img')->where($where)->select();
            if($obj->where($where)->delete())
            {
                self::deleteExtra($lists,$model);
                $return['code'] = 1;
                $return['msg'] = '删除成功';
            }else{
                $return['msg'] = '删除失败';
            }
        }
        return $return;
    }
    /**
     * 异步删除图片
     */
    public static function deleteImg($model){
        $path = input('post.path');
        $id   = input('post.id/d',0);
        $field = input('post.field');
        $return['code'] = 0;
        if($path){
            model('attachment')->deleteAttachment('',$path);
            if($id && $field){
                model($model)->save([$field=>''],['id'=>$id]);
            }
            $return['code'] = 1;
        }else{
            $return['msg'] = '参数错误';
        }
        return $return;
    }
    /**
     * @param $info
     * @param $model
     * 删除扩展数据
     */
    private static function deleteExtra($data,$model)
    {
        if(!$data->isEmpty())
        {
            foreach($data as $info)
            {
                $extra = model($model.'_data');
                $where = ['house_id'=>$info['id']];
                $detail = $extra::get($where);
                //删除房源图片
                if($extra->where($where)->delete())
                {
                    model('attachment')->deleteAttachment($detail['info'],$info['img'],$detail['file']);
                }
                //删除地铁关联数据
                \org\Relation::deleteByHouse($info['id'],$model);
                //删除学校关联数据
                \org\Relation::deleteByHouse($info['id'],$model,'school');
            }
        }

    }
    /**
     * @param $house_id
     * @param $data
     * 添加扩展数据
     */
    private static function optionHouseData($house_id,$data,$model,$update = false)
    {
        $info['seo_title'] = $data['title'];
        $info['seo_keys'] = $data['estate_name'];
        $info['house_id']  = $house_id;
        $info['info']      = isset($data['info']) ? $data['info'] : '';
        $info['file']      = $data['file'];//$this->getPic();
        $info['mating']    = '';
        if(isset($data['mating']))
        {
            $info['mating'] = implode(',',$data['mating']);
        }
        if($update)
        {
            model($model.'_data')->allowField(true)->save($info,['house_id'=>$house_id]);
        }else{
            model($model.'_data')->allowField(true)->save($info);
        }

    }
    /**
     * @return string
     * 图片上传
     */
    private static function uploadImg($user_id)
    {
        $img = '';
        if (isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
            try{
                $dir  = "user/".$user_id;
                $file = request()->file('file');
                $upload = new \org\Storage();
                $upload->thumbUploadFile($file,$dir);
                $img = $upload->getFullName();
            }catch(\Exception $e){
                // 上传失败获取错误信息
                \think\facade\Log::write('图片上传失败'.$e->getMessage(),'error');
            }
        }
        return $img;
    }
    /**
     * @param $obj
     * 添加图片
     */
    private static function getPic(){
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
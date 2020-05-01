<?php

namespace app\manage\service;
class Position
{
    /**
     * @param string $model
     * @return false|\PDOStatement|string|\think\Collection
     * 获取推荐位列表
     */
    public static function lists($model = 'house')
    {
        $where['model'] = $model;
        $pos_list = model('position_cate')->where($where)->field('id,title')->select();
        return $pos_list;
    }

    /**
     * @param $house_id
     * @param string $model
     * @return array
     * 获取指定楼盘的所有推荐位id
     */
    public static function getPositionIdByHouseId($house_id,$model = 'house')
    {
        $cate_id = model('position')->where(['house_id' => $house_id, 'model' => $model])->column('cate_id');//获取该楼盘 所属的推荐位id
        return $cate_id;
    }
    /**
     * @param $house_id string  楼盘id
     * @param $model string 数据模型
     * 添加或删除推荐位内容
     */
    public static function option($house_id,$model = 'house')
    {
        $position       = input('post.position/a');//新选择的推荐位id
        $exists_pos     = input('post.exists_pos');//原推荐位id
        $exists_pos_arr = [];
        $exists_pos && $exists_pos_arr = explode(',', $exists_pos);
        $add = [];
        $del = [];
        if ($position && $exists_pos_arr) {
            foreach ($position as $v) {
                if (!in_array($v, $exists_pos_arr)) {
                    //新增
                    $add[] = $v;
                }
            }
            foreach ($exists_pos_arr as $v) {
                if (!in_array($v, $position)) {
                    //删除
                    $del[] = $v;
                }
            }
        } elseif ($position && !$exists_pos) {
            $add = $position;
        } elseif (!$position && $exists_pos) {
            $del = $exists_pos_arr;
        }
        if ($del) {
            try{
                model('position')->where('house_id',$house_id)->where('model',$model)->where('cate_id','in',$del)->delete();
                model('position_cate')->where('id','in',$del)->where('num','gt',0)->setDec('num');
            }catch (\Exception $e){
                \think\facade\Log::record('删除推荐位出错：'.request()->baseFile().$e->getMessage());
            }
        }
        if ($add) {
            self::addPosition($model,$house_id,$add);
        }
    }
    public static function addPosition($model,$ids,$cate_id){
        $data = self::getInfoByModel($model,$ids,$cate_id);
        if($result=model('position')->saveAll($data)){
            if(is_numeric($ids)){
                $num = 1;
            }else{
                $num = count($result);
                model($model)->save(['rec_position'=>1],[['id','in',trim($ids,',')]]);//更新楼盘推荐位状态
            }
            model('position_cate')->where([['id','in',$cate_id]])->setInc('num',$num);//更新推荐位内容计数
            return true;
        }else{
            return false;
        }
    }
    //获取信息
    /**
     * @param $model 表名
     * @param $ids id字符串
     */
    private static function getInfoByModel($model,$ids,$cate_id){
        $obj = model($model);
        $data  = [];
        if(is_numeric($ids) && is_array($cate_id)){
            $info = $obj->where(['id'=>$ids])->field('id,title')->find();
            if($info){
                foreach($cate_id as $v){
                    $data[] = [
                        'house_id' => $info['id'],
                        'title'    => $info['title'],
                        'model'    => $model,
                        'cate_id'  => $v
                    ];
                }
            }
        }else{
            $lists = $obj->where([['id','in',trim($ids,',')]])->field('id,title')->select();
            if($lists){
                foreach($lists as $v){
                    $data[] = [
                        'house_id' => $v['id'],
                        'title'    => $v['title'],
                        'model'    => $model,
                        'cate_id'  => $cate_id,
                    ];
                }
            }
        }
        return $data;
    }
}
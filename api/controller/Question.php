<?php

namespace app\api\controller;
use think\Request;
class Question extends \think\Controller
{
    /**
     * @param Request $request
     * @return \think\response\Json
     * 保存问题
     */
    public function save(Request $request)
    {
        $refer = request()->header('Referer');
        $return['code']      = 0;
        $data['content']     = $request->content;
        $data['user_id']     = $request->user_id;
        $data['user_name']   = $request->user_name;
        $data['house_id']    = $request->house_id;
        $data['create_time'] = time();
        $data['status']      = getSettingCache('user','check_question');
        $data['city_id']     = $this->getCityIdByHouse($data['house_id']);//楼盘所属城市id
        try{
            if(db('question')->insert($data))
            {
                $return['code'] = 200;
                $return['msg']  = '提交成功';
            }
        }catch(\Exception $e){
            $return['msg'] = $e->getMessage();
        }$return['r'] = $refer;
        return json($return);
    }
    /**
     * @param $house_id
     * @param string $model
     * @return mixed
     * 获取指定楼盘所在城市id
     */
    private function getCityIdByHouse($house_id,$model = 'house')
    {
        $city_id = model($model)->where('id',$house_id)->value('city');
        return $city_id;
    }
}
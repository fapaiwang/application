<?php

namespace app\api\controller;


class CreateMap
{
    public static function index($param = [])
    {
        $base_path = '/uploads/map/'.$param['model'].'/';
        $title = str_replace('·','',$param['title']);
        if(!is_dir('.'.$base_path))
        {
            @mkdir('.'.$base_path,0777,true);
        }
        $file_name = $base_path.'map_'.$param['id'].'.jpg';
        $file_path = env('root_path').'public'.$file_name;
        if(!file_exists($file_path))
        {
            $url = "http://api.map.baidu.com/staticimage/v2?ak=".config('baidu_map_ak')."&width=400&height=200&center={$param['lng']},{$param['lat']}&labels={$param['lng']},{$param['lat']}&zoom=14&labelStyles={$title},1,14,0xffffff,0x000fff,1";
            self::download($url,$file_path);
        }
       return $file_name;
    }
    private static function download($url, $fileName)
    {
        try{
            $ch = curl_init();
            $fp = fopen($fileName, 'wb');
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 900);
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, 'downloadCallback');
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        }catch(\Exception $e){
            return json(['code'=>'400','msg'=>$e->getMessage()]);
        }
    }
}
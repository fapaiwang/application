<?php


if(!function_exists('rights'))
{
    function rights($str,$id,$name,$menuid=0){
        $str = str_replace('@id@',$id,$str);
        $str = str_replace('@menuid@',$menuid,$str);
        $str = str_replace('@name@',$name,$str);
        return $str;
    }
}
if(!function_exists('getPostionModel'))
{
    function getPostionModel($key = 0)
    {
        $data = [
            'house'=>'新房',
            'estate'=>'小区',
            'second_house'=>'二手房',
            'rental'=>'出租房',
            'office' => '写字楼出售',
            'office_rental' => '写字楼出租',
            'shops' => '商铺出售',
            'shops_rental' => '商铺出租'
        ];
        if(array_key_exists($key,$data))
        {
            return $data[$key];
        }else{
            return $data;
        }
    }
}
if(!function_exists('downloadCallback'))
{
    function downloadCallback($resource, $downloadSize = 0, $downloaded = 0, $uploadSize = 0, $uploaded = 0)
    {
        $data['total'] = $downloadSize;
        $data['download'] = $downloaded;
        cache('file_download',$data);
    }
}



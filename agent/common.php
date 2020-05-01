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



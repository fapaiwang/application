<?php
namespace app\common\service;


class ImageServer
{
    public function ImageWater($pathname,$source,$locate=10){
        $image = \think\Image::open($pathname);
        if($locate==10){
            $image->tilewater($source,100)->save($pathname);
        }else{
            $image->water($source,$locate,100)->save($pathname);
        }
        return true;
    }
}
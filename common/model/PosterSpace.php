<?php
namespace app\common\model;

class PosterSpace extends \think\Model
{
    protected $type = [
        'setting' => 'json'
    ];
    public function getTypeAttr($value){
        $type  = $this->typeArr();
        return $type[$value];
    }
    public function typeArr(){
        return $type  = [
            'banner'  => '矩形横幅',
            'couplet' => '对联广告',
            'imagelist' => '图片列表',
            'slide_pc'  => 'pc轮播图',
            'slide_mobile' => '手机轮播图',
            'text' => '文字广告',
            'code' => '代码广告'
        ];
    }
}
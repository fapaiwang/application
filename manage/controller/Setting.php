<?php

namespace app\manage\controller;

use app\common\controller\ManageBase;
class Setting extends ManageBase
{
    private $mod;
    public function initialize() {
        parent::initialize();
        $this->mod = model('setting');
    }
    public function index(){
        $type = input('param.type','site');
        if($info = $this->getInfo($type)){
            $this->assign('info',$info['data']);
            $this->assign('base',$info);
            $template = $type.'_edit';
        }else{
            $template = $type.'_add';
        }
        $this->assign('action_name',$type);
        return $this->fetch($template);
    }
    //空操作
    public function _empty($action){
        $this->redirect('index',['type'=>$action,'menuid'=>input('param.menuid/d',0)]);
    }
    private function getInfo($name){
        //$name = $this->action_name;
        $map['name'] = $name;
        $info = $this->mod->where($map)->find();
        return $info;
    }

    /**
     * 更新站点设置缓存
     */
    public function cache(){
        if($this->doCache()){
            $this->success('站点设置缓存更新完成','index');
        }else{
            $this->error('暂无可缓存数据','index');
        }
    }
    public function doCache(){
        $lists = $this->mod->select();
        if($lists){
            foreach($lists as $v){
                cache($v['name'],$v['data']);
            }
            return true;
        }
        return false;
    }
    //后置事件 生成缓存文件
    public function addDo(){
        \app\common\model\Setting::event('after_insert',function(Setting $that,$obj){
            cache($obj->name,$obj->data);
          $obj->name == 'site' && $that->createColorCss($obj->data);
            return true;
        });
        parent::addDo();
    }
    //后置事件 生成缓存文件
    public function editDo(){
        \app\common\model\Setting::event('after_update',function(Setting $that,$obj){
            cache($obj->name,$obj->data);
            $obj->name == 'site' && $that->createColorCss($obj->data);
            return true;
        });
        parent::editDo();
    }
    public function ajaxUploadImg()
    {
        $file = request()->file('file');
        $img  = '';
        // 移动到框架应用根目录/public/uploads/ 目录下
        $path = config('uploads_path') . strtolower($this->controller_name) . '/';//echo env('root_path') . $path;exit;
        $info = $file->validate(config('upload_img_rule'))->move(env('root_path') . $path);
        if ($info) {
            $img = '/uploads/' . strtolower($this->controller_name) . '/' . str_replace('\\', '/', $info->getSaveName());
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
        if ($img) {
            return $this->ajaxReturn(1, '上传成功', $img);
        } else {
            return $this->ajaxReturn(0, '请选择图片');
        }
    }
    private function createColorCss($data)
    {
        $root_path          = env('root_path');
        $template_file_path = $root_path.'public/static/css/pc-color-template.css';
        $color_file_path    = $root_path.'public/static/home/css/color.css';
        $template_info = @file_get_contents($template_file_path);

        $mobile_template_file_path = $root_path.'public/static/css/mobile-color-template.css';
        $mobile_color_file_path    = $root_path.'public/static/mobile/css/color.css';
        $mobile_template_info = @file_get_contents($mobile_template_file_path);

        $replace['@mainColor'] = $data['main_color'] ? $data['main_color'] : '#d32f2f';
        $replace['@textColor'] = $data['text_color'] ? $data['text_color'] : '#ffffff';
        $replace['@selectedColor'] = $data['selected_color'] ? $data['selected_color'] : '#B71B1C';
        $result = strtr($template_info,$replace);
        $mobile_result = strtr($mobile_template_info,$replace);

        @file_put_contents($color_file_path,$result);
        @file_put_contents($mobile_color_file_path,$mobile_result);

    }
}
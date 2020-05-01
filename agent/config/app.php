<?php
return [
    'NOT_AUTH_MODULE' => 'index|public|api|ajax|ueditor|Account',//不进行验证的控制器
    'NOT_AUTH_ACTION' => 'ajax|public|addDo|editDo|deleteImg|deleteVideo',//不进行验证的方法(包含字符)
// 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => Env::get('root_path') . 'public/jump/manage/dispatch_jump.tpl',
    'dispatch_error_tmpl'    => Env::get('root_path') . 'public/jump/manage/dispatch_jump.tpl',
];
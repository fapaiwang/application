<?php

return [
    'SUPERADMIN_ROLEID' => 1,//超级管理员角色id
    'NOT_AUTH_MODULE' => 'index|public|api|ajax|ueditor',//不进行验证的控制器
    'NOT_AUTH_ACTION' => 'ajax|public|addDo|editDo|save|deleteImg',//不进行验证的方法(包含字符)
    'ATUO_AUTH_RIGHTS' => true,//是否开启自动验证
// 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => Env::get('root_path') . 'public/jump/manage/dispatch_jump.tpl',
    'dispatch_error_tmpl'    => Env::get('root_path') . 'public/jump/manage/dispatch_jump.tpl',
];
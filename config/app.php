<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用地址
    'app_host'         => env('app.host', ''),
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 默认应用
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',

    // 应用映射（自动多应用模式有效）
    'app_map'          => [],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => [],



    // 异常页面的模板文件
    'exception_tmpl'   => app()->getThinkPath() . 'tpl/think_exception.tpl',


    // CORS 自动配置跨域
    'cors_auto'               => true,
    // CORS 配置跨域域名
    'cors_host'               => [],
    // CORS 授权请求方法
    'cors_methods'            => 'GET,PUT,POST,PATCH,DELETE',
    // CORS 跨域头部字段
    'cors_headers'            => 'Api-Type,Api-Name,Api-Uuid,Api-Token,User-Form-Token,User-Token,Token',



	// 默认跳转页面对应的模板文件
    'dispatch_success_tmpl' => app()->getBasePath() . 'common/view/dispatch_jump.tpl',
    'dispatch_error_tmpl'   => app()->getBasePath() . 'common/view/dispatch_jump.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => true,

	'password_salt'       => '@BEISOO', //密码加密串
];

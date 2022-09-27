<?php
// +----------------------------------------------------------------------
// | Cookie设置
// +----------------------------------------------------------------------
return [
    // cookie 保存时间
    'expire'    => 0,
    // cookie 保存路径
    'path'      => '/',
    // cookie 有效域名
    'domain'    => '',
    // httponly设置
    'httponly'  => false,
    // 是否使用 setcookie
    'setcookie' => true,

	// cookie 安全传输，只支持 https 协议
    'secure'    => app()->request->isSsl(),
    // samesite 安全设置，支持 'strict' 'lax' 'none'
    'samesite'  => app()->request->isSsl() ? 'none' : 'lax',
];

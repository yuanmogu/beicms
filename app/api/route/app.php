<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::rule('wiki','wiki/index');
Route::rule('token','auth/token');

/* // 如强制路由 可开启
Route::miss(function() {
	$result = [
		'code'=> -1,
		'msg'=> '接口调用错误',
		'status'=> 'API_ERROR',
	];
	return json($result);
});
*/
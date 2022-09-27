<?php
// +----------------------------------------------------------------------
// | 模板设置
// +----------------------------------------------------------------------


$template = get_theme_name();

return [

	// 模板路径
    'view_path'       => './template/' . $template . '/html/', 

    // 自定义标签库
	
	'taglib_pre_load' => 'app\common\taglib\Be', 
	
	'tpl_replace_string'  =>  [
		'__ASSETS__'=>'/template/' . $template . '/assets',
		'__IMAGES__'=>'/template/' . $template . '/images',
	]
];
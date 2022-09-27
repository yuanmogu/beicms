<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
		'xadmin:database'	=> 'app\admin\library\command\Database',
		'xadmin:queue'		=> 'app\admin\library\command\Queue',
		'xadmin:replace'	=> 'app\admin\library\command\Replace',
		'xadmin:clear'		=> 'app\admin\library\command\Clear',
    ],
];


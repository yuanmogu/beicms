<?php
declare (strict_types = 1);

namespace app\common\validate;

use think\Validate;

class CmsLinks extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule =   [
        'title'				=> 'require',
		'url'				=> 'url',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message  =   [
        'title.require'		=> '请输入文档标题',
		'url.url'		=> '输入的链接地址不正确',

    ];
}

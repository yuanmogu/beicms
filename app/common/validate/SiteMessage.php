<?php
declare (strict_types = 1);

namespace app\common\validate;

use think\Validate;

class SiteMessage extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */



    protected $rule =   [
        'title'				=> 'require',
		'phone'				=> 'require|mobile',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message  =   [
        'title.require'		=> '请输入留言主题',
		'phone.require'		=> '请输入手机号码',
		'phone.mobile'		=> '手机号码格式不正确',

    ];
}

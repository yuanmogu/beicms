<?php
declare (strict_types = 1);

namespace app\admin\validate;

use think\Validate;

class SystemUser extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule =   [
        'username'  => 'require|alphaNum|max:15|unique:system_user',
		'nickname'  => 'require|chsDash|max:30',
		'contact_mail'  => 'email',
		'contact_phone'  => 'mobile',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message  =   [
        'username.require'	=> '请输入用户账号',
		'username.alphaNum'	=> '用户账号只能由字母和数字组成',
		'username.max'		=> '用户账号最长15位',
        'username.unique'	=> '用户账号已经存在了',
		'nickname.require'	=> '请输入用户昵称',
		'username.chsDash'	=> '用户昵称不能包含特殊字符',
		'username.max'		=> '用户昵称最长30位',
		'contact_mail.email'	=> '邮件格式不正确',
		'contact_phone.mobile'	=> '手机号码不正确',

    ];
}

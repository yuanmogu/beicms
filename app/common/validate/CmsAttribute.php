<?php
declare (strict_types = 1);

namespace app\common\validate;

use think\Validate;

class CmsAttribute extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule =   [
        'title'				=> 'require|chsAlpha|max:10',
		'name'				=> 'require|alpha|max:20'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message  =   [
        'title.require'		=> '请输入字段标题',
		'title.chsAlpha'	=> '字段标题只能由汉字、字母组成',
		'title.max'			=> '字段标题最长10位',

		'name.require'	=> '请输入字段名',
		'name.alpha'	=> '字段名只能由英文字母组成',
		'name.max'		=> '字段名最长20位'

    ];
}

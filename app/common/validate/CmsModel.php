<?php
declare (strict_types = 1);

namespace app\common\validate;

use think\Validate;

class CmsModel extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */



    protected $rule =   [
        'title'				=> 'require|chsAlpha|max:20',
		'name'				=> 'require|alpha|max:20|unique:cms_model'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message  =   [
        'title.require'		=> '请输入模型名称',
		'title.chsAlpha'	=> '模型名称只能由汉字、字母组成',
		'title.max'			=> '模型名称最长20位',
        'name.unique'	=> '模型表名已经存在了',
		'name.require'	=> '请输入模型表名',
		'name.alpha'	=> '模型表名只能由英文字母组成',
		'name.max'		=> '模型表名最长20位'

    ];
}

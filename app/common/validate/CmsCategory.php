<?php
declare (strict_types = 1);

namespace app\common\validate;

use think\Validate;

class CmsCategory extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule =   [
        'title'		=> 'require',
		'folder'	=> 'require|unique:cms_category',
		'model_id'	=>'requireIf:type,archives',
		'link'		=>'requireIf:type,link'

    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message  =   [
        'title.require'		=> '请输入标题',
		'folder.require'	=> '请输入页面目录',
		'folder.unique'		=> '页面目录已经存在了，请修改。',
		'model_id.requireIf'	=> '文档类型，请选择一个模型',
		'link.requireIf'		=> '外链类型，请输入链接网址',


    ];
}

<?php


// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2022 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: https://gitee.com/zoujingli/ThinkLibrary
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/ThinkLibrary
// | github 代码仓库：https://github.com/zoujingli/ThinkLibrary
// +----------------------------------------------------------------------

declare (strict_types=1);

namespace app\admin\model;

use think\Model;

/**
 * 系统菜单模型
 * Class SystemMenu
 * @package think\admin\model
 */
class SystemMenu extends Model
{
    /**
     * 日志名称
     * @var string
     */
    protected $oplogName = '系统菜单';

    /**
     * 日志类型
     * @var string
     */
    protected $oplogType = '系统菜单管理';

    /**
     * 格式化创建时间
     * @param string $value
     * @return string
     */
    public function getCreateAtAttr(string $value): string
    {
        return format_datetime($value);
    }
}
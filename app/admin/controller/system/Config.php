<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2022 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// | 免费声明 ( https://thinkadmin.top/disclaimer )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/ThinkAdmin
// | github 代码仓库：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\admin\controller\system;

use app\AdminController;
use app\admin\library\service\AdminService;
use app\admin\library\service\SystemService;
use app\common\library\storage\AliossStorage;
use app\common\library\storage\QiniuStorage;
use app\common\library\storage\TxcosStorage;

/**
 * 系统参数配置
 * Class Config
 * @package app\admin\controller
 */
class Config extends AdminController
{
    const themes = [
        'default' => '默认色0',
        'white'   => '简约白0',
        'red-1'   => '玫瑰红1',
        'blue-1'  => '深空蓝1',
        'green-1' => '小草绿1',
        'black-1' => '经典黑1',
        'red-2'   => '玫瑰红2',
        'blue-2'  => '深空蓝2',
        'green-2' => '小草绿2',
        'black-2' => '经典黑2',
    ];

    /**
     * 系统参数配置
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '系统参数配置';
        $this->super = AdminService::instance()->isSuper();
        $this->fetch();
    }

    /**
     * 修改系统参数
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function system()
    {
        if ($this->request->isGet()) {
            $this->title = '修改系统参数';
            $this->themes = static::themes;
            $this->fetch();
        } else {
            $post = $this->request->post();
            // 数据数据到系统配置表
            foreach ($post as $k => $v) sysconf($k, $v);
            sysoplog('系统配置管理', "修改系统参数成功");
            $this->success('修改系统参数成功！', 'javascript:location.reload()');
        }
    }


}
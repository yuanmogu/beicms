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

namespace app\admin\controller;

use app\AdminController;
use app\admin\model\SystemUser;
use app\admin\validate\SystemUser as userValidate;
use app\admin\library\service\AdminService;
use app\admin\library\service\MenuService;

use think\exception\ValidateException;

/**
 * 后台界面入口
 * Class Index
 * @package app\admin\controller
 */
class Index extends AdminController
{
    /**
     * 显示后台首页
	 * @login true
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        /*! 根据运行模式刷新权限 */
        $debug = $this->app->isDebug();
        AdminService::instance()->apply($debug);
        /*! 读取当前用户权限菜单树 */
        $this->menus = MenuService::instance()->getTree();
        /*! 判断当前用户的登录状态 */
        $this->login = AdminService::instance()->isLogin();
        /*! 菜单为空且未登录跳转到登录页 */
        if (empty($this->menus) && empty($this->login)) {
            $this->redirect(sysuri('admin/login/index'));
        } else {
            $this->title = '系统管理后台';
            $this->super = AdminService::instance()->isSuper();
            $this->fetch();
        }
    }

    /**
     * 后台
     * @login true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */

    public function welcome()
    {
		$this->fetch();
	}


}

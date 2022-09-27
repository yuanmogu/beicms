<?php

namespace app\admin\controller\system;

use app\AdminController;
use libs\DataExtend;
use app\admin\model\SystemMenu;
use app\admin\library\service\AdminService;
use app\admin\library\service\MenuService;
use app\admin\library\service\NodeService;

/**
 * 系统菜单管理
 * Class Menu
 * @package app\admin\controller
 */
class Menu extends AdminController
{

	// 初始化函数
    public function initialize()
    {
        parent::initialize();
        $this->model = new SystemMenu();
    }

    /**
     * 系统菜单管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
		$this->title = '系统菜单管理';
		$this->type = input('get.type', 'index');

		if ($this->request->isGet()) {
			$get = $this->request->get();
			if($this->output == 'layui.table'){
				$total = $this->model->where([])->count();
				$data = $this->model->where([])->select()->toArray();

				$data = DataExtend::arr2tree($data);
				// 回收站过滤有效菜单
				if ($this->type === 'recycle') foreach ($data as $k1 => &$p1) {
					if (!empty($p1['sub'])) foreach ($p1['sub'] as $k2 => &$p2) {
						if (!empty($p2['sub'])) foreach ($p2['sub'] as $k3 => $p3) {
							if ($p3['status'] > 0) unset($p2['sub'][$k3]);
						}
						if (empty($p2['sub']) && ($p2['url'] === '#' or $p2['status'] > 0)) unset($p1['sub'][$k2]);
					}
					if (empty($p1['sub']) && ($p1['url'] === '#' or $p1['status'] > 0)) unset($data[$k1]);
				}
				// 菜单数据树数据变平化
				$data = DataExtend::arr2table($data);
				foreach ($data as &$vo) {
					if ($vo['url'] !== '#' && !preg_match('/^(https?:)?(\/\/|\\\\)/i', $vo['url'])) {
						$vo['url'] = trim(sysuri($vo['url']) . ($vo['params'] ? "?{$vo['params']}" : ''), '\\/');
					}
				}

				return json(['msg' => '', 'code' => 0, 'count' => $total, 'data' => $data]);
			}
		}

       
        $this->fetch();
    }


    /**
     * 添加系统菜单
     * @auth true
     */
    public function add()
    {
		$this->pid = input('pid', '0');
		/* 读取系统功能节点 */
		$this->auths = [];
		$this->nodes = MenuService::instance()->getList();
		foreach (NodeService::instance()->getMethods() as $node => $item) {
			if ($item['isauth'] && substr_count($node, '/') >= 2) {
				$this->auths[] = ['node' => $node, 'title' => $item['title']];
			}
		}
		/* 列出可选上级菜单 */
		$menus = $this->model->order('sort desc,id asc')->column('id,pid,icon,url,node,title,params', 'id');
		$this->menus = DataExtend::arr2table(array_merge($menus, [['id' => '0', 'pid' => '-1', 'url' => '#', 'title' => '顶部菜单']]));
		foreach ($this->menus as $key => $menu) if ($menu['spt'] >= 3 || $menu['url'] !== '#') unset($this->menus[$key]);
		$this->fetch();
    }

    /**
     * 编辑系统菜单
     * @auth true
     */
    public function edit($id)
    {
		$data	= $this->model->find($id);

		/* 读取系统功能节点 */
		$this->auths = [];
		$this->nodes = MenuService::instance()->getList();
		foreach (NodeService::instance()->getMethods() as $node => $item) {
			if ($item['isauth'] && substr_count($node, '/') >= 2) {
				$this->auths[] = ['node' => $node, 'title' => $item['title']];
			}
		}
		/* 列出可选上级菜单 */
		$menus = SystemMenu::order('sort desc,id asc')->column('id,pid,icon,url,node,title,params', 'id');
		$this->menus = DataExtend::arr2table(array_merge($menus, [['id' => '0', 'pid' => '-1', 'url' => '#', 'title' => '顶部菜单']]));
		if (isset($data['id'])) foreach ($this->menus as $menu) if ($menu['id'] === $data['id']) $data = $menu;
		foreach ($this->menus as $key => $menu) if ($menu['spt'] >= 3 || $menu['url'] !== '#') unset($this->menus[$key]);
		if (isset($data['spt']) && isset($data['spc']) && in_array($data['spt'], [1, 2]) && $data['spc'] > 0) {
			foreach ($this->menus as $key => $menu) if ($data['spt'] <= $menu['spt']) unset($this->menus[$key]);
		}

		$this->vo = $data;
		$this->fetch();
    }


    /**
     * 删除系统菜单
     * @auth true
     */
    public function remove()
    {
        if ($this->model->destroy($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}

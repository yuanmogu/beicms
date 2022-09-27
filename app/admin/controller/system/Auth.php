<?php

namespace app\admin\controller\system;

use app\AdminController;

use app\admin\model\SystemAuth;
use app\admin\model\SystemNode;
use app\admin\library\service\AdminService;

/**
 * 系统权限管理
 * Class Auth
 * @package app\admin\controller
 */
class Auth extends AdminController
{

	// 初始化函数
    protected function initialize()
    {
        parent::initialize();
        $this->model = new SystemAuth();
    }

    /**
     * 系统权限管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
		$this->title = '系统权限管理';	
		if ($this->request->isGet()) {
			$get = $this->request->get();
			if($this->output == 'layui.table'){
				$where = [];
				if(!empty($get['create_time'])){
					$daterange = explode(' ~ ', $get['create_time']);
					$where[] = ['create_time', 'BETWEEN TIME', [$daterange[0], $daterange[1]]];
				}
				if(!empty($get['status'])){
					$where[] = ['status', '=', $get['status']];
				}
				if(!empty($get['utype'])){
					$where[] = ['utype', '=', $get['utype']];
				}
				if(!empty($get['title'])){
					$where[] = ['title', 'like', '%'.$get['title'].'%'];
				}
				if(!empty($get['desc'])){
					$where[] = ['desc', 'like', '%'.$get['desc'].'%'];
				}
				
				$cfg = ['list_rows' => $get['limit'], 'query' => $get];
				$data = $this->model->where($where)->paginate($cfg)->toArray();
				return json(['msg' => '', 'code' => 0, 'count' => $data['total'], 'data' => $data['data']]);
			}
		}
		
		$this->fetch();
		
    }

    /**
     * 添加系统权限
     * @auth true
     */
    public function add()
    {
        $this->fetch();
    }

    /**
     * 编辑系统权限
     * @auth true
     */
    public function edit($id)
    {
		$this->vo	= $this->model->find($id);
        $this->fetch();
    }



    /**
     * 删除系统权限
     * @auth true
     */
    public function remove($id)
    {
        if ($this->model->destroy($id)) {
			SystemNode::where('auth',$id)->delete();
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 权限配置节点
     * @auth true
     * @throws \ReflectionException
     */
    public function apply($id)
    {
		$this->title = "编辑授权";

        if (input('action') === 'get') {
            $admin = AdminService::instance();
            if ($this->app->isDebug()) $admin->clearCache();
            $nodes = SystemNode::where('auth',$id)->column('node');
            $this->success('获取权限节点成功！', $admin->getTree($nodes));
        } elseif (input('action') === 'save') {
            [$post, $data] = [$this->request->post(), []];
            foreach ($post['nodes'] ?? [] as $node) {
                $data[] = ['auth' => $id, 'node' => $node];
            }
            SystemNode::where('auth',$id)->delete();
            SystemNode::insertAll($data);
            sysoplog('系统权限管理', "配置系统权限[{$id}]授权成功");
            $this->success('访问权限修改成功！', 'javascript:history.back()');
        } else {
			$this->vo	= $this->model->find($id);
            $this->fetch();
        }
    }

}

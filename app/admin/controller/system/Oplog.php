<?php

namespace app\admin\controller\system;

use Exception;

use app\AdminController;
use app\admin\model\SystemOplog;
use think\exception\HttpResponseException;

/**
 * 系统日志管理
 * Class Oplog
 * @package app\admin\controller
 */
class Oplog extends AdminController
{

	// 初始化函数
    protected function initialize()
    {
        parent::initialize();
        $this->model = new SystemOplog();
    }
    /**
     * 系统日志管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
		$this->title = '系统日志管理';

		if ($this->request->isGet()) {
			$get = $this->request->get();
			if(!empty($this->output)){
				$where = [];

				if(!empty($get['create_time'])){
					$daterange = explode(' ~ ', $get['create_time']);
					$where[] = ['create_time', 'BETWEEN TIME', [$daterange[0], $daterange[1]]];
				}
				if(!empty($get['username'])){
					$where[] = ['username', '=', $get['username']];
				}
				if(!empty($get['action'])){
					$where[] = ['action', '=', $get['action']];
				}
				if(!empty($get['content'])){
					$where[] = ['content', 'like', '%'.$get['content'].'%'];
				}
				if(!empty($get['geoip'])){
					$where[] = ['geoip', 'like', '%'.$get['geoip'].'%'];
				}
				if(!empty($get['node'])){
					$where[] = ['node', 'like', '%'.$get['node'].'%'];
				}
				
				$cfg = ['list_rows' => $get['limit'], 'query' => $get];
				$data = $this->model->where($where)->order('create_time desc')->paginate($cfg)->toArray();

				if($this->output == 'layui.table'){
					return json(['msg' => '', 'code' => 0, 'count' => $data['total'], 'data' => $data['data']]);
				}
				//表导出
				if($this->output == 'json'){
					$result = ['page' => ['limit' => $data['per_page'], 'total' => $data['total'], 'pages' => $data['last_page'], 'current' => $data['current_page']], 'list' => $data['data']];
					return json(['code' => 1, 'data' => $result]);
				}
			}
		}
		
		$columns = $this->model->column('action,username', 'id');
		$this->users = array_unique(array_column($columns, 'username'));
		$this->actions = array_unique(array_column($columns, 'action'));
		$this->fetch();
		
    }

    /**
     * 清理系统日志
     * @auth true
     */
    public function clear()
    {
        try {
            $table = $this->model->getTable();
			\think\facade\Db::execute("truncate table `{$table}`");
            sysoplog('系统运维管理', '成功清理所有日志数据');
            $this->success('日志清理成功！');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            $this->error("日志清理失败，{$exception->getMessage()}");
        }
    }

    /**
     * 删除系统日志
     * @auth true
     */
    public function remove($id)
    {
 		if(is_array($id)){
			$id = arr2str($id);
		}

        if ($this->model->whereIn('id',$id)->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}

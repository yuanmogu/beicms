<?php

namespace app\admin\controller\cms;


use app\common\model\CmsSpecial;


use app\AdminController;
/**
 * 专题管理
 * Class Flink
 * @package app\data\controller
 */
class Special extends AdminController
{

	// 初始化函数
    protected function initialize()
    {
        parent::initialize();
        $this->model = new CmsSpecial();
    }

    /**
     * 专题管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
		$this->title = '专题管理';

		$this->types = CmsSpecial::types();
		$this->type = input('get.type', $this->types[0] ?? '-');

		if ($this->request->isGet()) {
			$get = $this->request->get();
			if($this->output == 'layui.table'){
				$where = [];

				if(!empty($get['type'])){
					$where[] = ['type', '=', $get['type']];
				}

				if(!empty($get['create_time'])){
					$daterange = explode(' ~ ', $get['create_time']);
					$where[] = ['create_time', 'BETWEEN TIME', [$daterange[0], $daterange[1]]];
				}

				if(!empty($get['status'])){
					$where[] = ['status', '=', $get['status']];
				}
				
				if(!empty($get['title'])){
					$where[] = ['title', 'like', '%'.$get['title'].'%'];
				}
				if(!empty($get['url'])){
					$where[] = ['url', 'like', '%'.$get['url'].'%'];
				}
				
				$cfg = ['list_rows' => $get['limit'], 'query' => $get];
				$data = $this->model->where($where)->paginate($cfg)->toArray();
				return json(['msg' => '', 'code' => 0, 'count' => $data['total'], 'data' => $data['data']]);
			}
		}
		

		$this->fetch();
		
    }

    /**
     * 添加专题
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        $this->title = '添加专题';
		$this->types = CmsSpecial::types();
		$this->types[] = '--- 新增类型 ---';
		$this->type = input('get.type') ?: ($this->types[0] ?? '-');

		$this->template_list = get_template();
        $this->fetch();
    }

    /**
     * 编辑专题
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit($id)
    {

        $this->title = '编辑专题';
		$this->types = CmsSpecial::types();
		$this->types[] = '--- 新增类型 ---';
		$this->type = input('get.type') ?: ($this->types[0] ?? '-');

		$this->template_list = get_template();

		$this->vo	= $this->model->find($id);
        $this->fetch();
    }



    /**
     * 删除专题
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function remove($id)
    {
		if(!is_array($id)){
			$id = str2arr($id);
		}
		sysoplog('内容管理', "删除专题");
        if ($this->model->destroy($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

}
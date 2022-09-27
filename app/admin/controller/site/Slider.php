<?php

namespace app\admin\controller\site;

use app\common\model\SiteSlider;

use app\AdminController;
/**
 * 幻灯片管理
 * Class Flink
 * @package app\data\controller
 */
class Slider extends AdminController
{

	// 初始化函数
    protected function initialize()
    {
        parent::initialize();
        $this->model = new SiteSlider();
    }

    /**
     * 幻灯片管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
		$this->title = '幻灯片管理';

		$this->types = SiteSlider::types();
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
		
				
				$cfg = ['list_rows' => $get['limit'], 'query' => $get];
				$data = $this->model->where($where)->paginate($cfg)->toArray();
				return json(['msg' => '', 'code' => 0, 'count' => $data['total'], 'data' => $data['data']]);
			}
		}
		

		$this->fetch();
		
    }

    /**
     * 添加幻灯片
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        $this->title = '添加幻灯片';
		$this->types = SiteSlider::types();
		$this->types[] = '--- 新增类型 ---';
		$this->type = input('get.type') ?: ($this->types[0] ?? '-');
		sysoplog('站点管理', "添加幻灯片");
        $this->fetch();
    }

    /**
     * 编辑幻灯片
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit($id)
    {

        $this->title = '编辑幻灯片';
		$this->types = SiteSlider::types();
		$this->types[] = '--- 新增类型 ---';
		$this->type = input('get.type') ?: ($this->types[0] ?? '-');

		$this->vo	= $this->model->find($id);
		sysoplog('站点管理', "编辑幻灯片");
        $this->fetch();
    }



    /**
     * 删除幻灯片
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function remove($id)
    {
		if(is_array($id)){
			$id = arr2str($id);
		}
		sysoplog('站点管理', "删除幻灯片");
        if ($this->model->whereIn('id',$id)->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

}
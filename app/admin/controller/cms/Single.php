<?php

namespace app\admin\controller\cms;


use app\common\model\CmsSingle;
use app\common\validate\CmsSingle as singleValidate;

use app\AdminController;
/**
 * 单页管理
 * Class Flink
 * @package app\admin\controller
 */
class Single extends AdminController
{


	// 初始化函数
    protected function initialize()
    {
        parent::initialize();
        $this->model = new CmsSingle();
		$this->validate = new singleValidate();
    }

    /**
     * 编辑单页
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit($category_id = 0)
    {

        $this->title = '编辑单页';

		$data = $this->model->where('category_id',$category_id)->findOrEmpty();

		if ($data->isEmpty()) {
			$data = new CmsSingle;
			$data->category_id	= $category_id;
			$data->save();
		}

		$this->vo = $data;

		sysoplog('内容管理', "编辑单页");

        $this->fetch();
    }



}
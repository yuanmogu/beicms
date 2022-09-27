<?php

namespace app\admin\controller\cms;

use app\common\model\CmsCategory as categoryModel;
use app\common\validate\CmsCategory as categoryValidate;

use app\common\model\CmsModel;
use app\common\model\CmsSingle;

use libs\DataExtend;

use app\AdminController;
/**
 * 栏目分类管理
 * Class Cate
 * @package app\data\controller\shop
 */
class Category extends AdminController
{


    /**
     * 最大级别
     * @var integer
     */
    protected $maxLevel = 5;

	// 初始化函数
    protected function initialize()
    {
        parent::initialize();
        $this->model = new categoryModel();
		$this->validate = new categoryValidate();

    }


    /**
     * 栏目分类管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {

		$this->title = '栏目分类管理';

		if ($this->request->isGet()) {
			$get = $this->request->get();
			if($this->output == 'layui.table'){
				$total = $this->model->where([])->count();
				$data = $this->model->where([])->order('sort asc, id asc')->append(['model_title','type_text'])->select()->toArray();
				$data = DataExtend::arr2table($data);
				return json(['msg' => '', 'code' => 0, 'count' => $total, 'data' => $data]);
			}
		}
		
		$this->fetch();


    }


    /**
     * 添加栏目
	 * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add($type='archives')
    {
		$this->title = '添加栏目';

		$data['pid'] = input('pid', '0');

		$this->cates = categoryModel::getParentData($this->maxLevel, $data, [
			'id' => '0', 'pid' => '-1', 'title' => '顶部分类',
		]);
		
		if($type == 'archives'){
			$this->cms_model = CmsModel::where('status',1)->order('id asc')->column('id,title,name,template_list,template_show', 'id');
		}	
		//获取所有模版
		$this->template_list = get_template();

		$this->vo = $data;

		sysoplog('内容管理', "添加栏目");

        $this->fetch('add_'.$type);
    }

    /**
     * 编辑栏目
	 * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit($id)
    {
		$this->title = '编辑栏目';

		$data = $this->model->find($id)->toArray();

		$this->cates = categoryModel::getParentData($this->maxLevel, $data, [
			'id' => '0', 'pid' => '-1', 'title' => '顶部分类',
		]);

		if($data['type'] == 'archives'){
			$this->cms_model = CmsModel::where('status',1)->order('id asc')->column('id,title,name,template_list,template_show', 'id');
		}

		//获取所有模版
		$this->template_list = get_template();

		$this->vo = $data;

		sysoplog('内容管理', "编辑栏目");

        $this->fetch('edit_'.$data['type']);
    }


    /**
     * 删除栏目
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
		
		$this->model->startTrans();

		try {
			$err = '';
			foreach($id as $arc){

				$category = $this->model->findOrEmpty($arc);

				$cate_son = $this->model->where('pid',$category->id)->findOrEmpty();
				if (!$cate_son->isEmpty()) {
					//abort(412, $category->title.'删除失败，请先删除子栏目。');

					$err .=  ' - '.$category->title;
					continue;
				}
				
				if($category->type == "single") CmsSingle::where('category_id',$category->id)->delete();
				$category->delete();
			}

			$this->model->commit();
		} catch (\PDOException $e) {
			$this->model->rollback();
			return $this->error($e->getMessage());
		} catch (\Throwable $th) {
			$this->model->rollback();
			return $this->error($th->getMessage());
		}

		sysoplog('内容管理', "删除栏目");

		if(!empty($err)) $this->success($err.' 删除失败，请先删除子栏目。');
		$this->success('删除成功');
    }

}
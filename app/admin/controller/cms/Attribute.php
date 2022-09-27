<?php

namespace app\admin\controller\cms;

use app\common\model\CmsAttribute;
use app\common\validate\CmsAttribute as attributeValidate;

use app\common\model\CmsModel as modelModel;

use exts\DataTable;

use app\AdminController;


/**
 * 模型字段管理
 * Class Pager
 * @package app\admin\controller\cms
 */
class Attribute extends AdminController
{

	// 初始化函数
    protected function initialize()
    {
        parent::initialize();
		$this->model = new CmsAttribute();
		$this->validate = new attributeValidate();

		$this->type_list = $this->model->getTypeList();

    }

    /**
     * 模型字段页面管理
     * @auth true
	 * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */ 
    public function index($model_id)
    {

		$this->cms_model = modelModel::where('id',$model_id)->findOrEmpty();
		if ($this->cms_model->isEmpty()) return $this->error('未找到相关模型');

		
		$this->title = $this->cms_model->title.' 模型字段管理';

		if ($this->request->isGet()) {
			$get = $this->request->get();
			if($this->output == 'layui.table'){
				$cfg = ['list_rows' => $get['limit'], 'query' => $get];
				$data = $this->model->where('model_id', $model_id)->order('sort asc, id asc')->append(['group_name'])->paginate($cfg)->toArray();
				return json(['msg' => '', 'code' => 0, 'count' => $data['total'], 'data' => $data['data']]);
			}
		}

		$this->fetch();
		
    }

 
    /**
     * 添加模型字段
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add($model_id = 0)
    {
		$this->cms_model = modelModel::where('id',$model_id)->findOrEmpty();
		if ($this->cms_model->isEmpty()) return $this->error('未找到相关模型');

		$this->attr_group = $this->cms_model->attr_group;

        $this->fetch();
    }



    /**
     * 添加模型字段
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
	public function save() 
	{
		if ($this->request->isPost()) {

			$post = $this->request->post();

			try {
				$result = $this->validate->check($post);
				if (true !== $result) return $this->error($this->validate->getError());
			} catch (ValidateException $e) {			
				return $this->error($e->getError());
			}

			$cms_model = modelModel::where('id', $post['model_id'])->findOrEmpty();
			
			if ($cms_model->isEmpty()) {
				$this->error("没有找到模型");
			}

			$table = 'cms_archives_'.$cms_model->name;

            $this->model->startTrans();

			try {

				$this->model->data($post, true);
				
                $status = $this->model->create($post);
				
				if(!$status){
					throw new \think\Exception("添加失败");
				}
				
				$datatable = new DataTable();
				
				$post['after'] = $this->model->where('name', '<>', $post['name'])->where('model_id', $post['model_id'])->order('sort desc, id desc')->value('name');

				if(!$datatable->columField(strtolower($table), $post)->execute()){
					throw new \think\Exception("添加字段失败");
				}

				$this->model->commit();
            } catch (\PDOException $e) {
                $this->model->rollback();
                return $this->error($e->getMessage());
            } catch (\Throwable $th) {
                $this->model->rollback();
                return $this->error($th->getMessage());
            }
			
			sysoplog('内容管理', "添加模型字段");
			return $this->success('添加成功');
		}
	}

    /**
     * 编辑模型字段
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit($id)
    {

		$this->vo = $this->model->find($id);

		$this->cms_model = modelModel::where('id',$this->vo->model_id)->findOrEmpty();
		if ($this->cms_model->isEmpty()) return $this->error('未找到相关模型');

		$this->attr_group = $this->cms_model->attr_group;
        $this->fetch();
    }


    /**
     * 删除字段
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function remove($id)
    {
		$attribute = $this->model->findOrEmpty($id);
		if ($attribute->isEmpty()) {
			return $this->error("没有找到此字段");
		}

		$this->cms_model = modelModel::where('id',$attribute->model_id)->findOrEmpty();
		if ($this->cms_model->isEmpty()) return $this->error('未找到相关模型');
		
		$this->model->startTrans();
		try {

			$tablename = 'cms_archives_'.$this->cms_model->name;

			$db = new Datatable();
			if ($db->CheckField($tablename, $attribute->name)) {
				$db->delField($tablename, $attribute->name)->query();
			}

			$attribute->delete();

			$this->model->commit();
		} catch (\PDOException $e) {
			$this->model->rollback();
			return $this->error($e->getMessage());
		} catch (\Throwable $th) {
			$this->model->rollback();
			return $this->error($th->getMessage());
		}

		sysoplog('内容管理', "删除模型字段");

		$this->success('删除成功');
      
    }
}
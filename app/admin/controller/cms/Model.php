<?php

namespace app\admin\controller\cms;

use app\common\model\CmsModel;
use app\common\validate\CmsModel as modelValidate;

use app\common\model\CmsAttribute as attributeModel;

use exts\DataTable;

use app\AdminController;


/**
 * 模型管理
 * Class Pager
 * @package app\data\controller\base
 */
class Model extends AdminController
{

	// 初始化函数
    protected function initialize()
    {
        parent::initialize();
		$this->model = new CmsModel();
		$this->validate = new modelValidate();
    }


    /**
     * 模型页面管理
     * @auth true
	 * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */  
    public function index()
    {
		$this->title = '模型管理';

		if ($this->request->isGet()) {
			$get = $this->request->get();
			if($this->output == 'layui.table'){
				
				$cfg = ['list_rows' => $get['limit'], 'query' => $get];
				$data = $this->model->where([])->append(['attr_group'])->paginate($cfg)->toArray();
				return json(['msg' => '', 'code' => 0, 'count' => $data['total'], 'data' => $data['data']]);
			}
		}
		
		$this->fetch();
		
    }

 

    /**
     * 添加模型
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
		//获取所有模版
		$this->template_list = get_template();

        $this->fetch();
    }


 	/**
	 * 添加模型
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

            $this->model->startTrans();
			try {

				$this->model->data($post, true);
                $this->model->create($post);
				
				$table = 'cms_archives_'.$this->model->name;

				$db = new DataTable();	

				if ($db->CheckTable($table)) {
					throw new \think\Exception("已存在相同表名");
				}

				//创建新表
				if (!$db->initTable(strtolower($table), $this->model->title, 'id')->execute()) {
					throw new \think\Exception("创建表失败");
				}
				
				
				$adata = ['after'=>'id', 'name' => 'archives_id', 'title' => '文档ID','remark' => '文档ID',  'type' => 'number', 'length' => 11,  'is_must' => 1, 'is_show' => 0, 'value' => '0'];

				if (!$db->columField(strtolower($table), $adata)->execute()){
					throw new \think\Exception("添加文档ID字段失败");
				}
				
				$db->createIndex(strtolower($table), 'archives_id')->execute();
				
				$this->model->commit();
            } catch (\PDOException $e) {
				
                $this->model->rollback();
                $this->error($e->getMessage());
            } catch (\Throwable $th) {
				
                $this->model->rollback();
                $this->error($th->getMessage());
            }
			sysoplog('内容管理', "添加模型");
			$this->success("添加成功");
		}
	}


    /**
     * 编辑模型
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit($id)
    {
		//获取所有模版
		$this->template_list = get_template();

		$this->vo = $this->model->find($id);

        $this->fetch();
    }


    /**
     * 删除模型
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function remove($id)
    {
		$model = $this->model->findOrEmpty($id);
		if ($model->isEmpty()) {
			return $this->error("没有找到此模型");
		}
		
		$this->model->startTrans();
		try {

			attributeModel::where('model_id', $id)->delete();

			$table = 'cms_archives_'.$model->name;

			$db = new Datatable();
			if ($db->CheckTable($table)) {
				$db->delTable($table)->execute();
			}
			$model->delete();
			$this->model->commit();
		} catch (\PDOException $e) {
			$this->model->rollback();
			return $this->error($e->getMessage());
		} catch (\Throwable $th) {
			$this->model->rollback();
			return $this->error($th->getMessage());
		}
		sysoplog('内容管理', "删除模型");
		$this->success('删除成功');
      
    }
}
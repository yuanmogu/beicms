<?php

namespace app\admin\controller\cms;


use app\common\model\CmsArchives as archivesModel;
use app\common\validate\CmsArchives as archivesValidate;


use app\common\model\CmsCategory as categoryModel;

use app\common\model\CmsModel as modelModel;
use app\common\model\CmsAttribute as attributeModel;

use app\common\model\CmsSpecial as specialModel;

use libs\DataExtend;
use think\facade\Db;

use app\AdminController;
/**
 * 文档管理
 * Class ShopGoods
 * @package app\data\controller
 */
class Archives extends AdminController
{

	protected $categoryList;

    /**
     * 控制器初始化
     */
    protected function initialize()
    {
		parent::initialize();
		$this->model = new archivesModel();
		$this->validate = new archivesValidate();
		$specialList = specialModel::where('status', 1)->order('sort desc, id desc')->select()->toArray();	
		$this->specialList = DataExtend::arr2group($specialList,'type');

    }


    /**
     * 文档管理
     * @auth true
	 * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */  
    public function index()
    {
		$this->title = '文档管理';

		$this->category_id = $this->request->get('category_id/d',0);

		$catelist = categoryModel::where('status',1)->order('sort asc, id asc')->field('id, pid, type, folder, model_id, title, icon')->select()->toArray();
		foreach($catelist as $key => &$val){
			if($val['id'] == $this->category_id) $val['checked'] = true;
			$val['spread'] = true;

			$val['href'] = admuri('index',['category_id'=>$val['id'], 'spm'=>$this->request->param('spm')]);
			if($val['type'] == 'single') $val['href'] = admuri('cms.single/edit',['category_id'=>$val['id'], 'spm'=>$this->request->param('spm')]);
			if($val['type'] == 'link') $val['href'] = admuri('cms.category/edit',['id'=>$val['id'], 'spm'=>$this->request->param('spm')]);
			
			$type = '';
			if($val['type'] == 'link') $type = '<small class="color-red">【链接】</small>';
			if($val['type'] == 'single') $type = '<small class="color-blue">【单页】</small>';
			$val['title'] = '<i class="'.$val['icon'].'"></i> '.$val['title']. '<small class="color-red">'.$type.'</small>';

		}

		$cateTree = DataExtend::arr2tree($catelist,'id','pid','children');
		$this->cateTree = json_encode($cateTree);

		
		if ($this->request->isGet()) {

			$get = $this->request->get();

			if($this->output == 'layui.table'){
				
				$where = [];

				if(!empty($get['category_id'])){
					//包含所有子栏目
					$category_ids = DataExtend::getArrSubIds($catelist,$get['category_id']);
					$where[] = ['category_id','in',$category_ids];
					
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
				if(!empty($get['keywords'])){
					$where[] = ['keywords', 'like', '%'.$get['keywords'].'%'];
				}
				
				$cfg = ['list_rows' => $get['limit'], 'query' => $get];
				$data = $this->model->where($where)->order('sort desc,id desc')->paginate($cfg)->toArray();
				return json(['msg' => '', 'code' => 0, 'count' => $data['total'], 'data' => $data['data']]);
			}
		}


		$this->fetch();
      
    }

  
    /**
     * 添加内容
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add($category_id = 0)
    {

        $category = categoryModel::where('id', $category_id)->findOrEmpty();		
		if ($category->isEmpty()) {
			$this->error('请先选择一个栏目');
		}
		
		$modelInfo = modelModel::where('id', $category->model_id)->append(['attr_group'])->findOrEmpty();
		if ($modelInfo->isEmpty()) {
			$this->error('没有找到栏目模型');
		}

		$this->title = '添加'.$modelInfo->title;

		$this->fieldGroup = attributeModel::getFieldList($modelInfo->toArray());
		$this->category_list = categoryModel::getCategoryTable($modelInfo->id);

		$this->table = $modelInfo->name;
		$this->model_id = $modelInfo->id;
		$this->category_id = $category_id;
		$this->fetch();
    }

    /**
     * 添加内容
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function save() 
    {
        if ($this->request->isPost()) {
			
			$category_id = $this->request->post('category_id');
			if(empty($category_id)){
				return $this->error('栏目选择错误');
			}

			$category = categoryModel::where('id',$category_id)->findOrEmpty();

			if ($category->isEmpty()) {
				$this->error("没有找到此栏目信息");
			}

			$modelModel = modelModel::where('id',$category->model_id)->findOrEmpty();

			if ($modelModel->isEmpty()) {
				$this->error("没有找到此模型信息");
			}
			
			$post = $this->request->post();

			try {
				$result = $this->validate->check($post);
				if (true !== $result) abort(412, $this->validate->getError());
			} catch (ValidateException $e) {			
				return $this->error($e->getError());
			}
			
			$this->model->startTrans();

            try {

				$post['admin_id'] = $this->app->session->get('user.id', 0);

			   	$post['model_id'] = $modelModel->id;

                $this->model->save($post);

				$data = $post[$modelModel->name];


				$data = $this->model->setContent($modelModel->id,$data);

				$data['archives_id'] = $this->model->id;

				Db::name('cms_archives_'.$modelModel->name)->save($data);
		

				$this->model->commit();
            } catch (\PDOException $e) {
               $this->model->rollback();
               return $this->error($e->getMessage());
            } catch (\Throwable $th) {
				$this->model->rollback();
                return $this->error($th->getMessage());
            }
           
			sysoplog('内容管理', "添加内容");

            return $this->success("发布成功", admuri('admin/cms.archives/index',['category_id'=>$category_id]));
        }

		$this->error("发布失败");
    }



    /**
     * 编辑内容
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit($id = 0)
    {

		$this->info = $this->model->where('id', $id)->findOrEmpty();	
		
		if ($this->info->isEmpty()) {
			$this->error('没有找到此内容');
		}
		
		$this->model_info = modelModel::where('id', $this->info->model_id)->append(['attr_group'])->findOrEmpty();
		if ($this->model_info->isEmpty()) {
			$this->error('没有找到此模型');
		}

		$this->title = '编辑'.$this->model_info->title;

		$this->tableInfo = Db::name('cms_archives_'.$this->model_info->name)->where('archives_id',$id)->find();

		$this->fieldGroup = attributeModel::getFieldList($this->model_info->toArray());

		$this->category_list = categoryModel::getCategoryTable($this->model_info->id);

		$this->fetch();
    }


  
    /**
     * 编辑内容
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function update() 
    {
		
        if ($this->request->isPost()) {
			
			$id = $this->request->post('id/d');

			$content = $this->model->where('id',$id)->findOrEmpty();

			if ($content->isEmpty()) {
				return $this->error("没有找到此文档信息");
			}

			$modelModel = modelModel::where('id',$content->model_id)->findOrEmpty();

			if ($modelModel->isEmpty()) {
				$this->error("没有找到此模型信息");
			}

			$post = $this->request->post();

			try {
				$result = $this->validate->check($post);
				if (true !== $result) abort(412, $this->validate->getError());
			} catch (ValidateException $e) {			
				return $this->error($e->getError());
			}
			
			$this->model->startTrans();

            try {
				
                $content->save($post);

				$data = $post[$modelModel->name];

				//过滤字段
				$data = $this->model->setContent($modelModel->id,$data);

				Db::name('cms_archives_'.$modelModel->name)->where('archives_id',$content->id)->save($data);
			
				$this->model->commit();
            } catch (\PDOException $e) {
               $this->model->rollback();
               return $this->error($e->getMessage());
            } catch (\Throwable $th) {
				$this->model->rollback();
                return $this->error($th->getMessage());
            }
			
			sysoplog('内容管理', "编辑内容");
            return $this->success("保存成功",admuri('admin/cms.archives/index',['category_id'=>$content->category_id]));
        }

		$this->error("保存失败");
    }

    /**
     * 删除内容
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
			foreach($id as $arc){

				$content = $this->model->findOrEmpty($arc);

				Db::name($content->table_name)->where('archives_id',$arc)->delete();

				$content->delete();
			
			}

			$this->model->commit();
		} catch (\PDOException $e) {
			$this->model->rollback();
			return $this->error($e->getMessage());
		} catch (\Throwable $th) {
			$this->model->rollback();
			return $this->error($th->getMessage());
		}

		sysoplog('内容管理', "删除内容");

		$this->success('删除成功');
    }



}
<?php
/**
 * +----------------------------------------------------------------------
 * | 通用内容控制器
 * +----------------------------------------------------------------------
 */
namespace app\index\controller;

use app\HomeController;

use app\common\model\CmsArchives as archivesModel;
use app\common\model\CmsCategory as categoryModel;
use app\common\model\CmsAttribute;
use app\common\model\CmsModel as modelModel;

use think\facade\View;

class Category extends HomeController
{

    // 列表
    public function index()
    {

		// 当前模型ID
		$category = categoryModel::where('folder', '=', $this->request->param('folder'))->findOrEmpty();			
  
		if ($category->isEmpty()) {
            $this->error('未找到对应栏目');
        }
	

        $data = [
            'cate'			=> $category,	// 栏目信息            
            'title'			=> $category->title.' - '. $this->site['title'],
            'keywords'		=> $category->keywords.','. $this->site['keywords'],
            'description'	=> $category->description . $this->site['description'],
        ];

		
		if(!empty($category->template_list)){
			$template = $category->template_list;			
		}elseif(!empty($category->model->template_list)){
			$template = $category->model->template_list;			
		}else{
			$template = 'default';
		}
		$template = str_replace(".html","",$template);
			
        View::assign($data);
        return View::fetch($template);
    }





}

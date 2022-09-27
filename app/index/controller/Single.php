<?php
/**
 * +----------------------------------------------------------------------
 * | 单页控制器
 * +----------------------------------------------------------------------
 */
namespace app\index\controller;

use think\facade\Request;
use think\facade\View;

use app\common\model\CmsCategory as categoryModel;
use app\common\model\CmsSingle as singleModel;

use app\HomeController;

class Single extends HomeController
{
    // 首页
    public function index()
    {
	
		$cate = categoryModel::where('folder', '=', Request::param('folder'))->findOrEmpty();	
		
		if ($cate->isEmpty()) {
            $this->error('未找到页面');
        }
		
		$page = singleModel::where('category_id', '=', $cate->id)->findOrEmpty();	


		$info = array_merge($cate->toArray(), $page->toArray());

        $data = [			
            'info'			=> $info,				// 栏目信息
            'title'			=> $cate->title.' - '. $this->site['title'],
            'keywords'		=> $cate->keywords.', '. $this->site['keywords'],
            'description'	=> $cate->description . $this->site['description'],
        ];


		if(!empty($cate->template_show)){
			$template = str_replace(".html","",$cate->template_show);
		}else{
			$template = 'default';
		}
		
        View::assign($data);
        return View::fetch($template);
    }

  

}

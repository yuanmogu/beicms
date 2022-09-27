<?php
/**
 * +----------------------------------------------------------------------
 * | 专题页
 * +----------------------------------------------------------------------
 */
namespace app\index\controller;

use think\facade\Request;
use think\facade\View;

use app\common\model\CmsSpecial as specialModel;

use app\HomeController;

class Special extends HomeController
{

	
    // 首页
    public function index()
    {
		$data = [			
            'title'			=> '专题 - '. $this->site['title'],
            'keywords'		=> $this->site['keywords'],
            'description'	=> $this->site['description'],
        ];
		View::assign($data);
        return View::fetch();
		
    }



    // 专题列表
    public function show()
    {
	
		$special = specialModel::where('folder', '=', Request::param('folder'))->findOrEmpty();	
		
		if ($special->isEmpty()) {
            $this->error('未找到专题');
        }

        $data = [			
            'info'			=> $special,				// 栏目信息
            'title'			=> $special->title.' - '. $this->site['title'],
            'keywords'		=> $special->keywords.','. $this->site['keywords'],
            'description'	=> $special->description . $this->site['description'],
        ];


		if(!empty($page->template)){
			$template = str_replace(".html","",$page->template);
		}else{
			$template = 'default';
		}
		
        View::assign($data);
        return View::fetch($template);
    }

  

}

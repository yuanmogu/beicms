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

class Archives extends HomeController
{


    // 详情
    public function index(int $id){
      

		// 当前模型ID
		$this->category = categoryModel::where('folder', '=', $this->request->param('folder'))->findOrEmpty();			

		if ($this->category->isEmpty()) {
            $this->error('没有找到相关栏目');
        }

	  	//文档
		$archives = archivesModel::where('id', $id)->findOrEmpty();
		if ($archives->isEmpty()) {
			$this->error('没有找到此内容');
		}
		if ($archives->status !== 1) {
			$this->error($archives->status_text);
		}

		$archives->view	+= 1;

		$archives->isAutoWriteTimestamp(false)->save();

		//附加内容
        $add_info = $this->app->db->name($archives->table_name)->withoutField('id')->where('archives_id', $id)->find();

		$info = array_merge($archives->toArray(),$add_info);
		
		$info['tags'] = explode(",", $info['keywords']);		


        $data = [	
			'info'			=> $info,			// 详情信息
            'cate'			=> $this->category,	// 栏目信息    
            'title'			=> $info['title'] .' - '. $this->category->title.' - '. $this->site['title'],
            'keywords'		=> $info['keywords'] .','. $this->category->keywords.','. $this->site['keywords'],
            'description'	=> $info['description'] . $this->category->description . $this->site['description'],
        ];

		
		if(isset($info["template"]) && !empty($info["template"])){
			$template = $info["template"];			
		}else{
			if(!empty($this->category->template_show)){
				$template = $this->category->template_show;			
			}elseif(!empty($archives->model->template_show)){
				$template = $archives->model->template_show;			
			}else{
				$template = 'default';
			}			
		}

		$template = str_replace(".html","",$template);
		
        View::assign($data);

        return View::fetch($template);
	}


    // 点赞
    public function likes(int $id){

	  	//文档
		$archives = archivesModel::where('id', $id)->findOrEmpty();

		if ($archives->isEmpty()) {
			$this->error('没有找到此内容');
		}
		if ($archives->status !== 1) {
			$this->error($archives->status_text);
		}

		$archives->likes += 1;

		$archives->save();

		$this->success('点赞成功');


	}

}

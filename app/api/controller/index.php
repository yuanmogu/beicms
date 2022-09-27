<?php
declare (strict_types=1);

namespace app\api\controller;

use app\ApiController;

/**
 * 前端API接口
 */
class Index extends ApiController
{

	public function index()
	{
		return $this->success("ok");
	}

    // 获取首页内容
    public function home()
    {
		$site = sysconf('site.');
		$contact = sysconf('contact.');
		$slider = \app\common\model\SiteSlider::getTaglib('',6);
		$links = \app\common\model\SiteLinks::getTaglib('',6);

        return $this->result(['site'=>$site, 'contact'=>$contact, 'slider'=>$slider,'links'=>$links]);
    }

    // 获取站点配置
    public function config()
    {
		$name = $this->request->get('name','site.');
		$data = sysconf($name);
        return $this->result($data);
    }

    // 获取幻灯图片
    public function slider()
    {
		$type = $this->request->get('type','');
		$limit = $this->request->get('limit', 6);

		$data = \app\common\model\SiteSlider::getTaglib($type,$limit);
        $this->result($data);
    }

    // 获取留言消息
    public function message()
    {
		$page = $this->request->get('page', 0);
		$limit = $this->request->get('limit', 6);

		$data = \app\common\model\SiteMessage::getTaglib($page,$limit);
        return $this->result($data);
    }

    // 获取站点链接
    public function links()
    {
		$type = $this->request->get('type','');
		$limit = $this->request->get('limit', 6);

		$data = \app\common\model\SiteLinks::getTaglib($type,$limit);
        return $this->result($data);
    }


    // 获取单页列表
    public function singlelist()
    {
		$get = $this->request->get();
        $data = \app\common\model\CmsSingle::getTaglib($get);
		return $this->result($data);
    }

    // 获取单页内容
    public function single()
    {
		$folder = $this->request->get('folder','');
		if(empty($folder)) return $this->error('请求数据失败');
        $data = \app\common\model\CmsSingle::where('status',1)->where('folder', $folder)->findOrEmpty();
		if ($data->isEmpty()) return $this->error('未找到相关内容');
		return $this->result($data);
    }


    // 获取栏目分类
    public function category()
    {
		$get = $this->request->get();
		$data = \app\common\model\CmsCategory::getTaglib($get);
		return $this->result($data);
    }

    // 获取文章列表
    public function catelist()
    {
		$get = $this->request->get();
		$data = \app\common\model\CmsArchives::getTaglib($get);
		return $this->result($data);
    }

    // 获取文章内容
    public function archives()
    {
		$id = $this->request->get('id');

	  	//文档
		$archives = \app\common\model\CmsArchives::where('id', $id)->findOrEmpty();
		if ($archives->isEmpty()) return $this->error('没有找到此内容');
		if ($archives->status !== 1) return $this->error($archives->status_text);

		$archives->view	+= 1;
		$archives->isAutoWriteTimestamp(false)->save();

		//附加内容
        $add_info = $this->app->db->name($archives->table_name)->withoutField('id')->where('archives_id', $id)->find();
		$data = array_merge($archives->toArray(),$add_info);

		return  $this->result($data);
    }


}

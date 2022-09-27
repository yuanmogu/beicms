<?php
namespace app\admin\controller\site;

use app\AdminController;

/**
 * 模板参数配置
 * Class Config
 * @package app\data\controller\base
 */
class Template extends AdminController
{

    /**
     * 模板管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $this->skey = 'template';
        $this->title = '模板管理';

		$this->list = get_template_all();

		$this->data['pc'] = sysconf('template.pc');
		$this->data['mobile'] = sysconf('template.mobile');

		$this->fetch();

    }

	/**
	 * 设置主题
	 * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
	 */
	public function set($name, $id) {

		sysconf('template.'.$name, $id.'');

		sysoplog('站点管理', "变更网站模板");

		$this->success('修改成功！');

	}



    /**
     * 模板配置
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function config()
    {

		$this->name = $this->request->param('name');

        if (empty($this->name)) {
            $this->error(lang('没有获取到要修改的模板'));
        }
      
        $this->data = get_theme_config($this->name, true); // 获取整个配置文件

		
		if ($this->request->isPost()) {
            $post = $this->request->post();

            if (empty($post)) {
                $this->error(lang('提交的数据有误'));
            }

            foreach ($this->data as $key=>&$value) {
				
                if (!empty($value['item'])) { // 分组写法
					
                    if (isset($post[$key])) {
                        foreach ($value['item'] as $k=>&$v) {
							
                            if (isset($post[$key][$v['name']])) {
                                if (is_array($post[$key][$v['name']])) {
                                    $v['value'] = implode(',', $post[$key][$k]);
                                } else {
									$v['value'] = $post[$key][$v['name']];
                                }
                            }
                        }
                    }
                } else {
                    if (isset($post[$key])) {
                        if (is_array($post[$key])) {
                            $value['value'] = implode(',', $post[$key]);
                        } else {
                            $value['value'] = $post[$key];
                        }
                    }
                }
            }
		

			write_theme_config($this->name, $this->data);

            $this->app->cache->tag('get_theme_config')->clear();
			

			sysoplog('站点管理', "修改网站模板参数");

            $this->success("保存成功");
        }


		$this->fetch();
    }
}
<?php

namespace app\admin\controller\site;

use app\common\model\SiteAddons as addonsModel;
use think\Addons\Manager as addonsManager;


use app\admin\model\SystemMenu as menuModel;

use app\AdminController;

/**
 * 插件管理
 * Class Addons
 * @package app\data\controller
 */
class Addons extends AdminController
{

	// 初始化函数
    protected function initialize()
    {
        parent::initialize();
        $this->model = new addonsModel();
    }

    /**
     * 插件管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
		$this->title = '插件管理';

		$addons_all = get_addons_info_all();

		$addons_list = $this->model->order('sort desc, id asc')->column('*','name');

		$list = $addons_list + $addons_all;

		foreach($list as $key => $val){
			//数据库中有，但是实际没有。
			if(!array_key_exists($val['name'], $addons_all)) unset($list[$key]);
		}


		$this->list = $list;
		$this->fetch();
		
    }

    /**
     * 插件配置
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function config()
    {

		$this->name = $this->request->param('name');
	
		//重新赋值
	  	$addon_info = $this->model->where('name',$this->name)->findOrEmpty();

		if ($this->request->isPost()) {
            $post = $this->request->post();

            if (empty($post)) {
                $this->error(lang('提交的数据有误'));
            }
	
			$addon_info->config = $post;

			$addon_info->save();

			sysoplog('插件管理', "修改插件配置");

            return $this->success("保存成功");
        }


		//获取插件初始配置
		$addon = get_addons_instance($this->name);
		if (!$addon) {
			$this->error(lang('没有获取到要修改的插件'));
		}
		$this->data = $addon->getConfig(true);


		$this->fetch();
    }



    /**
     * 插件安装
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function install()
    {
        if ($this->request->isAjax()) {
            $name = $this->request->param('name', '', 'trim');
            if (empty($name)) {
                $this->error('请选择需要安装的插件！');
            }

			$this->model->startTrans();

            try {
                addonsManager::install($name);
				
				//获取插件初始配置
				$addon = get_addons_instance($name);
				$info = $addon->getInfo();
				$config = $addon->getConfig();
				if(!empty($config)) $info['config'] = $config;

				$this->model->save($info);

				$this->model->commit();
            } catch (\Exception $e) {
				$this->model->rollback();
                $this->error($e->getMessage());
            }

			sysoplog('插件管理', "安装插件");
			return $this->success('插件安装成功！');
        }
    }

    /**
     * 插件卸载
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function uninstall()
    {
        $name = $this->request->param('name');
        if (empty($name)) {
            $this->error('请选择需要安装的插件！');
        }
        if ( !preg_match('/^[a-zA-Z0-9]+$/', $name)) {
            $this->error('插件标识错误！');
        }
        try {
            addonsManager::uninstall($name, true);
			$this->model->where('name',$name)->delete();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
		sysoplog('插件管理', "卸载插件");
        return $this->success('插件卸载成功！清除浏览器缓存和框架缓存后生效！');
    }







    /**
     * 排序
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setsort()
    {
		if ($this->request->isAjax()) {

			$name = $this->request->post('name');	
			$value = $this->request->post('value');

			$this->model->startTrans();
			try {
				
				$this->model->where('name',$name)->update(['sort'=>$value]);
				menuModel::where('pid',21)->where('node','addons.'.$name)->update(['sort'=>$value]);

                $this->model->commit();
            } catch (\PDOException $e) {
                $this->model->rollback();
                return $this->error($e->getMessage());
            } catch (\Throwable $th) {
                $this->model->rollback();
                return $this->error($th->getMessage());
            }
            
            return $this->success('修改成功');	
		}

		return $this->error('修改失败');
    }



    /**
     * 删除插件
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function remove($id)
    {
		if(is_array($id)){
			$id = arr2str($id);
		}
		sysoplog('站点管理', "删除插件");
        if ($this->model->whereIn('id',$id)->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

}
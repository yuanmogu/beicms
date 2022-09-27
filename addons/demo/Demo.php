<?php

namespace addons\demo;
use think\facade\Db;
use think\Addons;

class Demo extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

	/**
     * 钩子，名称后必须加 Hook
     * @return bool
     */
    public function demoHook($param)
    {
		
        dump($param);
		dump($this->getConfig());

		$list = Db::name('addons_demo')->where('status',1)->select()->toArray();
		dump($list);
		
		$this->assign('list', $list);
		return $this->fetch('index');
    }

}

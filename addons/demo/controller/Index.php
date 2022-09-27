<?php
namespace addons\demo\controller;

use think\Addons\HomeController;

class Index extends HomeController
{
    public function index()
    {
		
		$info  = $this->getInfo();
		dump($info);

		
		$config  = $this->getConfig();
		dump($config);


		$this->assign('info',$info);

		return $this->fetch('index');

    }
}l
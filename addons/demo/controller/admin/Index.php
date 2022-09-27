<?php
namespace addons\demo\controller\admin;

use think\Addons\AdminController;

class Index extends AdminController
{
    public function index()
    {

		$config = $this->getConfig();

        $title = 'hello admin world';
		
		$this->assign('title', $title);
		return $this->fetch();
    }
}
<?php
declare (strict_types = 1);

namespace app\index\controller;

use think\facade\Request;
use think\facade\Db;
use think\facade\View;

use app\common\model\CmsSingle;
use app\admin\library\storage\LocalStorage;

use app\HomeController;

class Index extends HomeController 
{
    public function index()
    {	
		return View::fetch();
    }
}

<?php
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
namespace form\factory;

use think\facade\View;

/**
 * @title 后台中间件
 */
class Text extends \form\Factory {

	public function show(){
		return $this->display('text');
	}

}
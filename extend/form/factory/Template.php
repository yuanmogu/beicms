<?php
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
namespace form\factory;

use think\facade\View;

/**
 * @title 后台中间件
 */
class Template extends \form\Factory {

	public function show(){		
		return $this->display('template');
	}

	protected function parseValue(){
		$this->field['template'] = get_template();
		$this->field['value'] = isset($this->data[$this->field['name']]) ? $this->data[$this->field['name']] : (isset($this->field['value']) ? $this->field['value'] : '');
	}
}
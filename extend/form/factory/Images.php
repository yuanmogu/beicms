<?php
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
namespace form\factory;

use think\facade\View;

/**
 * @title 后台中间件
 */
class Images extends \form\Factory {

	public function show(){
		return $this->display('images');
	}

	protected function parseValue(){
		$value = isset($this->data[$this->field['name']]) ? $this->data[$this->field['name']] : (isset($this->field['value']) ? $this->field['value'] : '');
		if(is_array($value)){
			$this->field['value'] = implode("|", $value);
		}else{
			$this->field['value'] = $value;
		}
		
	}

}
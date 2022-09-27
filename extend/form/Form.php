<?php
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
namespace form;


/**
 * @title 后台中间件
 */
class Form {

	public static function render($field, $data, $prekey){
		if (in_array($field['type'], ['string', 'text'])) {
			$field['type'] = 'text';
		}

		$class = "\\form\\factory\\" . ucfirst($field['type']);

		if (class_exists($class)) {
			$elem = new $class($field, $data, $prekey);
		}else{
			$elem = new Factory($field, $data, $prekey);
		}
		
		return $elem->show();
	}
}
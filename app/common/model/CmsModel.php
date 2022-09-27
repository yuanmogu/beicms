<?php

namespace app\common\model;

use exts\Datatable;
use think\Model;

/**
 * 模型
 * Class ShopGoodsCate
 * @package app\data\model
 */
class CmsModel extends Model
{

	//protected $append = ['attr_group','grid_list'];

	public function setNameAttr($value) {
		return strtolower($value);
	}	
	
	public function getAttrGroupAttr($value, $data) {
	
		return explode("|", $data['attribute_group']);
		
	}

	public function getGridListAttr($value, $data) {
		$list = [];
		if ($data['list_grid'] !== '') {
			$row = explode(PHP_EOL, $data['list_grid']);
			foreach ($row as $r) {
				list($field, $title) = explode(":", $r);
				$list[$field] = ['field' => $field, 'title' => $title];
				if (strrpos($title, "|")) {
					$title = explode("|", $title);
					$list[$field] = ['field' => $field, 'title' => $title[0], 'format' => trim($title[1])];
				}
			}
		}
		return $list;
	}



}
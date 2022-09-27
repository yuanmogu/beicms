<?php

namespace app\common\model;

use exts\ip\Ip2Region;

use think\Model;

/**
 * 用户消息模型
 * Class BaseUserMessage
 * @package app\data\model
 */
class SiteMessage extends Model
{
	//protected $append = ['geoisp'];

    public function getGeoispAttr($value,$data)
    {
		$region = new Ip2Region();
		$isp = $region->btreeSearch($data['ip']);
		return str_replace(['内网IP', '0', '|'], '', $isp['region'] ?? '') ?: '-';
	}

    public function setReplyAttr($value, $data)
    {
		if(!empty($value) && empty($data['delete_time'])) $this->set('status', 2);
    }





	/**
     * 获取列表
     * @param $tag
     * @return array|false|\PDOStatement|string|\think\Collection
	*/
    public static function getTaglib(int $page = 0, int $limit = 10)
    {

		$where = [];

		$where[] = ['is_show','=',1];

		$cache = cache("siteMessage".$page.$limit);

		if(!empty($cache)) return $cache;

		$items = static::where($where)->order('create_time desc, id desc')->limit($page, $limit)->select()->toArray();

		cache("siteMessage".$page.$limit, $items, 7200);

		return $items;

	}
}
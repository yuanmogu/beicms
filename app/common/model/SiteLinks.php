<?php
namespace app\common\model;

use think\Model;


/**
 * 网站链接模型
 * Class DataUser
 * @package app\admin\model
 */
class SiteLinks extends Model
{

    /**
     * 获取所有链接类型
     * @param boolean $simple 加载默认值
     * @return array
     */
    public static function types(bool $simple = false): array
    {
        $types = static::where([])->distinct(true)->column('type');
        if (empty($types) && empty($simple)) $types = ['通用链接'];
        return $types;
    }




	/**
     * 标签调用
     * @param strong $type 类型
	 * @param int $limit 条数
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getTaglib(string $type = '', int $limit = 6): array
    {
		$where = [];

		$where[] = ['status','=',1];

		if(!empty($type)) $where[] = ['type','=',$type];
		
		$cache = cache("siteLinks".$type.$limit);

		if(!empty($cache)) return $cache;

        $items = static::where($where)->order('sort desc, id desc')->limit($limit)->select()->toArray();
		
		cache("siteLinks".$type.$limit, $items, 7200);

        return $items;

    }
}
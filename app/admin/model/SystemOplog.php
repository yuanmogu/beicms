<?php
declare (strict_types=1);

namespace app\admin\model;

use exts\ip\Ip2Region;

use think\Model;

/**
 * 系统日志模型
 * Class SystemOplog
 * @package think\admin\model
 */
class SystemOplog extends Model
{

	protected $append = ['geoisp'];


    public function getGeoispAttr($value,$data)
    {
		$region = new Ip2Region();
		$isp = $region->btreeSearch($data['geoip']);
		return str_replace(['内网IP', '0', '|'], '', $isp['region'] ?? '') ?: '-';
	}


    /**
     * 格式化创建时间
     * @param string $value
     * @return string
     */
    public function getCreateAtAttr(string $value): string
    {
        return format_datetime($value);
    }
}
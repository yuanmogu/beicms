<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2022 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: https://gitee.com/zoujingli/ThinkLibrary
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/ThinkLibrary
// | github 代码仓库：https://github.com/zoujingli/ThinkLibrary
// +----------------------------------------------------------------------

declare (strict_types=1);

namespace libs;

/**
 * 数据处理扩展
 * Class DataExtend
 * @package think\admin\extend
 */
class DataExtend
{

	/**
	 * 获得所有的子
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public static function getChilds($data, $id = 0, $pk = 'id', $pid = 'pid') {

		$array = [];
		foreach ($data as $k => $v) {
			if ($v[$pid] == $id) {
				$array[] = $v[$pk];
				array_merge($array, static::getChilds($data, $v[$pk]));
			}
		}
		return $array;
	}

	/**
	 * 获取id的所有父，包含自己
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public static function getParents($data, $id = 0, $pk = 'id', $pid = 'pid') {
		static $ids = [];
		foreach ($data as $k => $v) {
			if ($v[$pk] == $id) {
				array_unshift($ids, $id);
				if ($v[$pid] == 0) {
					break;
				}
				static::getParents($data, $v[$pid]);
			}
		}
		return $ids;
	}


	/**
	 * 传递一个子分类ID返回所有的父级分类[前台栏目]
	 * @param $cate
	 * @param $id
	 * @return array
	 */
	public static function getParentsTree($data, $id = 0, $pk = 'id', $pid = 'pid')
	{
		$arr = array();
		foreach ($data as $v) {
			if ($v[$pk] == $id) {
				$arr[] = $v;
				$arr = array_merge(static::getParentsTree($data, $v[$pid]), $arr);
			}
		}
		return $arr;
	}



    /**
     * 按数组中某列分组
     * @param array $its 待处理数据
     * @param string $group 分组列
     * @return array
     */
    public static function arr2group(array $its, string $group = 'id'): array
    {
		$arr = [];
        foreach ($its as $it) {
			if (isset($it[$group])) $arr[$it[$group]][] = $it;
		}
        return $arr;
    }


    /**
     * 一维数组生成数据树
     * @param array $its 待处理数据
     * @param string $cid 自己的主键
     * @param string $pid 上级的主键
     * @param string $sub 子数组名称
     * @return array
     */
    public static function arr2tree(array $its, string $cid = 'id', string $pid = 'pid', string $sub = 'sub'): array
    {
        [$tree, $its] = [[], array_column($its, null, $cid)];
        foreach ($its as $it) isset($its[$it[$pid]]) ? $its[$it[$pid]][$sub][] = &$its[$it[$cid]] : $tree[] = &$its[$it[$cid]];
        return $tree;
    }

    /**
     * 一维数组生成数据树
     * @param array $its 待处理数据
     * @param string $cid 自己的主键
     * @param string $pid 上级的主键
     * @param string $path 当前 PATH
     * @return array
     */
    public static function arr2table(array $its, string $cid = 'id', string $pid = 'pid', string $path = 'path'): array
    {
        $call = function (array $its, callable $call, array &$data = [], string $parent = '') use ($cid, $pid, $path) {
            foreach ($its as $it) {
                $ts = $it['sub'] ?? [];
                unset($it['sub']);
                $it[$path] = "{$parent}-{$it[$cid]}";
                $it['spc'] = count($ts);
                $it['spt'] = substr_count($parent, '-');
                $it['spl'] = str_repeat('ㅤ├ㅤ', $it['spt']);
                $it['sps'] = ",{$it[$cid]},";
                array_walk_recursive($ts, function ($val, $key) use ($cid, &$it) {
                    if ($key === $cid) $it['sps'] .= "{$val},";
                });
                $it['spp'] = arr2str(str2arr(strtr($parent . $it['sps'], '-', ',')));
                $data[] = $it;
                if (empty($ts)) continue;
                $call($ts, $call, $data, $it[$path]);
            }
            return $data;
        };
        return $call(static::arr2tree($its, $cid, $pid), $call);
    }

    /**
     * 获取数据树子ID集合
     * @param array $list 数据列表
     * @param mixed $value 起始有效ID值
     * @param string $ckey 当前主键ID名称
     * @param string $pkey 上级主键ID名称
     * @return array
     */
    public static function getArrSubIds(array $list, $value = 0, string $ckey = 'id', string $pkey = 'pid'): array
    {
        $ids = [intval($value)];
        foreach ($list as $vo) if (intval($vo[$pkey]) > 0 && intval($vo[$pkey]) === intval($value)) {
            $ids = array_merge($ids, static::getArrSubIds($list, intval($vo[$ckey]), $ckey, $pkey));
        }
        return $ids;
    }
}
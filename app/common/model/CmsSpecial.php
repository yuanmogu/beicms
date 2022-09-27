<?php
namespace app\common\model;

use think\Model;


/**
 * 网站链接模型
 * Class DataUser
 * @package app\admin\model
 */
class CmsSpecial extends Model
{



	public function getUrlAttr($value,$data)
    {
		return url('special',['folder'=>$data['folder']])->build();
    }


    /**
     * 获取所有链接类型
     * @param boolean $simple 加载默认值
     * @return array
     */
    public static function types(bool $simple = false): array
    {
        $types = static::where([])->distinct(true)->column('type');
        if (empty($types) && empty($simple)) $types = ['通用专题'];
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
    public static function getTaglib($tag)
    {

		$type	= empty($tag['type']) ? '' : $tag['type'];
		
		$order	= empty($tag['order']) ? 'sort desc, create_time desc' : $tag['order'];
		$limit	= empty($tag['limit']) ? 10 : (int)$tag['limit'];

		$pagesize = empty($tag['pagesize']) ? 0 : (int)$tag['pagesize'];

		$where = [];

		$where[] = ['status','=',1];

		if(!empty($type)) $where[] = ['type','=',$type];

		$special = static::where($where)->order($order)->append(['url']);

		if($pagesize > 0){
			$param = request()->param();
			foreach($param as $key => $val){
				if(empty($val) || $key == "folder"){
					unset($param[$key]);
				}
			}
			
			$list = $special->paginate([
				'query'     => $param,
				'list_rows' => $pagesize,
			]);

		}else{

			$list = $special->limit($limit)->select();			
			
		}	


        return $list;

    }
}
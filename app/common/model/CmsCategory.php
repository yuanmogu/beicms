<?php

namespace app\common\model;

use libs\DataExtend;
use think\Model;

/**
 * 分类模型
 * Class ShopGoodsCate
 * @package app\data\model
 */
class CmsCategory extends Model
{


	//protected $append = ['model_title','table_name','url'];

	public $types = ['archives'=>'文档','single'=>'单页','link'=>'跳转'];


	public function getModelTitleAttr($value,$data)
    {
		$model_title = ' - ';
		if($data['model_id'] > 0) $model_title = isset($this->model->title) ? $this->model->title : '';
        return $model_title;
    }

	public function getTypeTextAttr($value,$data)
    {
		$type_text = ' - ';
		if(isset($this->types[$data['type']])) $type_text = $this->types[$data['type']];
        return $type_text;
    }


    public function getUrlAttr($value,$data)
    {
		$url = url('category',['folder'=>$data['folder']])->build();
		if($data['type'] == 'single') $url = url('single',['folder'=>$data['folder']])->build();
		if($data['type'] == 'link') $url = $data['link'];
		return $url;
    }



	//获取上级栏目地址
    public function getParentUrlAttr($value,$data)
    {
		$url = $this->url;
		if($data['pid'] > 0){
			$parent = self::where('id',$data['pid'])->find();

			$url = url('category',['folder'=>$parent['folder']])->build();
			if($parent['type'] == 'single') $url = url('single',['folder'=>$parent['folder']])->build();
			if($parent['type'] == 'link') $url = $parent['link'];
		}
		return $url;
    }


	//获取附加表名
    public function getTableNameAttr($value,$data)
    {
		$tableName = $this->model->getAttr('name');

        return "cms_archives_".$tableName;
    }


	/**
     * 获取上级可用选项
     * @param int $max 最大级别
     * @param array $data 表单数据
     * @param array $parent 上级分类
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getParentData(int $max, array &$data, array $parent = []): array
    {
        $items = static::where([])->order('sort desc,id asc')->select()->toArray();
        $cates = DataExtend::arr2table(empty($parent) ? $items : array_merge([$parent], $items));
        if (isset($data['id'])) foreach ($cates as $cate) if ($cate['id'] === $data['id']) $data = $cate;
        foreach ($cates as $key => $cate) {
            $isSelf = isset($data['spt']) && isset($data['spc']) && $data['spt'] <= $cate['spt'] && $data['spc'] > 0;
            if ($cate['spt'] >= $max || $isSelf) unset($cates[$key]);
        }
        return $cates;
    }

	//获取栏目分类，可指定模型。
	public static function getCategoryTable($model_id=0)
	{
		$categoryList = self::where('status', 1)->field('id, pid, title, model_id')->select()->toArray();		

		$list = DataExtend::arr2table($categoryList);

		if($model_id > 0){

			$delkey = [];
			//去除不相关栏目
			foreach($list as $key => $val){
				if($val['model_id'] != $model_id){ 
					if($val['spc'] == 0) {
						$delkey[] = $key;
					}else{
						$scat = array_filter(explode(',', $val['sps']));
						foreach($list as $k => $v){
							if ( in_array($v['id'],$scat) && $v['model_id'] == $model_id)  continue 2;
						}
						$delkey[] = $key;
					}
				}
			}
		
			if(!empty($delkey)){
				foreach($delkey as $val){
					unset($list[$val]);
				}
			}
		}

	
		return $list;
	}



	public function model()
    {
        return $this->belongsTo(CmsModel::class,'model_id');
    }




	//获取面包屑导航
	public static function getBreadcrumb(string $symbol='',string $class='breadcrumb-item')
	{
		$str = '<li  class="'.$class.'"><a href="/"><i class="bi bi-house"></i> 首页</a></li>';

		$cid = 0;
		$arr = [];
		$tempArr = [];

		$cid = get_cateid();
		if (empty($cid)) return $str;

		$arr = self::where(['id'=>$cid,'status'=>1])->append(['url'])->find();
		if (empty($arr)) return $str;

		$tempArr[] = $arr->toArray();
		while (true) { // 循环获取上级
			$arr = self::where(['id'=>$arr['pid'],'status'=>1])->append(['url'])->find();
			if (empty($arr)) break;
			$tempArr[] = $arr->toArray();
		}


		if (empty($tempArr)) return $str;

       
        for ($i = count($tempArr)-1; $i >= 0; $i--)
        {
            if ($cid != $tempArr[$i]['id']) {
                $str .= $symbol.'<li  class="'.$class.'"><a href="'.$tempArr[$i]['url'].'" >'.$tempArr[$i]['title'].'</a></li>';
            } else {
                $str .= $symbol.'<li  class="'.$class.' active">'.$tempArr[$i]['title'].'</li>';
            }
        }


        return '<ol class="breadcrumb">'.$str.'</ol>';
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
    public static function getTaglib($tag): array
    {
		$type	= empty($tag['type']) ? '' : $tag['type'];

		$cid	= empty($tag['cid']) ? 0 : $tag['cid'];
		$mid	= empty($tag['mid']) ? 0 : $tag['mid'];
		
		$order	= empty($tag['order']) ? 'sort asc, id asc' : $tag['order'];
		$limit	= empty($tag['limit']) ? 100 : (int)$tag['limit'];

		$current = !empty($tag['current']) ? $tag['current'] : ' active ';

		$where = [];

		$where[] = ['status','=',1];


		if($type=='top'){
			$where[] = ['pid','=', 0];	
		}	

		if ($cid > 0) {
			if($type=='peer'){
				$pid = self::where('id',$cid)->value('pid');
				$where[] = ['pid','=', $pid];	
			}
			if($type=='son'){
				$allcate = self::where('status',1)->field('id,pid')->select()->toArray();
				$ids = \libs\DataExtend::getChilds($allcate,$cid);
				$where[] = ['id','in', $ids];
			}
			if(empty($type)){
				$allcate = self::where('status',1)->field('id,pid')->select()->toArray();
				$ids = \libs\DataExtend::getArrSubIds($allcate,$id);
				$where[] = ['id','in', $ids];
			}
		}		

		if ($mid > 0) {
			$where[] = ['model_id','=',$mid];
		}


        $items = static::where($where)->order($order)->limit($limit)->append(['url'])->select()->toArray();

		//取得当前页面ID的所有父ID
		$cid = get_cateid();

		if($cid > 0){
			$pids = DataExtend::getParents($items, $cid);
			foreach($items as &$val) $val['current'] = in_array($val['id'], $pids) ? $current : "";
		}
		
		$list = DataExtend::arr2tree($items);

        return $list;

    }



}
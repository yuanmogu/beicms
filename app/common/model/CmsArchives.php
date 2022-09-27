<?php
// +----------------------------------------------------------------------
// | IECMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
namespace app\common\model;

use think\Model;
use think\facade\Db;

use app\site\model\SiteMember;

use app\admin\library\Storage;

/**
 * 文档模型
*/
class CmsArchives extends Model
{

	protected $append = ['cate_name','status_text','table_name'];

    public function getFlagAttr($value,$data)
    {
        return explode(',', $data['flag']);
    }

	public function setFlagAttr($value,$data)
    {
		return implode(',', $data['flag']);
    }
	
	//获取状态描述
	public function getStatusTextAttr($value, $data)
    {
		$status = [0=>'未审核',1=>'正常',2=>'待审核'];
        return $status[$data['status']];
    }

	//获取栏目标题
	public function getCateNameAttr($value, $data)
    {
		return $this->category->title;
    }
	//获取栏目链接
	public function getCateUrlAttr($value, $data)
    {
		return $this->category->url;
    }


	//获取附加表名
    public function getTableNameAttr($value,$data)
    {
		$tableName = $this->model->getAttr('name');

        return "cms_archives_".$tableName;
    }

	//获取文档地址
    public function getUrlAttr($value,$data)
    {
		return url('archives',['id'=>$data['id'],'folder'=>$this->category->folder])->build();
    }

    /**
     * 内容数据修改器
     * @access  public
     * @param   string  $content
     * @return  string
     */
    public function setContent($model_id, $data)
    {

		//过滤字段
		$attributes = CmsAttribute::where('model_id',$model_id)->select()->toArray();

		foreach($attributes as $field){

			$val = isset($data[$field['name']]) ? $data[$field['name']] : $field['value'];	


			if($field['type'] == 'checkbox'){
				$data[$field['name']] = implode(",", $val);  
			}

			if($field['type'] == 'editor'){
				//远程图片下载
				$data[$field['name']] = $this->imageLocal($val);

			}
		}

		return $data;
        
    }



    /**
     * 编辑器图片本地化
     * @access  public
     * @param   string  $content
     * @return  string
     */
    public function imageLocal($content)
    {

        $pattern = "/<img.*?src=\"(.*?)\"/i";
        $autolocal = sysconf('editor.download');

        if ($autolocal && preg_match_all($pattern, $content, $images)) {
            $images = array_unique($images[1]);
            foreach ($images as $key => $value) {
				
				$file = strstr($value, '?', true);
				if($file == false) $file = $value;
				
				//判断文件后缀
				$ext = pathinfo($file,PATHINFO_EXTENSION);
				if(empty($ext) || !in_array($ext,['jpg','jpeg','png','gif','webp'])) continue;


				//判断是否远程图片，如果是本站地址就忽略。
				$root =  parse_url($file);
				if(!isset($root['host']) || $root['host'] == request()->host()) continue;
				
				$image  = Storage::down($file);
				if(isset($image['url'])){
					$content = str_replace($value, $image['url'], $content);
				}
            }
        }
		
		return $content;

	}




	/**
     * 关联模型
	*/
    public function model()
    {
        return $this->belongsTo("CmsModel", 'model_id');
    }

    /**
     * 关联栏目模型
	*/
    public function category()
    {
        return $this->belongsTo("CmsCategory", 'category_id');
    }






	/**
     * 获取文档列表
     * @param $tag
     * @return array|false|\PDOStatement|string|\think\Collection
	*/
    public static function getTaglib($tag)
    {
		$model	= !isset($tag['model']) ? '' : $tag['model'];
		$cid	= !isset($tag['cid']) ? '' : $tag['cid']; //栏目ID
		$mid	= !isset($tag['mid']) ? '' : $tag['mid']; //模型ID
		$sid	= !isset($tag['sid']) ? '' : $tag['sid']; //专题ID

		$field	= empty($tag['field']) ? '' : $tag['field'];
        $flag	= empty($tag['flag']) ? '' : $tag['flag'];


		$tags	= empty($tag['tags']) ? '' : $tag['tags'];
	
		$pagesize = empty($tag['pagesize']) ? 0 : (int)$tag['pagesize'];
        $order	= $tag['order'] ?? 'create_time desc, id desc';      // 排序
        $limit	= empty($tag['limit']) ? 10 : (int)$tag['limit'];

		
		$condition = '';
		$where[] = ['a.status','=',1];

		if (empty($cid)) $cid = get_cateid();

		if (is_numeric($cid) && $cid > 0) {
            // 查询子分类,列表要包含子分类内容
            $allcate = CmsCategory::where('status',1)->field('id,pid')->select()->toArray();
			$ids = \libs\DataExtend::getArrSubIds($allcate,$cid);

			$where[] = ['a.category_id','in', $ids];
		}


		if (!empty($mid) && is_numeric($mid)) {
			$where[] = ['a.model_id','=',$mid];
		}

		if (!empty($sid) && is_numeric($sid)) {
			$where[] = ['a.special_id','=',$sid];
		}


		if (!empty($flag)) {

			$arr = [];
			foreach (explode(',', $flag) as $k => $v) {
				if(!empty($v)){
					$arr[] = "FIND_IN_SET('{$v}', a.flag)";
				}
			}
			if (!empty($arr)) {
				$condition .= "(" . implode(' AND ', $arr) . ")";
			}
        
        }

		if ($tags !== '') {

			$arr = [];
			foreach (explode(',', $tags) as $k => $v) {
				if(!empty($v)){
					$arr[] = "FIND_IN_SET('{$v}', a.keywords)";
				}
			}
			if (!empty($arr)) {
				$condition .= "(" . implode(' OR ', $arr) . ")";
			}
        
        }


		$archives = self::with(['category'])->alias('a')->whereTime('a.create_time','<=',time())->where($condition)->order($order);

		if ($model !== '') {
			
			$model_info = CmsModel::where('name',$model)->findOrEmpty();
			
			if (!$model_info->isEmpty()) {
				
				$where[] = ['a.model_id','=', $model_info->id];
		   

				if ($field !== '') {
					$archives->join('cms_archives_'.$model . ' x', 'a.id = x.archives_id', 'LEFT');
					$archives->field('a.*,'.$field);				
				}
			}
		}

		$archives->where($where);
	
		if($pagesize > 0){
			$param = request()->param();
			foreach($param as $key => $val){
				if(empty($val) || $key == "folder"){
					unset($param[$key]);
				}
			}
			
			$list = $archives->paginate([
				'query'     => $param,
				'list_rows' => $pagesize,
			]);

		}else{

			$list = $archives->limit($limit)->select();			
			
		}	
			

		return $list;
	}
}
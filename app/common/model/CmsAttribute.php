<?php
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
namespace app\common\model;

use think\Model;

use think\facade\Config;
use think\facade\Db;

/**
 * 设置模型
 */
class CmsAttribute extends Model {


	//protected $append = ['group_name'];


	public function setNameAttr($value) {
		return strtolower($value);
	}

    public function getGroupNameAttr($value,$data)
    {
        $attr_group = CmsModel::where('id',$data['model_id'])->value('attribute_group');
		
		$attr_group = explode("|", $attr_group);

		if(isset($attr_group[$data['group_id']])){
			return $attr_group[$data['group_id']];
		}
        return '';
    }

	protected function getOptionAttr($value, $data){
		$list = [];
		if ($data == '') {
			return $list;
		}
		if (in_array($data['type'], ['checkbox', 'radio', 'select'])) {
			$row = explode(PHP_EOL, $data['extra']);
			foreach ($row as $k => $val) {
				if (strrpos($val, ":")) {
					list($key, $label) = explode(":", $val);
					$list[] = ['key' => $key, 'label' => $label];
				}else{
					$list[] = ['key' => $k, 'label' => $val];
				}
			}
		}
		return $list;
	}


	public static function getFieldList($model, $ac = "add"){
		$list = [];
		$group = $model['attr_group'];

		$map = [];
		$map[] = ['model_id', '=', $model['id']];
		$map[] = ['is_show', '=', 1];

		$row = self::where($map)->order('group_id asc, sort asc, id asc')
			->select()
			->append(['option'])
			->toArray();


		foreach ($row as $key => $value) {

			if (isset($group[$value['group_id']])) {

				$list[$group[$value['group_id']]][] = $value;
			}else{
				$list[$value['group_id']][] = $value;
			}
		}

		return $list;
	}


	public static function getTypeList(){
		return [
			"text"=>['label'=>"单行文本",'type'=>"VARCHAR",'length'=>"250",'value'=>""],
			"textarea"=>['label'=>"多行文本",'type'=>"VARCHAR",'length'=>"250",'value'=>""],
			"select"=>['label'=>"下拉选择",'type'=>"VARCHAR",'length'=>"250",'value'=>""],
			"radio"=>['label'=>"单选",'type'=>"VARCHAR",'length'=>"100",'value'=>""],
			"checkbox"=>['label'=>"多选",'type'=>"VARCHAR",'length'=>"250",'value'=>""],
			"number"=>['label'=>"数字",'type'=>"INT",'length'=>"11",'value'=>"0"],
			"decimal"=>['label'=>"金额",'type'=>"DECIMAL",'length'=>"14,2",'value'=>"0.00"],
			"switch"=>['label'=>"开关",'type'=>"TINYINT",'length'=>"1",'value'=>"0"],
			"range"=>['label'=>"滑块",'type'=>"TINYINT",'length'=>"1",'value'=>"0"],
			"date"=>['label'=>"日期控件",'type'=>"DATE",'length'=>"",'value'=>""],
			"datetime"=>['label'=>"时间控件",'type'=>"DATETIME",'length'=>"",'value'=>""],
			"editor"=>['label'=>"编辑器",'type'=>"TEXT",'length'=>"",'value'=>""],
			"file"=>['label'=>"单文件上传",'type'=>"VARCHAR",'length'=>"200",'value'=>""],
			"image"=>['label'=>"单图上传",'type'=>"VARCHAR",'length'=>"200",'value'=>""],
			"images"=>['label'=>"多图上传",'type'=>"TEXT",'length'=>"",'value'=>""],
			"color"=>['label'=>"颜色选取",'type'=>"VARCHAR",'length'=>"10",'value'=>""]
		];
	}


	public function model()
    {
        return $this->belongsTo(CmsModel::class);
    }
}
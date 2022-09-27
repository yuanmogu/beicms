<?php
declare (strict_types = 1);

namespace app\common\model;

use libs\DataExtend;

use think\Model;

/**
 * @mixin \think\Model
 */
class CmsSingle extends Model
{
	//protected $append = ['genre_text','url'];
	
	protected $pageGenre = ['article'=>'文章','images'=>'图集','media'=>'媒体'];


	public function getUrlAttr($value,$data)
    {
		return url('single',['folder'=>$data['folder']])->build();
    }

	/**
     * 内容体裁
     * @return array
     */
    public function getGenreTextAttr($value,$data)
    {

		if(isset($this->pageGenre[$data['genre']])){
			return $this->pageGenre[$data['genre']];
		}

        return $data['genre'];
    }

    public function getGenreAttr($value,$data)
    {
		$value = !empty($value) ? explode(',', $value) : [];
		return $value;
    }

    public function setGenreAttr($value,$data)
    {
        return implode(',', $value);
    }





}

<?php
// 这是系统自动生成的公共文件


if (!function_exists('get_cateid')) {
	/***
	 * 获取当前栏目ID
	 * @return mixed
	 */
	function get_cateid()
	{
		$result = 0;
		if (request()->has('folder')) {
			$result = \app\common\model\CmsCategory::where('folder', '=', request()->param('folder'))->value('id');
		}
		return $result;
	}
}




if (!function_exists('getImagesArr')) {
	/**
	 * 图集转数组输出
	 */

    function getImagesArr($data,$Separator = "|")
    {
		if(!empty($data)){
			$data = explode($Separator, $data);
		}
		return $data;
    }
}


if (!function_exists('textarea_br')) {
	/**
	 * 文本域中换行标签输出
	 */
	function textarea_br($info) {
		$info = str_replace("\r\n","<br />",$info);
		return $info;
	}
}

if (!function_exists('substr_zh')) {
	/**
	 * 文本域中换行标签输出
	 */
	function substr_zh($text,$words) {
		$info = mb_substr($text,0,$words,'utf-8');
		if(mb_strlen($text,'utf-8') > $words){
			$info = $info . '...';
		}
		return $info;
	}
}

if (!function_exists('format_time_ago')) {
	/**
	 * 时间转换
	*/
	function format_time_ago($time){

		if(!is_numeric($time)){
			$time = strtotime($time);
		}

		$t = time() - $time;
		$f = array(
			'31536000'=>'年',
			'2592000'=>'个月',
			'604800'=>'星期',
			'86400'=>'天',
			'3600'=>'小时',
			'60'=>'分钟',
			'1'=>'秒'
		);
		foreach ($f as $k=>$v)    {
			if (0 !=$c=floor($t/(int)$k)) {
				return $c.$v.'前';
			}
		}
	}
}

if (!function_exists('is_mobile')) {

    /**
     * 验证输入的手机号码
     * @access  public
     * @param string $mobile 需要验证的手机号码
     * @return bool
     */
    function is_mobile($mobile)
    {
        $chars = "/^(?:(?:\+|00)86)?1[3-9]\d{9}$/";
        if (preg_match($chars, $mobile)) {
            return true;

        } else {
            return false;
        }
    }
}
if (!function_exists('is_email')) {

    /**
     * 验证输入的邮箱
     * @access  public
     * @param string $email 需要验证的邮箱
     * @return bool
     */
    function is_email($email)
    {
        $chars = "/^[A-Za-z0-9\u4e00-\u9fa5]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/";
        if (preg_match($chars, $email)) {
            return true;

        } else {
            return false;
        }
    }
}
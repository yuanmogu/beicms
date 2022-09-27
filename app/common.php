<?php

// +----------------------------------------------------------------------
// | Library for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2022 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: https://gitee.com/zoujingli/ThinkLibrary
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee 仓库地址 ：https://gitee.com/zoujingli/ThinkLibrary
// | github 仓库地址 ：https://github.com/zoujingli/ThinkLibrary
// +----------------------------------------------------------------------

use libs\CodeExtend;
use libs\HttpExtend;

use app\admin\library\service\SystemService;
use app\common\library\Storage;
use think\db\Query;
use think\Model;

if (!function_exists('p')) {
    /**
     * 打印输出数据到文件
     * @param mixed $data 输出的数据
     * @param boolean $new 强制替换文件
     * @param null|string $file 保存文件名称
     * @return false|int
     */
    function p($data, bool $new = false, ?string $file = null)
    {
        return SystemService::instance()->putDebug($data, $new, $file);
    }
}

if (!function_exists('sysconf')) {
    /**
     * 获取或配置系统参数
     * @param string $name 参数名称
     * @param mixed $value 参数内容
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function sysconf(string $name = '', $value = null)
    {
        if (is_null($value) && is_string($name)) {
            return SystemService::instance()->get($name);
        } else {
            return SystemService::instance()->set($name, $value);
        }
    }
}

if (!function_exists('sysuri')) {
    /**
     * 生成最短 URL 地址
     * @param string $url 路由地址
     * @param array $vars PATH 变量
     * @param boolean|string $suffix 后缀
     * @param boolean|string $domain 域名
     * @return string
     */
    function sysuri(string $url = '', array $vars = [], $suffix = true, $domain = false): string
    {
        return SystemService::instance()->sysuri($url, $vars, $suffix, $domain);
    }
}

if (!function_exists('xss_safe')) {
    /**
     * 文本内容XSS过滤
     * @param string $text
     * @return string
     */
    function xss_safe(string $text): string
    {
        // 将所有 onxxx= 中的字母 o 替换为符号 ο，注意它不是字母
        $rules = ['#<script.*?<\/script>#is' => '', '#(\s)on(\w+=\S)#i' => '$1οn$2'];
        return preg_replace(array_keys($rules), array_values($rules), trim($text));
    }
}

if (!function_exists('sysoplog')) {
    /**
     * 写入系统日志
     * @param string $action 日志行为
     * @param string $content 日志内容
     * @return boolean
     */
    function sysoplog(string $action, string $content): bool
    {
        return SystemService::instance()->setOplog($action, $content);
    }
}
if (!function_exists('str2arr')) {
    /**
     * 字符串转数组
     * @param string $text 待转内容
     * @param string $separ 分隔字符
     * @param null|array $allow 限定规则
     * @return array
     */
    function str2arr(string $text, string $separ = ',', ?array $allow = null): array
    {
        $items = [];
        foreach (explode($separ, trim($text, $separ)) as $item) {
            if ($item !== '' && (!is_array($allow) || in_array($item, $allow))) {
                $items[] = trim($item);
            }
        }
        return $items;
    }
}
if (!function_exists('arr2str')) {
    /**
     * 数组转字符串
     * @param array $data 待转数组
     * @param string $separ 分隔字符
     * @param null|array $allow 限定规则
     * @return string
     */
    function arr2str(array $data, string $separ = ',', ?array $allow = null): string
    {
        foreach ($data as $key => $item) {
            if ($item === '' || (is_array($allow) && !in_array($item, $allow))) {
                unset($data[$key]);
            }
        }
        return $separ . join($separ, $data) . $separ;
    }
}
if (!function_exists('encode')) {
    /**
     * 加密 UTF8 字符串
     * @param string $content
     * @return string
     */
    function encode(string $content): string
    {
        [$chars, $length] = ['', strlen($string = iconv('UTF-8', 'GBK//TRANSLIT', $content))];
        for ($i = 0; $i < $length; $i++) $chars .= str_pad(base_convert(ord($string[$i]), 10, 36), 2, 0, 0);
        return $chars;
    }
}
if (!function_exists('decode')) {
    /**
     * 解密 UTF8 字符串
     * @param string $content
     * @return string
     */
    function decode(string $content): string
    {
        $chars = '';
        foreach (str_split($content, 2) as $char) {
            $chars .= chr(intval(base_convert($char, 36, 10)));
        }
        return iconv('GBK//TRANSLIT', 'UTF-8', $chars);
    }
}
if (!function_exists('enbase64url')) {
    /**
     * Base64安全URL编码
     * @param string $string
     * @return string
     */
    function enbase64url(string $string): string
    {
        return CodeExtend::enSafe64($string);
    }
}
if (!function_exists('debase64url')) {
    /**
     * Base64安全URL解码
     * @param string $string
     * @return string
     */
    function debase64url(string $string): string
    {
        return CodeExtend::deSafe64($string);
    }
}
if (!function_exists('http_get')) {
    /**
     * 以get模拟网络请求
     * @param string $url HTTP请求URL地址
     * @param array|string $query GET请求参数
     * @param array $options CURL参数
     * @return boolean|string
     */
    function http_get(string $url, $query = [], array $options = [])
    {
        return HttpExtend::get($url, $query, $options);
    }
}
if (!function_exists('http_post')) {
    /**
     * 以post模拟网络请求
     * @param string $url HTTP请求URL地址
     * @param array|string $data POST请求数据
     * @param array $options CURL参数
     * @return boolean|string
     */
    function http_post(string $url, $data, array $options = [])
    {
        return HttpExtend::post($url, $data, $options);
    }
}

if (!function_exists('down_file')) {
    /**
     * 下载远程文件到本地
     * @param string $source 远程文件地址
     * @param boolean $force 是否强制重新下载
     * @param integer $expire 强制本地存储时间
     * @return string
     */
    function down_file(string $source, bool $force = false, int $expire = 0): string
    {
        return Storage::down($source, $force, $expire)['url'] ?? $source;
    }
}


if (!function_exists('format_bytes')) {
    /**
     * 文件字节单位转换
     * @param string|integer $size
     * @return string
     */
    function format_bytes($size): string
    {
        if (is_numeric($size)) {
            $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
            return round($size, 2) . ' ' . $units[$i];
        } else {
            return $size;
        }
    }
}
if (!function_exists('format_datetime')) {
    /**
     * 日期格式标准输出
     * @param int|string $datetime 输入日期
     * @param string $format 输出格式
     * @return string
     */
    function format_datetime($datetime, string $format = 'Y年m月d日 H:i:s'): string
    {
        if (empty($datetime)) return '-';
        if (is_numeric($datetime)) {
            return date($format, $datetime);
        } else {
            return date($format, strtotime($datetime));
        }
    }
}


if (!function_exists('get_thumb')) {
    /**
     * 生成缩略图
     * @param string $src 文件地址
     * @param boolean $type 1:
     * @param integer $replace 强制替换已有图片
     * @return string
     */
	function get_thumb($url = '', $width = 480, $height = 480, $type = 3, $replace = false)
	{
		$root =  parse_url($url);
		
		//如果不是本站地址，直接返回。
		if(isset($root['host']) && $root['host'] != request()->host()){
			return $url;
		}

		$src = public_path() . $root['path'];

		if (is_file($src) && file_exists($src)) {
			$ext = pathinfo($src, PATHINFO_EXTENSION);
			$name = basename($src, '.'.$ext);
			$dir = dirname($src);

	
			if (in_array(strtolower($ext), array('gif', 'jpg', 'jpeg', 'bmp', 'png'))) {
				$name = $name . '_thumb_' . $width . '_' . $height . '.' . $ext;
				$file = $dir . '/' . $name;

				if (!file_exists($file) || $replace == true) {
					$image = \think\Image::open($src);
					$image->thumb($width, $height, $type);
					$image->save($file);
				}

				$url = str_replace(public_path(), "", $file);
			}
		}

		return $url;
	}

}

if (!function_exists('get_theme_name')) {
    /**
     * 获取当前网站模板
     * @return string
     */
	function get_theme_name() {

		// 查找所有系统设置的模板
		$template_config = sysconf('template.');

		//自动选择模板目录
		$template = !empty($template_config['pc']) ? $template_config['pc']:'default';
		if (request()->isMobile() && !empty($template_config['mobile'])) {
			$template	= $template_config['mobile'];
		}

		return $template;
	}
}



if (!function_exists('get_form')) {
    /**
     * 后台表单模板
     * @return string
     */
	function get_form($field = [], $data = [], $prekey ='') {
		return \form\Form::render($field, $data, $prekey);
	}
}


if (!function_exists('get_theme_config')) {
    /**
     * 获取模板配置
     * @param string $type 类型
     * @param string $name 标识
     * @param string $module 模块
     * @param bool $complete true-获取所有结构数组，false-获取配置值
     * @return array|mixed|null
     */
    function get_theme_config($theme, $complete=false)
    {

		$k = "template_{$theme}_config";
		$config_file = public_path('template'). $theme . DIRECTORY_SEPARATOR . 'config.json';
  
        $config = app()->cache->get($k);
        if ($config && $complete===false && app()->isDebug()!==true) {
            return $config;
        }

        // 优先从数据库里取
        $temp_arr = [];
   
        if (is_file($config_file)) {
            $temp_arr = json_decode(file_get_contents($config_file),true);
        }

        if (!empty($temp_arr)) {
            if ($complete) {

				foreach ($temp_arr as $key => $value) {
					if (!empty($value['item'])) {
						foreach ($value['item'] as $kk=>$v) {
							if (in_array($v['type'], ['checkbox'])) {
								$temp_arr[$key]['item'][$kk]['value'] = explode(',', $v['value']);
							} 
						}
					} 
				}

                return $temp_arr;
            }
            $config = [];
            foreach ($temp_arr as $key => $value) {
				
                if (!empty($value['item'])) {
                    foreach ($value['item'] as $kk=>$v) {
						
                        if (in_array($v['type'], ['checkbox'])) {
                            $config[$key][$v['name']]= explode(',', $v['value']);
                        } else if (in_array($v['type'], ['images'])) {
                            $config[$key][$v['name']]= explode('|', $v['value']);
                        } else {
                            $config[$key][$v['name']] = $v['value'];
                        }
                    }
                } else {
                    if (in_array($value['type'], ['checkbox'])) {
                        $config[$key] = explode(',', $value['value']);
                    } elseif (in_array($value['type'], ['images'])) {
                        $config[$key] = explode('|', $value['value']);
                    } else {
                        $config[$key] = $value['value'];
                    }
                }
            }
            app()->cache->tag('get_theme_config')->set($k, $config);
        }
        return $config;
    }
}
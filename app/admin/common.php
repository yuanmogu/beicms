<?php
use app\admin\library\service\AdminService;
use app\admin\library\service\QueueService;
use app\admin\library\service\TokenService;


if (!function_exists('auth')) {
    /**
     * 访问权限检查
     * @param null|string $node
     * @return boolean
     * @throws ReflectionException
     */
    function auth(?string $node): bool
    {
        return AdminService::instance()->check($node);
    }
}

if (!function_exists('admuri')) {
    /**
     * 生成后台 URL 地址
     * @param string $url 路由地址
     * @param array $vars PATH 变量
     * @param boolean|string $suffix 后缀
     * @param boolean|string $domain 域名
     * @return string
     */
    function admuri(string $url = '', array $vars = [], $suffix = true, $domain = false): string
    {
        return '#' . sysuri($url, $vars, $suffix, $domain);
    }

    function stauri(string $uri = '', array $var = [], $suffix = false, $domain = false)
    {
        url('@', $var, $suffix, $domain);

    }
}


if (!function_exists('sysqueue')) {
    /**
     * 注册异步处理任务
     * @param string $title 任务名称
     * @param string $command 执行内容
     * @param integer $later 延时执行时间
     * @param array $data 任务附加数据
     * @param integer $rscript 任务类型(0单例,1多例)
     * @param integer $loops 循环等待时间
     * @return string
     * @throws \app\admin\library\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function sysqueue(string $title, string $command, int $later = 0, array $data = [], int $rscript = 1, int $loops = 0): string
    {
        return QueueService::instance()->register($title, $command, $later, $data, $rscript, $loops)->code;
    }
}

if (!function_exists('systoken')) {
    /**
     * 生成 CSRF-TOKEN 参数
     * @param null|string $node
     * @return string
     */
    function systoken(?string $node = null): string
    {
        $result = TokenService::instance()->buildFormToken($node);
        return $result['token'] ?? '';
    }
}

if (!function_exists('trace_file')) {
    /**
     * 输出异常数据到文件
     * @param \Exception $exception
     * @return void
     */
    function trace_file(Exception $exception)
    {
        $path = app()->getRuntimePath() . 'trace';
        if (!file_exists($path)) mkdir($path, 0755, true);
        $name = substr($exception->getFile(), strlen(app()->getRootPath()));
        $file = $path . DIRECTORY_SEPARATOR . date('Ymd_His_') . strtr($name, ['/' => '.', '\\' => '.']);
        $class = get_class($exception);
        file_put_contents($file,
            "[CODE] {$exception->getCode()}" . PHP_EOL .
            "[INFO] {$exception->getMessage()}" . PHP_EOL .
            "[FILE] {$class} in {$name} line {$exception->getLine()}" . PHP_EOL .
            "[TIME] " . date('Y-m-d H:i:s') . PHP_EOL . PHP_EOL .
            '[TRACE]' . PHP_EOL . $exception->getTraceAsString()
        );
    }
}













if (!function_exists('write_theme_config')) {
    /**
     * 写入插件配置文件
     * @param $type
     * @param $name
     * @param $module
     * @param $data
     */
    function write_theme_config($theme, $data)
    {

        $config_file = public_path('template').  DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . 'config.json';
  

        if (!is_really_writable($config_file)) {
            throw new Exception(lang('%s,File cannot be written',[$config_file]));
        }

        if ($handle=fopen($config_file, 'w')) {
			
			fwrite($handle, json_encode($data, JSON_UNESCAPED_UNICODE+JSON_PRETTY_PRINT)); //保留中文和换行
		
            fclose($handle);
        } else {
            throw new Exception(lang('%s,File has no permission to write', [$config_file]));
        }
    }
}


if (!function_exists('is_really_writable')) {
    /**
     * 判断文件是否可写
     * @param $file
     * @return bool
     */
    function is_really_writable($file)
    {
        // 在 Unix 内核系统中关闭了 safe_mode, 可以直接使用 is_writable()
        if (DIRECTORY_SEPARATOR == '/' AND @ini_get("safe_mode") == false) {
            return is_writable($file);
        }

        // 在 Windows 系统中打开了 safe_mode的情况
        if (is_dir($file)) {
            $file = rtrim($file, '/').'/'.md5(mt_rand(1,100).mt_rand(1,100));

            if (($fp = @fopen($file, 'ab')) === false) {
                return false;
            }

            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return true;
        } elseif (($fp = @fopen($file, 'ab')) === false) {
            return false;
        }
        fclose($fp);
        return true;
    }
}




if (!function_exists('get_template_all')) {
    /**
     * 解析模板列表
     * @param string $rule 配置名称
     * @param string $type 配置类型
     * @return array
	*/
	function get_template_all(){
		return [
			'pc'  => get_template_list('pc'),
			'mobile' => get_template_list('mobile')
		];
	}
}

if (!function_exists('get_template_list')) {
	/**
     * 解析模板列表
     * @param string $rule 配置名称
     * @param string $type 配置类型
     * @return array
	*/
	function get_template_list($type){

		$tempPath = public_path('template');

		$file  = opendir($tempPath);
		$list = [];
		while (false !== ($filename = readdir($file))) {
			if (!in_array($filename, array('.', '..'))) {
				$files = $tempPath . $filename . '/info.php';
				if (is_file($files)) {
					$info = include($files);
					
					if (isset($info['type']) && $info['type'] == $type) {
						$info['id']  = $filename;
						$preview = '/template/'. $filename . '/' . $info['preview'];
						$info['preview'] = is_file($tempPath . $filename . '/' . $info['preview']) ? $preview : '/static/images/default.png';

						$info['config'] = get_theme_config($filename);

						$list[] = $info;
					}else{
						continue;
					}
				}
			}
		}
		return $list;
	}
}



if (!function_exists('get_template')) {
	/**
	 * 获取所有模版
	 * @return mixed
	 */
	function get_template()
	{

		$template_pc = sysconf('template.pc');

		//模板目录
		$folder = !empty($template_pc) ? $template_pc:'default';
		
		$tpl['category'] = get_file_folder_List(public_path('template').$folder.'/html/category', 2, '*');
		$tpl['archives'] = get_file_folder_List(public_path('template').$folder.'/html/archives', 2, '*');
		$tpl['single'] = get_file_folder_List(public_path('template').$folder.'/html/single', 2, '*');
		$tpl['special'] = get_file_folder_List(public_path('template').$folder.'/html/special', 2, '/*[!index].html'); //排除专题首页模板
			

		return $tpl;
	}
}

if (!function_exists('get_file_folder_List')) {
	/**
	 * 获取文件目录列表
	 * @param string $pathname 路径
	 * @param integer $fileFlag 文件列表 0所有文件列表,1只读文件夹,2是只读文件(不包含文件夹)
	 * @param string $pathname 路径
	 * @return array
	 */
	function get_file_folder_List($pathname, $fileFlag = 0, $pattern = '*')
	{

		$fileArray = array();
		$pathname = rtrim($pathname, '/') . '/';	
		$list = glob($pathname . $pattern);
		foreach ($list as $i => $file) {
			switch ($fileFlag) {
				case 0:
					$fileArray[] = basename($file);
					break;
				case 1:
					if (is_dir($file)) {
						$fileArray[] = basename($file);
					}
					break;

				case 2:
					if (is_file($file)) {
						$fileArray[] = basename($file);
					}
					break;

				default:
					break;
			}
		}

		if (empty($fileArray)) $fileArray = NULL;
		return $fileArray;
	}
}

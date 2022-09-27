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

namespace app\admin\library\service;

use app\admin\library\Exception;
use libs\FaviconExtend;
use app\admin\library\Helper;
use app\admin\model\SystemConfig;
use app\admin\model\SystemData;
use app\admin\model\SystemOplog;
use app\admin\library\Service;
use app\admin\library\storage\LocalStorage;

use think\helper\Str;

/**
 * 系统参数管理服务
 * Class SystemService
 * @package app\admin\library\service
 */
class SystemService extends Service
{

    /**
     * 配置数据缓存
     * @var array
     */
    protected $data = [];

  

    /**
     * 生成静态路径链接
     * @param string $path 后缀路径
     * @param ?string $type 路径类型
     * @param mixed $default 默认数据
     * @return string|array
     */
    public function uri(string $path = '', ?string $type = '__ROOT__', $default = '')
    {
        static $app, $root, $full;
        empty($app) && $app = rtrim(url('@')->build(), '\\/');
        empty($root) && $root = rtrim(dirname($this->app->request->basefile()), '\\/');
        empty($full) && $full = rtrim(dirname($this->app->request->basefile(true)), '\\/');
        $data = ['__APP__' => $app . $path, '__ROOT__' => $root . $path, '__FULL__' => $full . $path];
        return is_null($type) ? $data : ($data[$type] ?? $default);
    }

    /**
     * 生成全部静态路径
     * @param string $path
     * @return string[]
     */
    public function uris(string $path = ''): array
    {
        return $this->uri($path, null);
    }

    /**
     * 设置配置数据
     * @param string $name 配置名称
     * @param mixed $value 配置内容
     * @return integer|string
     * @throws \think\db\exception\DbException
     */
    public function set(string $name, $value = '')
    {
        $this->data = [];
        [$type, $field] = $this->_parse($name);
        if (is_array($value)) {
            $count = 0;
            foreach ($value as $kk => $vv) {
                $count += $this->set("{$field}.{$kk}", $vv);
            }
            return $count;
        } else {
            $this->app->cache->delete('SystemConfig');
            $map = ['type' => $type, 'name' => $field];
            $data = array_merge($map, ['value' => $value]);
            $query = SystemConfig::master(true)->where($map);
            return (clone $query)->count() > 0 ? $query->update($data) : $query->insert($data);
        }
    }

    /**
     * 读取配置数据
     * @param string $name
     * @param string $default
     * @return array|mixed|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get(string $name = '', string $default = '')
    {
        if (empty($this->data)) {
            SystemConfig::cache('SystemConfig')->select()->map(function ($item) {
                $this->data[$item['type']][$item['name']] = $item['value'];
            });
        }
        [$type, $field, $outer] = $this->_parse($name);
        if (empty($name)) {
            return $this->data;
        } elseif (isset($this->data[$type])) {
            $group = $this->data[$type];
            if ($outer !== 'raw') foreach ($group as $kk => $vo) {
                $group[$kk] = htmlspecialchars(strval($vo));
            }
            return $field ? ($group[$field] ?? $default) : $group;
        } else {
            return $default;
        }
    }

    /**
     * 解析缓存名称
     * @param string $rule 配置名称
     * @return array
     */
    private function _parse(string $rule): array
    {
        $type = 'base';
        if (stripos($rule, '.') !== false) {
            [$type, $rule] = explode('.', $rule, 2);
        }
        [$field, $outer] = explode('|', "{$rule}|");
        return [$type, $field, strtolower($outer)];
    }

    /**
     * 生成最短URL地址
     * @param string $url 路由地址
     * @param array $vars PATH 变量
     * @param boolean|string $suffix 后缀
     * @param boolean|string $domain 域名
     * @return string
     */
    public function sysuri(string $url = '', array $vars = [], $suffix = true, $domain = false): string
    {

        // 读取默认节点配置
        $app = $this->app->config->get('route.default_app') ?: 'admin';
        $ctr = Str::snake($this->app->config->get('route.default_controller') ?: 'index');
        $act = Str::lower($this->app->config->get('route.default_action') ?: 'index');
		$ext = $this->app->config->get('route.url_html_suffix') ?: 'html';

        // 生成完整链接地址
        $pre = $this->app->route->buildUrl('@')->suffix(false)->domain($domain)->build();
        $uri = $this->app->route->buildUrl($url, $vars)->suffix($suffix)->domain($domain)->build();

        // 替换省略链接路径
        $sysuri = preg_replace([
            "/^(\/adx\.php)\/{$app}\/{$ctr}\/{$act}\.{$ext}|(^\w|\?|$)?/i",
            "/^(\/adx\.php\/){$app}\/([\w.]+\/[\w]+\.{$ext})|(^\w|\?|$)?/i",
            "/^(\/adx\.php)\/$/i",
        ], ['$1$2', '$1$2$3',  '$1'], $uri);


		return $sysuri;
    }


    /**
     * 写入系统日志内容
     * @param string $action
     * @param string $content
     * @return boolean
     */
    public function setOplog(string $action, string $content): bool
    {
        $oplog = $this->getOplog($action, $content);
        return SystemOplog::insert($oplog) !== false;
    }

    /**
     * 获取系统日志内容
     * @param string $action
     * @param string $content
     * @return array
     */
    public function getOplog(string $action, string $content): array
    {
        return [
            'node'      => NodeService::instance()->getCurrent(),
            'action'    => $action, 'content' => $content,
            'geoip'     => $this->app->request->ip() ?: '127.0.0.1',
            'username'  => AdminService::instance()->getUserName() ?: '-',
            'create_time' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * 打印输出数据到文件
     * @param mixed $data 输出的数据
     * @param boolean $new 强制替换文件
     * @param string|null $file 文件名称
     * @return false|int
     */
    public function putDebug($data, bool $new = false, ?string $file = null)
    {
        if (is_null($file)) $file = $this->app->getRootPath() . 'runtime' . DIRECTORY_SEPARATOR . date('Ymd') . '.log';
        $str = (is_string($data) ? $data : ((is_array($data) || is_object($data)) ? print_r($data, true) : var_export($data, true))) . PHP_EOL;
        return $new ? file_put_contents($file, $str) : file_put_contents($file, $str, FILE_APPEND);
    }

    /**
     * 设置网页标签图标
     * @param ?string $icon 网页标签图标
     * @return boolean
     * @throws \app\admin\library\Exception
     */
    public function setFavicon(?string $icon = null): bool
    {
        try {
            $icon = $icon ?: sysconf('site.favicon');
			$root =  parse_url($icon);
			if(isset($root['host']) && $root['host'] != request()->host()){
				$src = LocalStorage::down($icon);
				$root = parse_url($src);
			}
			$file = public_path() . $root['path'];
            if (!is_file($file) || !file_exists($file)) return false;
            $favicon = new FaviconExtend($file, [48, 48]);
            return $favicon->saveIco("{$this->app->getRootPath()}public/favicon.ico");
        } catch (Exception $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }
    /**
     * 清理运行缓存
     */
    public function clearRuntime(): void
    {
        $this->app->cache->clear();
		$runtimePath = $this->app->getRootPath() . 'runtime' . DIRECTORY_SEPARATOR;
        $this->clearFiles($runtimePath);
    }

    protected function clearFiles(string $path): void
    {
        $files = is_dir($path) ? scandir($path) : [];
        foreach ($files as $file) {
            if ('.' != $file && '..' != $file && is_dir($path . $file)) {
                $this->clearFiles($path . $file . DIRECTORY_SEPARATOR);
                @rmdir($path . $file);
            } elseif ('.gitignore' != $file && is_file($path . $file)) {
                unlink($path . $file);
            }
        }
    }


}
<?php
declare (strict_types = 1);

namespace app\admin\middleware;

use app\admin\library\service\AdminService;
use think\exception\HttpResponseException;

class Check
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {

		$header = [];
		// HTTP.CORS 跨域规则配置
		if (($origin = $request->header('origin', '*')) !== '*') {
			if (is_string($hosts = config('app.cors_host', []))) $hosts = str2arr($hosts);
			if (config('app.cors_auto', 1) || in_array(parse_url(strtolower($origin), PHP_URL_HOST), $hosts)) {
				$headers = config('app.cors_headers', 'Api-Name,Api-Type,Api-Token,User-Form-Token,User-Token,Token');
				$header['Access-Control-Allow-Origin'] = $origin;
				$header['Access-Control-Allow-Methods'] = config('app.cors_methods', 'GET,PUT,POST,PATCH,DELETE');
				$header['Access-Control-Allow-Headers'] = "Authorization,Content-Type,If-Match,If-Modified-Since,If-None-Match,If-Unmodified-Since,X-Requested-With,{$headers}";
				$header['Access-Control-Expose-Headers'] = $headers;
				$header['Access-Control-Allow-Credentials'] = 'true';
			}
		}

		// 访问模式及访问权限检查
		if ($request->isOptions()) {
			return response()->code(204)->header($header);
		} 

		if (!AdminService::instance()->isLogin() && !preg_match('/login/', $request->pathinfo())) {
			
			$result = [
				'code' => 0,
				'msg'  => "请先登录",
				'data' => '',
				'url'  => sysuri('admin/login/index'),
				'wait' => 3,
			];

			$response = view(config('app.dispatch_error_tmpl'), $result);
			throw new HttpResponseException($response);

		}  

		if (!AdminService::instance()->check()) {
			return json(['code' => 0, 'info' => "没有权限" , 'url' => sysuri('admin/index/index')])->header($header);
		} 

		
		$header['X-Frame-Options'] = 'sameorigin';
		return $next($request)->header($header);
    }
}

<?php
declare (strict_types = 1);

namespace app;

use think\Response;
use think\Validate;
use think\exception\ValidateException;
use think\exception\HttpResponseException;

/**
 * 控制器基础类
 */
class HomeController extends BaseController 
{

	protected $site = [];
	protected $contact = [];

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;


    // 初始化
    protected function initialize()
    {

		$this->site = sysconf('site.');
		$this->contact = sysconf('contact.');

		$template = get_theme_name();
		$this->app->view->assign('TPL', get_theme_config($template));
		$this->app->view->assign('site', $this->site);
		$this->app->view->assign('contact', $this->contact);
		
	}

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }



    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param  mixed     $msg 提示信息
     * @param  string    $url 跳转的URL地址
     * @param  mixed     $data 返回的数据
     * @param  integer   $wait 跳转等待时间
     * @param  array     $header 发送的Header信息
     * @return void
     */
    protected function success($msg = '操作成功！', string $url = null, $data = '',  int $code = 1, int $wait = 3, array $header = [])
    {
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        } elseif ($url) {
			$url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : (string)$this->app->route->buildUrl($url);
        }

        $result = [
            'code'  => $code,
            'msg'   => $msg,
            'data'  => $data,
            'url'   => $url,
            'wait'  => $wait,
        ];

		$type = ($this->request->isJson() || $this->request->isAjax()) ? 'json' : 'html';

        if (strtolower($type) == 'html'){
			$response = Response::create(config('app.dispatch_success_tmpl'), 'view')->assign($result)->header($header);
        } else {
			$response = Response::create($result, $type)->header($header);
        }
        
        throw new HttpResponseException($response);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param  mixed     $msg 提示信息
     * @param  string    $url 跳转的URL地址
     * @param  mixed     $data 返回的数据
     * @param  integer   $wait 跳转等待时间
     * @param  array     $header 发送的Header信息
     * @return void
     */
    protected function error($msg = '操作失败！',  $url = null, $data = '', int $code = 0, int $wait = 3, array $header = [])
    {
        if (is_null($url)) {
            $url = $this->request->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ($url) {
			$url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : (string)$this->app->route->buildUrl($url);
        }

        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        $type = ($this->request->isJson() || $this->request->isAjax()) ? 'json' : 'html';
        if ($type == 'html'){
			$response = Response::create(config('app.dispatch_error_tmpl'), 'view')->assign($result)->header($header);
        } else {
			$response = Response::create($result, $type)->header($header);
        }
        throw new HttpResponseException($response);
    }


    /**
     * URL重定向
     * @access protected
     * @param  string         $url 跳转的URL表达式
     * @param  array|integer  $params 其它URL参数
     * @param  integer        $code http code
     * @param  array          $with 隐式传参
     * @return void
     */
    protected function redirect($url, $params = [], $code = 302, $with = [])
    {
        $response = Response::create($url, 'redirect');

        if (is_integer($params)) {
            $code   = $params;
            $params = [];
        }
     
        $response->code($code);
        throw new HttpResponseException($response);
    }



}

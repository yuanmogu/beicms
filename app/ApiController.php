<?php
declare (strict_types = 1);

namespace app;

use think\Response;
use think\exception\HttpResponseException;

/**
 * 控制器基础类
 */
class ApiController extends BaseController 
{

	/**
     * 默认响应输出类型,支持json/xml
     * @var string
     */
    protected $responseType = 'json';



    // 初始化
    protected function initialize()
    {
		if($this->request->isOptions()) exit();
		
		//TOKEN 验证请求来源是否授权
		$header = $this->request->header();
		if(!isset($header['x-access-token'])) exit();
		
		$token = $header['x-access-token'];


	}

    /**
     * 操作成功返回的数据
     * @param string $msg    提示信息
     * @param mixed  $data   要返回的数据
     * @param int    $code   错误码，默认为1
     * @param string $type   输出类型
     * @param array  $header 发送的 Header 信息
     */
    protected function success($msg = '', $data = null, $code = 1, $type = null, array $header = [])
    {
        $this->result($data, $msg, $code, $type, $header);
    }


    /**
     * 操作失败返回的数据
     * @param string $msg    提示信息
     * @param mixed  $data   要返回的数据
     * @param int    $code   错误码，默认为0
     * @param string $type   输出类型
     * @param array  $header 发送的 Header 信息
     */
    protected function error($msg = '', $data = null, $code = 0, $type = null, array $header = [])
    {
        $this->result($data, $msg, $code, $type, $header);
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @access protected
     * @param mixed  $msg    提示信息
     * @param mixed  $data   要返回的数据
     * @param int    $code   错误码，默认为0
     * @param string $type   输出类型，支持json/xml/jsonp
     * @param array  $header 发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    protected function result($data = null, $msg = 'ok', $code = 1, $type = null, array $header = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => $this->request->server('REQUEST_TIME'),
            'data' => $data,
        ];

        // 如果未设置类型则自动判断
        $type = !empty($type) ? $type : $this->responseType;

        if (isset($header['statuscode'])) {
            $code = $header['statuscode'];
            unset($header['statuscode']);
        } else {
            //未设置状态码,根据code值判断
            $code = $code >= 1000 || $code < 200 ? 200 : $code;
        }
        $response = Response::create($result, $type, $code)->header($header);
        throw new HttpResponseException($response);
    }



}

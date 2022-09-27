<?php
declare (strict_types = 1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于ThinkPHP6开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: 权栈 <616550110@qq.com> MIT License Code
// +----------------------------------------------------------------------

namespace exts\sms;

class Tensms {

    /**
     * @var array
     */
    public $config = [];

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var string
     */
    public $version = '2019-07-11';

    /**
     * @var string
     */
    public $action = 'SendSms';

    /**
     * @var string
     */

    public $protocol = 'https://';
    /**
     * @var string
     */

    public $host = 'sms.tencentcloudapi.com';

    /**
     * @var string
     */
    public $algorithm = "TC3-HMAC-SHA256";

    /**
     * @var string
     */
    public $method = 'POST';

    /**
     * @var string
     */
    public $service = 'sms';

    /**
     * @var string
     */
    public $timestamp = 0;

    /**
     * @var object 对象实例
     */
    protected static $instance = null;

    /**
     * 类构造函数
     * class constructor.
     */
    public function __construct()
    {
        $this->config = saenv('tensms');
        $this->timestamp = time();
        $this->request = \think\facade\Request::instance();
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return auth
     */

    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        // 返回实例
        return self::$instance;
    }

    /**
     * @param string $action
     *
     * @return $this
     * @throws ClientException
     */
    public function action($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function options(array $options)
    {
        if ($options !== []) {
            $this->options = array_merge($this->options, $options);
        }

        return $this;
    }

    /**
     * @param string $method
     *
     * @return $this
     * @throws ClientException
     */
    public function method($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * 设置请求协议
     * @param string $protocol 请求协议（https://  http://）
     */
    public function setProtocol($protocol) 
    {
        $this->protocol = $protocol;
    }

    /**
     * 短信发送函数
     * @return exit
     */
    public function request() 
    {

        $this->options = is_array($this->options) ? json_encode($this->options,JSON_UNESCAPED_UNICODE) : $this->options;
        $hashedRequestPayload = $this->RequestPayload($this->options);

        // 拼接字符串
        $canonicalRequest = $this->method."\n"."/\n"."\n"."content-type:application/json\n"
            ."host:".$this->host."\n\n"."content-type;host\n".$hashedRequestPayload;           

        // 获取时间戳
        $date = gmdate("Y-m-d", $this->timestamp);
        $credentialScope = $date."/".$this->service."/tc3_request";
        $stringToSign = $this->stringToSign($canonicalRequest,$credentialScope);

        // 获取真实签名
        $signature = $this->signatrue($stringToSign, $date);
        $authorization = $this->authorization($credentialScope, $signature);
    
        try {
            $result = \system\Http::post($this->protocol.$this->host
                                        ,$this->options,null,
                                        $this->header($authorization));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
      
        return json_decode($result,true);

    }

    /**
     * 返回数据哈希值
     * @access protected
     * @param mixed $payload   
     */
    protected function RequestPayload ($payload) 
    {   
        return hash("SHA256", $payload);
    }

    /**
     * 返回待签名字符串
     * @access protected
     * @param mixed $sign   
     * @param mixed $param
     */
    protected function stringToSign($canonicalRequest, $credentialScope)
    {
        $hashedCanonicalRequest = hash("SHA256", $canonicalRequest);
        return $this->algorithm."\n".$this->timestamp."\n".$credentialScope."\n".$hashedCanonicalRequest;
    }
    /**
     * 返回真实签名
     * @access protected
     * @param mixed $stringToSign   
     * @param mixed $date
     */
    protected function signatrue($stringToSign, $date) {

        $secretDate = hash_hmac("SHA256", $date, "TC3".$this->config['secret_key'], true);
        $secretService = hash_hmac("SHA256", $this->service, $secretDate, true);
        $secretSigning = hash_hmac("SHA256", "tc3_request", $secretService, true);
        return hash_hmac("SHA256", $stringToSign, $secretSigning);
    }

    /**
     * 返回数据签名串
     * @access protected
     * @param string $credentialScope   
     * @param string $signature
     */    
    protected function authorization($credentialScope, $signature)
    {
        return $this->algorithm." Credential=".$this->config['secret_id']."/".$credentialScope .", SignedHeaders=content-type;host, Signature=".$signature;
    }

    /**
     * 设置头部信息
     * @access protected
     * @param array $authorization
     */
    protected function header($authorization)
    {
        $header = array(
            'Content-Type: application/json',
            'X-TC-Action: SendSms',
            'X-TC-Timestamp: '.$this->timestamp,
            'X-TC-Version: 2019-07-11',
            'X-TC-Language: zh-CN',
            'Authorization: '.$authorization,
        );

        return $header;
    }
}
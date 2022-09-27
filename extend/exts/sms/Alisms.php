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

class Alisms {

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
    public $version = '2017-05-25';

    /**
     * @var string
     */
    public $product = 'Dysmsapi';

    /**
     * @var string
     */
    public $action = 'SendSms';

    /**
     * @var string
     */
    public $host = 'dysmsapi.aliyuncs.com';

    /**
     * @var string
     */
    public $protocol = 'https://';
    /**
     * @var string
     */
    public $method = 'GET';

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
        $this->config = saenv('alisms');
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
    public function setProtocol($protocol) {
        $this->protocol = $protocol;
    }

    /**
     * 短信发送函数
     * @return exit
     */
    public function request() {

        // 传递参数
        $apiParams = [
            "AccessKeyId" => $this->config['access_id'],
            "Action" => $this->action,
            "Format" => 'JSON',
            "SignatureMethod" => 'HMAC-SHA1',
            "SignatureNonce" => uniqid((string)mt_rand(0,0xffff), true),
            "SignatureVersion" => '1.0',
            "Timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
            "Version" => $this->version
        ];
        
        $apiParams = array_merge($apiParams,$this->options);
        ksort($apiParams);
        $httpParams = '&'.http_build_query($apiParams);
        
        // 计算签名
        $signature = $this->signature($httpParams);
        $signurl = $this->sendHttpUrl($signature,$httpParams);

        // 发送数据
        try {
            $result = \system\Http::get($signurl);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
     
        return json_decode($result,true);
    }

    /**
     * 设置签名算法
     * @param string $signMethod 签名方法
     */
    public function signature($signMethod) 
    {
        $stringToSign = "GET&%2F&" . urlencode(substr($signMethod,1));
        $stringToSign = base64_encode(hash_hmac("sha1", $stringToSign,$this->config['access_secret'] . "&",true));
        $signature = urlencode($stringToSign);
        return $signature;
    }

    /**
     * 返回签名URL
     * @param mixed $sign   
     * @param mixed $param
     */
    public function sendHttpUrl($sign, $param)
    {
        return $this->protocol.$this->host.'/?Signature='.$sign.$param;
    }
}
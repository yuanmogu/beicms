<?php
declare (strict_types=1);

namespace app\common\library;

use app\common\model\SiteMailing;
use PHPMailer\PHPMailer\PHPMailer;
use libs\CodeExtend;

/**
 * 邮件发送类
 *
 */
class Email
{

    /**
     * @var object 对象实例
     */
    protected static $instance = null;

    /**
     * @PHPMailer 对象实例
     */
    protected $mail = [];

    /**
     * @userValiModel 验证码对象实例
     */
    public $userValiModel = null;


    /**
     * 错误信息
     */
    protected $_error = '';


    //默认配置
    protected $config = [
        'host' => 'smtp.exmail.qq.com',		// 服务器地址
        'port' => 465,							// 服务器端口
        'username' => 'i@beisoo.com',				// 邮件用户名
        'password' => '123456',					// 邮件密码
        'formname' => '贝搜科技',					// 发送邮件显示
		'debug'=> false
    ];

    /**
     * 类构造函数
     * class constructor.
     */
    public function __construct()
    {
        // 此配置项为数组。
        if ($email = sysconf('email.')) {
            $this->config = array_merge($this->config, $email);
        }
        // 创建PHPMailer对象实例
        $this->mail = new PHPMailer();
        $this->mail->CharSet = 'UTF-8';
        $this->mail->IsSMTP();
        /**
         * 是否开启调试模式
         */
        $this->mail->SMTPDebug = $this->config['debug'];
        $this->mail->SMTPAuth = true;
        $this->mail->SMTPSecure = $this->config['auth'];
        $this->mail->Host = $this->config['host'];
        $this->mail->Port = $this->config['port'];
        $this->mail->Username = $this->config['username'];
        $this->mail->Password = trim($this->config['password']);
        $this->mail->SetFrom($this->config['formmail'], $this->config['formname']);
        // 实例化数据库对象
        $this->mailingModel = new SiteMailing();
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return EMAIL
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
     * 设置邮件主题
     * @param string $subject 邮件主题
     * @return $this
     */
    public function Subject($subject)
    {
        $this->mail->Subject = $subject;
        return $this;
    }

    /**
     * 设置发件人
     * @param string $email 发件人邮箱
     * @param string $name 发件人名称
     * @return $this
     */
    public function from($email, $name = '')
    {
        $this->mail->setFrom($email, $name);
        return $this;
    }

    /**
     * 设置邮件内容
     * @param string $body 邮件下方
     * @param boolean $isHtml 是否HTML格式
     * @return $this
     */
    public function MsgHTML($MsgHtml, bool $isHtml = true)
    {
        if ($isHtml) {
            $this->mail->msgHTML($MsgHtml);
        } else {
            $this->mail->Body = $MsgHtml;
        }
        return $this;
    }

    /**
     * 设置收件人
     * @param mixed $email 收件人,多个收件人以,进行分隔
     * @param string $name 收件人名称
     * @return $this
     */
    public function to($email, $name = '')
    {
        $emailArr = $this->buildAddress($email);
        foreach ($emailArr as $address => $name) {
            $this->mail->addAddress($address, $name);
        }

        return $this;
    }

    /**
     * 添加附件
     * @param string $path 附件路径
     * @param string $name 附件名称
     * @return Email
     */
    public function attachment($path, $name = '')
    {
        if (is_file($path)) {
            $this->mail->addAttachment($path, $name);
        }

        return $this;
    }

    /**
     * 构建Email地址
     * @param mixed $emails Email数据
     * @return array
     */
    protected function buildAddress($emails)
    {
        $emails = is_array($emails) ? $emails : explode(',', str_replace(";", ",", $emails));
        $result = [];
        foreach ($emails as $key => $value) {
            $email = is_numeric($key) ? $value : $key;
            $result[$email] = is_numeric($key) ? "" : $value;
        }

        return $result;
    }

    /**
     * 获取最后一条
     * @param string $email
     * @return mailingModel|array|mixed|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLast(string $email)
    {
        $sms = mailingModel::where('email', $email)->order('id', 'desc')->find();
        return $sms ?: null;
    }

    /**
     * 发送验证码
     */
    public function captcha(string $email = '', string $event = "default")
    {
        $code = CodeExtend::random(6);
        $array = [
            'code' => $code,
            'event' => $event,
            'email' => $email,
        ];

        $this->mailingModel->create($array);

        // 设置标题
        $this->to($email)->Subject("验证码")->MsgHTML("验证码为：" . $code);

        return $this;
    }

    /**
     * 检查验证码
     * @param string $email
     * @param string $event
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function check(string $email, $code = '', string $event = "default")
    {
        $result = $this->mailingModel->where([
			['event', '=', $event],
            ['email', '=', $email],
            ['status', '=', 1],
        ])->order("id", "desc")->find();

        if (!empty($result) && $result->code == $code) {

            // 设置已使用
            $result->status = 0;
            $result->save();

            // 是否过期
            $expires = time() - strtotime($result['create_time']);
            if ($expires <= 60) {
                return true;
            }

            $this->setError("当前验证码已过期！");

        } else {
            $this->setError("无效验证码");
        }

        return false;
    }

    /**
     * 获取最后产生的错误
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * 设置错误
     * @param string $error 信息信息
     */
    protected function setError($error)
    {
        $this->_error = $error;
    }

    /**
     * 发送邮件
     * @return boolean
     */
    public function send()
    {
        $result = false;

        try {
            $result = $this->mail->send();
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            $this->setError($e->getMessage());
        }

        $this->setError($result ? '' : $this->mail->ErrorInfo);
        return $result;
    }

    /**
     * 测试发送
     */
    public function testEmail($config)
    {

        if (empty($config) || !is_array($config)) {
            return '缺少必要的信息';
        }

        $this->config = array_merge($this->config, $config);
        $this->mail->Host = $this->config['host'];
        $this->mail->Port = $this->config['port'];
        $this->mail->Username = $this->config['username'];
        $this->mail->Password = trim($this->config['password']);
        $this->mail->SetFrom($this->config['formmail'], $this->config['formname']);
        return $this->to($config['test_mail'])->Subject("测试邮件")->MsgHTML("如果您看到这封邮件，说明测试成功了！")->send();
    }

}

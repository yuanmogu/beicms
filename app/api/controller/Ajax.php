<?php
declare (strict_types=1);

namespace app\api\controller;

use app\BaseController;

use app\common\library\Email;
use app\common\library\Sms;
use app\common\model\SiteUser;
use app\common\model\SiteMailing;

/**
 * 异步调用
 */
class Ajax extends BaseController
{

    // 初始化函数
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * 发送短信
     * @return mixed|void
     */
    public function smsSend()
    {
        if (request()->isAjax()) {

            $mobile = input('mobile');
            $event = input('event', 'register');

            if (!is_mobile($mobile)) {
                return $this->error('手机号码不正确');
            }

            $sms = Sms::instance();
            $last = $sms->getLast($mobile);
            if ($last && time() - $last['create_time'] < 60) {
                return $this->error(__('发送频繁'));
            }

            // 查询是否存在
            if (SiteUser::getByMobile($mobile)) {
                return $this->error('当前手机号已被占用');
            }

            if ($sms->$event($mobile, $event)) {
                return $this->success("验证码发送成功！");
            } else {
                return $this->error($sms->getError());
            }
        }
    }

    /**
     * 发送邮件
     * @return mixed|void
     */
    public function emailSend()
    {
        if (request()->isAjax()) {

            $email = input('email');
            $event = input('event', 'register');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->error('邮件格式不正确');
            }

            $Ems = Email::instance();
            $last = $Ems->getLast($email);
            if ($last && time() - $last['create_time'] < 60) {
                return $this->error(__('发送频繁'));
            }

            if (SiteUser::getByEmail($email)) {
                return $this->error('当前邮箱已被占用');
            }

            if ($Ems->captcha($email, $event)->send()) {
                return $this->success("验证码发送成功！");
            } else {
                return $this->error($Ems->getError());
            }
        }
    }
}

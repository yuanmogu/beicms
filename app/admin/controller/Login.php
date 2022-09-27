<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2022 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// | 免费声明 ( https://thinkadmin.top/disclaimer )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/ThinkAdmin
// | github 代码仓库：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\AdminController;
use libs\CodeExtend;
use app\admin\model\SystemUser;
use app\admin\library\service\AdminService;
use app\admin\library\service\CaptchaService;
use app\admin\library\service\SystemService;

/**
 * 用户登录管理
 * Class Login
 * @package app\admin\controller
 */
class Login extends AdminController
{

    /**
     * 后台登录入口
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {

        if ($this->app->request->isGet()) {
            if (AdminService::instance()->isLogin()) {
                $this->redirect(sysuri('admin/index/index'));
            } else {
                // 当前运行模式
                $system = SystemService::instance();
                // 后台背景处理
                $images = str2arr(sysconf('login_image') ?: '', '|') ?: [
                    $system->uri('/static/theme/img/login/bg1.jpg'), $system->uri('/static/theme/img/login/bg2.jpg'),
                ];
                $this->loginStyle = sprintf('style="background-image:url(%s)" data-bg-transition="%s"', $images[0], join(',', $images));
                // 登录验证令牌
                $this->captchaType = 'LoginCaptcha';
                $this->captchaToken = CodeExtend::uniqidDate(18);
                if (!$this->app->session->get('LoginInputSessionError')) {
                    $this->app->session->set($this->captchaType, $this->captchaToken);
                }
                // 加载登录模板
                $this->title = '系统登录';
                $this->fetch();
            }
        } else {

			$data = $this->app->request->post();
				
			try {

				$rule = [
					'username'	=> 'require|min:4',
					'password'	=> 'require|min:4',
					'verify' => 'require',
					'uniqid' => 'require',
				];

				$message  =   [
					'username.require' => '登录账号不能为空!',
					'username.min'   => '登录账号不能少于4位字符!',
					'password.require' => '登录密码不能为空!',
					'password.min'   => '登录密码不能少于4位字符!',
					'verify.require'   => '图形验证码不能为空!',
					'uniqid.require'   => '图形验证标识不能为空!',
				];

				$result = $this->validate($data,$rule,$message);
				if (true !== $result) $this->error($result);

			} catch (ValidateException $e) {
				$this->error($e->getError());
			}


            if (!CaptchaService::instance()->check($data['verify'], $data['uniqid'])) {
                $this->error('图形验证码验证失败，请重新输入!');
            }
            /*! 用户信息验证 */
            $map = ['username' => $data['username']];
            $user = SystemUser::where($map)->findOrEmpty();
            if ($user->isEmpty()) {
                $this->app->session->set('LoginInputSessionError', true);
                $this->error('登录账号错误，请重新输入!');
            }
            if (empty($user['status'])) {
                $this->app->session->set('LoginInputSessionError', true);
                $this->error('账号已经被禁用，请联系管理员!');
            }
            if (md5($data['password'] . config('app.password_salt')) !== $user['password']) {

                $this->app->session->set('LoginInputSessionError', true);
                $this->error('登录密码错误，请重新输入!');
            }
            $this->app->session->set('user', $user->toArray());
            $this->app->session->delete('LoginInputSessionError');
            $user->inc('login_num')->update([
                'login_at' => date('Y-m-d H:i:s'),
                'login_ip' => $this->app->request->ip(),
            ]);
            sysoplog('系统用户登录', '登录系统后台成功');
            $this->success('登录成功', sysuri('admin/index/index'));
        }
    }

    /**
     * 生成验证码
     */
    public function captcha()
    {
		$input = $this->app->request->post();
        $image = CaptchaService::instance()->initialize();
        $captcha = ['image' => $image->getData(), 'uniqid' => $image->getUniqid()];
        if ($this->app->session->get($input['type']) === $input['token']) {
            $captcha['code'] = $image->getCode();
            $this->app->session->delete($input['type']);
        }
        $this->success('生成验证码成功', $captcha);
    }

    /**
     * 退出登录
     */
    public function out()
    {
        $this->app->session->clear();
        $this->app->session->destroy();
        $this->success('退出登录成功!', sysuri('admin/login/index'));
    }
}

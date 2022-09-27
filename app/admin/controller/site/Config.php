<?php


namespace app\admin\controller\site;

use app\AdminController;
use app\admin\library\service\AdminService;
use app\admin\library\service\ModuleService;
use app\admin\library\service\SystemService;
use app\admin\library\storage\AliossStorage;
use app\admin\library\storage\QiniuStorage;
use app\admin\library\storage\TxcosStorage;

use app\common\library\Email;
use app\common\library\Sms;

/**
 * 站点设置配置
 * Class Config
 * @package app\admin\controller
 */
class Config extends AdminController
{

    /**
     * 修改站点设置参数
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        if ($this->request->isGet()) {
            $this->title = '站点设置';
            $this->fetch();
        } else {
            $post = $this->request->post();
            // 数据数据到系统配置表
            foreach ($post as $k => $v) sysconf($k, $v);
            sysoplog('站点管理', "修改站点设置参数");
            $this->success('修改系统参数成功！', 'javascript:location.reload()');
        }
    }


    /**
     * 生成ICO图标
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setFavicon(?string $icon = null)
    {
		if ($this->request->isAjax()) {
			try {
				SystemService::instance()->setFavicon($icon);
			} catch (\Exception $exception) {
				$this->error($exception->getMessage());
			}
			sysoplog('站点管理', "生成ICO图标");
			$this->success('生成ICO图标成功！');
		}
    }


    /**
     * 邮件测试
     */
    public function testEmail()
    {
       if ($this->request->isGet()) {
			$config = sysconf('email.');
			$info = Email::instance()->testEMail($config);
			$info === true ? $this->success('测试邮件发送成功！') : $this->error($info);
        }
    }



    /**
     * 修改文件存储
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function storage()
    {

        if ($this->request->isGet()) {
            $this->type = input('type', 'local');
            if ($this->type === 'alioss') {
                $this->points = AliossStorage::region();
            } elseif ($this->type === 'qiniu') {
                $this->points = QiniuStorage::region();
            } elseif ($this->type === 'txcos') {
                $this->points = TxcosStorage::region();
            }
            $this->fetch("storage-{$this->type}");
        } else {
            $post = $this->request->post();
            if (!empty($post['storage']['allow_exts'])) {
                $deny = ['sh', 'asp', 'bat', 'cmd', 'exe', 'php'];
                $exts = array_unique(str2arr(strtolower($post['storage']['allow_exts'])));
                if (count(array_intersect($deny, $exts)) > 0) $this->error('禁止上传可执行的文件！');
                $post['storage']['allow_exts'] = join(',', $exts);
            }
            foreach ($post as $name => $value) sysconf($name, $value);
            sysoplog('站点管理', "修改站点设置参数");
            $this->success('修改文件存储成功！');
        }
    }
}
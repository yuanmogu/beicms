<?php
namespace app\admin\controller\system;

use app\AdminController;

use app\admin\model\SystemAuth;
use app\admin\model\SystemUser as userModel;
use app\admin\validate\SystemUser as userValidate;
use app\admin\library\service\AdminService;

use think\exception\ValidateException;


/**
 * 系统用户管理
 * Class User
 * @package app\admin\controller
 */
class User extends AdminController
{

	// 初始化函数
    protected function initialize()
    {
        parent::initialize();
        $this->model = new userModel();
		$this->validate = new userValidate();
    }

    /**
     * 系统用户管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
		$this->title = '系统用户管理';

		$this->type = input('get.type', 'index');

		if ($this->request->isGet()) {
			$get = $this->request->get();
			if($this->output == 'layui.table'){
				$where[] = ['status', '=', intval($this->type === 'index')];

				if(!empty($get['login_at'])){
					$daterange = explode(' ~ ', $get['login_at']);
					$where[] = ['login_at', 'BETWEEN TIME', [$daterange[0], $daterange[1]]];
				}

				if(!empty($get['status'])){
					$where[] = ['status', '=', $get['status']];
				}
	
				if(!empty($get['username'])){
					$where[] = ['username', 'like', '%'.$get['username'].'%'];
				}
				if(!empty($get['nickname'])){
					$where[] = ['nickname', 'like', '%'.$get['nickname'].'%'];
				}
				
				$cfg = ['list_rows' => $get['limit'], 'query' => $get];
				$data = $this->model->where($where)->paginate($cfg)->toArray();
				return json(['msg' => '', 'code' => 0, 'count' => $data['total'], 'data' => $data['data']]);
			}
		}
		
		$this->fetch();

      
    }

    /**
     * 添加系统用户
     * @auth true
     */
    public function add()
    {

		$this->authorizes = SystemAuth::items();

        $this->fetch();
    }

    /**
     * 编辑系统用户
     * @auth true
     */
    public function edit($id)
    {
		$this->superName = AdminService::instance()->getSuperName();
		$this->authorizes = SystemAuth::items();
        $this->vo	= $this->model->find($id);
        $this->fetch();
    }

    /**
     * 修改用户密码
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function pass($id = 0)
    {
        if ($this->app->request->isGet()) {
			$this->verify = false;
			$this->vo = $this->model->where('id',$id)->find()->toArray();
			$this->fetch();
        } else {
            $data = $this->app->request->post();
				
			try {

				$rule = [
				  'password'	=> 'require',
				  'repassword'	=> 'require|confirm:password',
				];

				$message  =   [
					'password.require' => '请输入新密码',
					'repassword.require' => '请再次输入密码',
					'repassword.confirm' => '两次密码输入不一致',
				];

				$result = $this->validate($data,$rule,$message);
				if (true !== $result) $this->error($result);

			} catch (ValidateException $e) {
				$this->error($e->getError());
			}
			$user = $this->model->find($id);
            if ($user->save(['password' => $data['password']])) {
                sysoplog('系统用户管理', "修改用户[{$user['id']}]密码成功");
                $this->success('密码修改成功，下次请使用新密码登录！', '');
            } else {
                $this->error('密码修改失败，请稍候再试！');
            }
        }
    }


    /**
     * 删除系统用户
     * @auth true
     */
    public function remove($id)
    {
        $this->_checkInput();
        if ($this->model->destroy($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 检查输入变量
     */
    private function _checkInput()
    {
        if (in_array('10000', str2arr(input('id', '')))) {
            $this->error('系统超级账号禁止删除！');
        }
    }
}

<?php
declare (strict_types = 1);

namespace app;

use stdClass;

use think\facade\Db;
use think\facade\Session;
use think\facade\Validate;


use think\exception\HttpResponseException;

/**
 * 控制器基础类
 */
class AdminController extends BaseController 
{

	/**
     * 请求GET参数
     * @var array
     */
    public $get = [];

    /**
     * 当前节点
     * @var string
     */
    public $node;

    /**
     * 管理员信息
     * @var array
     */
    public $admin = null;

	/**
     * 数据库实例
     * @var object
     */
	public $model = null;

	/**
     * 验证实例
     * @var object
     */
	public $validate = null;

	/**
     * 操作状态
     * @var int
     */
	public $status = false;



	/**
     * 获取列表数据的标识
     * @var int
     */
	public $output = false;


    // 初始化
    protected function initialize()
    {
		$this->output = $this->request->request('output','');
	}



    /**
     * 返回失败的操作
     * @param mixed $info 消息内容
     * @param mixed $data 返回数据
     * @param mixed $code 返回代码
     */
    public function error($info, $data = '{-null-}', $code = 0): void
    {
        if ($data === '{-null-}') $data = new stdClass();
        throw new HttpResponseException(json([
            'code' => $code, 'info' => $info, 'data' => $data,
        ]));
    }

    /**
     * 返回成功的操作
     * @param mixed $info 消息内容
     * @param mixed $data 返回数据
     * @param mixed $code 返回代码
     */
    public function success($info, $data = '{-null-}', $code = 1): void
    {
        if ($data === '{-null-}') $data = new stdClass();
        throw new HttpResponseException(json([
            'code' => $code, 'info' => $info, 'data' => $data,
        ]));
    }

    /**
     * URL重定向
     * @param string $url 跳转链接
     * @param integer $code 跳转代码
     */
    public function redirect(string $url, int $code = 301): void
    {
        throw new HttpResponseException(redirect($url, $code));
    }

    /**
     * 返回视图内容
     * @param string $tpl 模板名称
     * @param array $vars 模板变量
     * @param null|string $node 授权节点
     */
    public function fetch(string $tpl = '', array $vars = [], ?string $node = null): void
    {
        foreach ($this as $name => $value) $vars[$name] = $value;
		throw new HttpResponseException(view($tpl, $vars));
    }

  
    /**
     * 数据回调处理机制
     * @param string $name 回调方法名称
     * @param mixed $one 回调引用参数1
     * @param mixed $two 回调引用参数2
     * @param mixed $thr 回调引用参数3
     * @return boolean
     */
    public function callback(string $name, &$one = [], &$two = [], &$thr = []): bool
    {
        if (is_callable($name)) return call_user_func($name, $this, $one, $two, $thr);
        foreach (["_{$this->app->request->action()}{$name}", $name] as $method) {
            if (method_exists($this, $method) && false === $this->$method($one, $two, $thr)) {
                return false;
            }
        }
        return true;
    }


	/**
	 * 添加
	 */
	public function save() 
	{
		if ($this->request->isPost()) {

			$post = $this->request->post();
			
			if(!empty($this->validate)){
				try {
					$result = $this->validate->check($post);
					if (true !== $result) $this->error($this->validate->getError());
				} catch (ValidateException $e) {			
					return $this->error($e->getError());
				}
			}

            $this->model->startTrans();
			try {
				$this->model->data($post, true);
                $this->status = $this->model->save($post);
				$this->model->commit();
            } catch (\PDOException $e) {
                $this->model->rollback();
                return $this->error($e->getMessage());
            } catch (\Throwable $th) {
                $this->model->rollback();
                return $this->error($th->getMessage());
            }
			
			return $this->status ? $this->success('保存成功') : $this->error('保存失败');
		}
	}

	/**
	 * 编辑
	 */
	public function update() 
	{

        if ($this->request->isPost()) {

			$post = $this->request->post();

			if(!empty($this->validate)){
				try {
					$result = $this->validate->check($post);
					if (true !== $result) $this->error($this->validate->getError());
				} catch (ValidateException $e) {			
					return $this->error($e->getError());
				}
			}

            $this->model->startTrans();
			try {
				$this->model->data($post, true);
                $this->status = $this->model->update($post);
                $this->model->commit();
            } catch (\PDOException $e) {
                $this->model->rollback();
                return $this->error($e->getMessage());
            } catch (\Throwable $th) {
                $this->model->rollback();
                return $this->error($th->getMessage());
            }
			
			return $this->success('更新成功');
		}
		

	}


	/**
	 * 修改字段
	 */
	public function modify() 
	{
		if ($this->request->isAjax()) {

			$id = $this->request->post('id');	

			if(is_array($id)) $id = arr2str($id);

			$key = $this->request->post('key/s');
			$value = $this->request->post('value');

			$array = array();
			$array[$key] = $value;
	
			$this->model->startTrans();
			try {
				
				$this->status = $this->model->whereIn('id',$id)->update($array);
                $this->model->commit();
            } catch (\PDOException $e) {
                $this->model->rollback();
                return $this->error($e->getMessage());
            } catch (\Throwable $th) {
                $this->model->rollback();
                return $this->error($th->getMessage());
            }
            
            return $this->success('修改成功');	
		}

		return $this->error('修改失败');
	}

    

}

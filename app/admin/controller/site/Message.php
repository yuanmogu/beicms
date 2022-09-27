<?php

namespace app\admin\controller\site;

use app\common\model\SiteMessage;
use app\common\validate\SiteMessage as messageValidate;

use think\facade\View;

use app\AdminController;
/**
 * 网站留言管理
 * Class Notify
 * @package app\data\controller\base
 */
class Message extends AdminController
{

	    // 初始化函数
    protected function initialize()
    {
        parent::initialize();
        $this->model = new SiteMessage();
		$this->validate = new messageValidate();
    }

    /**
     * 网站留言管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
		$this->title = '消息留言管理';

		if ($this->request->isGet()) {
			$get = $this->request->get();
			if($this->output == 'layui.table'){
				
				$where = [];
				if(!empty($get['create_time'])){
					$daterange = explode(' ~ ', $get['create_time']);
					$where[] = ['create_time', 'BETWEEN TIME', [$daterange[0], $daterange[1]]];
				}

				if(!empty($get['title'])){
					$where[] = ['title', 'like', '%'.$get['title'].'%'];
				}
				if(!empty($get['name'])){
					$where[] = ['name', 'like', '%'.$get['name'].'%'];
				}
				if(!empty($get['phone'])){
					$where[] = ['phone', 'like', '%'.$get['phone'].'%'];
				}
		
				$cfg = ['list_rows' => $get['limit'], 'query' => $get];
				$data = $this->model->where($where)->append(['geoisp'])->paginate($cfg)->toArray();
				return json(['msg' => '', 'code' => 0, 'count' => $data['total'], 'data' => $data['data']]);
			}
		}
		
		$this->fetch();

      
    }

	/**
     * 添加网站留言
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
		sysoplog('站点管理', "添加网站留言");
        $this->fetch();
    }

    /**
     * 编辑网站留言
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit($id)
    {
		sysoplog('站点管理', "编辑网站留言");
		$this->vo	= $this->model->find($id);
        $this->fetch();
    }


	/**
     * 网站留言设置
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function config()
    {
		
        if ($this->request->isGet()) {
            $this->title = '网站留言设置';
            $this->fetch();
        } else {
            $post = $this->request->post();
            // 数据数据到系统配置表
            foreach ($post as $k => $v) sysconf($k, $v);
			sysoplog('站点管理', "网站留言设置");
            $this->success('网站留言设置成功！', 'javascript:location.reload()');
        }


    }



    /**
     * 删除网站留言
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function remove($id)
    {
		if(is_array($id)){
			$id = arr2str($id);
		}
		sysoplog('站点管理', "删除网站留言");
        if ($this->model->whereIn('id',$id)->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }


}
<?php
/**
 * +----------------------------------------------------------------------
 * | 留言
 * +----------------------------------------------------------------------
 */
namespace app\index\controller;

use app\common\model\SiteMessage as messageModel;
use app\common\validate\SiteMessage as messageValidate;
use think\facade\View;
use think\facade\Session;

use think\exception\ValidateException;

use app\common\library\Email;

use app\HomeController;

class Message extends HomeController
{
    // 查看留言
    public function index()
    {
		$list = messageModel::where('is_show',1)->order('update_time desc, create_time desc')->paginate(10);
	
		$view = [
			'list'			=> $list,
            'title'			=> '留言',
        ];


		View::assign($view);

		return View::fetch();
    }



	public function add() 
	{
		if ($this->request->isPost()) {

			$post = $this->request->post();

			
			try {
				validate(messageValidate::class)->check($post);
			} catch (ValidateException $e) {
				$this->error($e->getError());
			}
	
			$post['create_time'] = date('Y-m-d H:i:s');
			$post['ip'] = $this->request->ip();
			$post['status'] = 0;

			$interval = sysconf('message.interval');

			if(!empty($interval) && (int)$interval>0){
				$time = "-".(int)$interval." hours";
				$message = messageModel::where(['ip'=>$post['ip']])->whereTime('create_time',$time)->find();
				if(!empty($message)){
					$this->error('您刚才已经提交过了，请稍后再试。');
				}
			}

			if(!empty($post['content']) && is_array($post['content']) ){		
				$msg = '';
				foreach ($data['content'] as $key => $val) {				
					 $msg .= $key.':'.$val.';';
				}
				$data['content'] = $msg;
			}

			$this->model = new messageModel();

            $this->model->startTrans();
			try {
				$this->model->data($post, true);
                $this->model->save($post);
				$this->model->commit();
            } catch (\PDOException $e) {
                $this->model->rollback();
                return $this->error($e->getMessage());
            } catch (\Throwable $th) {
                $this->model->rollback();
                return $this->error($th->getMessage());
            }

	
			//邮件通知开始
			$toemail = sysconf('message.toemail');
			if(!empty($toemail) && is_email($toemail)) {

				$title = '来自【'.$this->site['title'].'】的留言：'.$post['title'];
				//拼接内容
				$content = '主题：'.$post['title'];

				if(isset($post['name'])){
					$content .=  '姓名：'.$post['name'];
				}
				
				$content .=  '<br>电话：'.$post['phone'];

				
				if(isset($post['content'])){
					$content .=  '<br>信息：'.$post['content'];
				}


				$sms = Email::instance()->to($toemail)->Subject($title)->MsgHTML($content)->send();

				if (!$sms) {
					$log = [
						'node'=>'index/message/add',
						'geoip'=>$post['ip'],
						'action'=>'留言提交',
						'content'=>'邮件发送失败：{$sms->getError()}',
						'username'=>'--',
						'create_time'=>date('Y-m-d H:i:s')
					];
					$this->app->db->name('system_oplog')->strict(false)->insert($log);
					//return $this->error($sms->getError());
				}

			}
			//邮件通知结束
			
			return $this->success('感谢！我们会及时与您取得联系！');
		}
	}






}

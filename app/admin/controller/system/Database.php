<?php
/**
 * +----------------------------------------------------------------------
 * | 数据库备份控制器
 * +----------------------------------------------------------------------
 */
namespace app\admin\controller\system;

use app\AdminController;

use app\admin\library\service\AdminService;

use exts\DataBase as Backup;


/**
 * 数据库备份还原
 * Class Database
 * @package app\site\controller
*/
class Database extends AdminController
{
    protected $db = '', $datadir;

    function initialize(){
        parent::initialize();

        $this->config=array(
            'path'     => './Data/', // 数据库备份路径
            'part'     => 2048000,  // 数据库备份卷大小
            'compress' => 1,         // 数据库备份文件是否启用压缩 0不压缩 1 压缩
            'level'    => 9          // 数据库备份文件压缩级别 1普通 4 一般  9最高
        );
        $this->db = new Backup($this->config);

		$this->super = AdminService::instance()->isSuper();
    }


	/**
     * 数据库列表
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
	*/
    public function index()
    {

		$this->title = '数据库管理';
 
        // 数据信息
        $list = $this->db->dataList();

        // 计算总大小
        $total = 0;
        foreach ($list as $k => $v) {
            $total += $v['data_length'];
            $list[$k]['data_size'] = format_bytes($v['data_length']);
        }


        // 提示信息
        $this->tips = '数据库中共有 ' . count($list) . ' 张表，共计 ' . format_bytes($total);
   			
		$this->list = $list;

		$this->fetch();
       
	}


	/**
     * 数据库备份
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
	*/
    public function backup()
    {
        $tables = $this->request->param('id');
        if (!empty($tables)) {
            $tables = explode(',', $tables);
			$name = date('Ymd-His');
            foreach ($tables as $table) {
                $this->db->setFile(['name' => $name, 'part' => 1])->backup($table, 0);
            }
			$this->success("备份成功！");
        } else {
			$this->error('请选择要备份的表！');
        }
    }

	/**
     * 数据库优化
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
	*/
    public function optimize() {
        $tables = $this->request->param('id');

        if (empty($tables)) {
			$this->error('请选择要优化的表！');
        }
        $tables = explode(',',$tables);
        if ($this->db->optimize($tables)) {
			$this->success("数据表优化成功！");
        } else {
			$this->error('数据表优化出错请重试！');
        }
    }

	/**
     * 数据库修复
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
	*/
    public function repair() {
        $tables = $this->request->param('id');
        if (empty($tables)) {
			$this->error('请选择要修复的表！');
        }
        $tables = explode(',',$tables);
        if ($this->db->repair($tables)) {
			$this->success("数据表修复成功！");
        } else {
			$this->error('数据表修复出错请重试！');
        }
    }


	/**
     * 数据库还原
     * @auth true
	 * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
	*/
    public function restore()
    {
		
		$this->title = '数据库还原';

		$list = $this->db->fileList();

		array_multisort(array_column($list, 'time'), SORT_DESC, $list);

		foreach ($list as $k => $v) {
			$list[$k]['data_length'] = format_bytes($v['size']);
		}

		$this->list = $list;

		// 提示信息
		$this->tips = '备份文件列表中共有 ' . count($list) . ' 个文件';

		$this->fetch();

    }


    /**
     * 还原数据库
	 * @auth true
     * @throws Exception
     */
    public function _restore($id)
    {
	
		$this->config = sysconf('database.');

		$gzfiles = $this->db->getFile('timeverif', $id);

		$sqldata='';

		foreach($gzfiles as $gzfile){

			if ($this->config['compress']) {
				
				$file = gzopen($gzfile[1], 'rb');

				$buffer_size = 4096; // read 4kb at a time

				while(!gzeof($file)) {
					$sqldata .= gzread($file, $buffer_size);
				}

				gzclose($file);

			}else{
				
				$sqldata .= file_get_contents($gzfile[1]);
			}
		}


		$sqlFormat = str_replace("\r", "\n", $sqldata);
		$sqlRecords = explode(";\n", $sqlFormat);
		
			
		cache('sqlRecords',$sqlRecords);
		$this->recordCount = count($sqlRecords);
		
		$this->fetch();
    }



    /**
     * 还原数据库
	 * @auth true
     * @throws Exception
     */
    public function _restore_progress($key = 1)
    {
		$sqlRecords  = cache('sqlRecords');

		if(empty($sqlRecords))
			return json(['code'=>0,'msg'=>' !!没有找到数据备份，请重试!!']);

		$recordCount = count($sqlRecords);

		$result = [
			'code'=> 1,
			'total'=> $recordCount,
			'key'=> $key,
			'msg'=> '',
			'progress'=> '',
		];
		
		
					
		if (isset($sqlRecords[$key-1])) {

			$sqlLine = trim($sqlRecords[$key-1]);
			if (!empty($sqlLine)) {
				try {

					$table = '';
					$action = '';

					if (strstr($sqlLine, 'DROP TABLE')) {
						preg_match('/DROP TABLE IF EXISTS `([^ ]*)`/', $sqlLine, $matches);
						$action = "数据表操作";
						$table = $matches[1];
					}
					if (strstr($sqlLine, 'CREATE TABLE')) {
						preg_match('/CREATE TABLE `([^ ]*)`/', $sqlLine, $matches);
						$action = "数据表创建";
						$table = $matches[1];
					}
					if (strstr($sqlLine, 'INSERT INTO')) {
						preg_match('/INSERT INTO `([^ ]*)`/', $sqlLine, $matches);
						$action = "数据导入";
						$table = $matches[1];
					}

					if (false !== $this->app->db->execute($sqlLine)) {
						$result['msg'] .= "[{$key}/{$recordCount}] >>> ".$table."：".$action."...ok";
					} else {
						about(412, " !!还原数据出错!! ");
					}
				} catch (\Throwable $th) {
					$result['code'] = 0;
					$result['msg'] = $th->getMessage();
				}
			}else{
				$result['msg'] = "[{$key}/{$recordCount}] >>> 全部还原完成";
				cache('sqlRecords',NULL);
			}
			// 更新进度
			$result['progress'] = round(($key/$recordCount)*100).'%';
			
			return json($result);
		}
	}




	/**
     * 删除数据库备份文件
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
	*/
    public function remove(string $id)
    {
        if ($this->request->isPost()) {
            if (strpos($id, ',') !== false) {
                $idArr = explode(',', $id);
                foreach ($idArr as $k => $v) {
                    $this->db->delFile($v);
                }
                $this->success("删除成功！");
            }
            if ($this->db->delFile($id)) {
                $this->success("删除成功！");
            } else {
                $this->error("备份文件删除失败，请检查文件权限！");
            }
        }
    }


}
<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2022 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: https://gitee.com/zoujingli/ThinkLibrary
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/ThinkLibrary
// | github 代码仓库：https://github.com/zoujingli/ThinkLibrary
// +----------------------------------------------------------------------

declare (strict_types=1);

namespace app\admin\library\service;

use libs\DataExtend;
use app\admin\model\SystemAuth;
use app\admin\model\SystemNode;
use app\admin\model\SystemUser;
use app\admin\library\Service;

/**
 * 系统权限管理服务
 * Class AdminService
 * @package app\admin\library\service
 */
class AdminService extends Service
{

    /**
     * 是否已经登录
     * @return boolean
     */
    public function isLogin(): bool
    {
        return $this->getUserId() > 0;
    }

    /**
     * 是否为超级用户
     * @return boolean
     */
    public function isSuper(): bool
    {
        return $this->getUserName() === $this->getSuperName();
    }

    /**
     * 获取超级用户账号
     * @return string
     */
    public function getSuperName(): string
    {
        return $this->app->config->get('app.super_user', 'admin');
    }

    /**
     * 获取后台用户ID
     * @return integer
     */
    public function getUserId(): int
    {
        return intval($this->app->session->get('user.id', 0));
    }

    /**
     * 获取后台用户名称
     * @return string
     */
    public function getUserName(): string
    {
        return $this->app->session->get('user.username', '');
    }



    /**
     * 检查指定节点授权
     * --- 需要读取缓存或扫描所有节点
     * @param null|string $node
     * @return boolean
     * @throws \ReflectionException
     */
    public function check(?string $node = ''): bool
    {
        $service = NodeService::instance();
        $methods = $service->getMethods();
        // 兼容 windows 控制器不区分大小写的验证问题
        foreach ($methods as $key => $rule) {
            if (preg_match('#.*?/.*?_.*?#', $key)) {
                $attr = explode('/', $key);
                $attr[1] = strtr($attr[1], ['_' => '']);
                $methods[join('/', $attr)] = $rule;
            }
        }
        $current = $service->fullnode($node);
        if (function_exists('admin_check_filter')) {
            return admin_check_filter($current, $methods, $this->app->session->get('user.nodes', []), $this);
        } elseif ($this->isSuper()) {
            return true;
        } elseif (empty($methods[$current]['isauth'])) {
            return !(!empty($methods[$current]['islogin']) && !$this->isLogin());
        } else {
            return in_array($current, $this->app->session->get('user.nodes', []));
        }
    }

    /**
     * 获取授权节点列表
     * @param array $checkeds
     * @return array
     * @throws \ReflectionException
     */
    public function getTree(array $checkeds = []): array
    {
        [$nodes, $pnodes, $methods] = [[], [], array_reverse(NodeService::instance()->getMethods())];
        foreach ($methods as $node => $method) {
            [$count, $pnode] = [substr_count($node, '/'), substr($node, 0, strripos($node, '/'))];
            if ($count === 2 && !empty($method['isauth'])) {
                in_array($pnode, $pnodes) or array_push($pnodes, $pnode);
                $nodes[$node] = ['node' => $node, 'title' => $method['title'], 'pnode' => $pnode, 'checked' => in_array($node, $checkeds)];
            } elseif ($count === 1 && in_array($pnode, $pnodes)) {
                $nodes[$node] = ['node' => $node, 'title' => $method['title'], 'pnode' => $pnode, 'checked' => in_array($node, $checkeds)];
            }
        }
        foreach (array_keys($nodes) as $key) foreach ($methods as $node => $method) if (stripos($key, $node . '/') !== false) {
            $pnode = substr($node, 0, strripos($node, '/'));
            $nodes[$node] = ['node' => $node, 'title' => $method['title'], 'pnode' => $pnode, 'checked' => in_array($node, $checkeds)];
            $nodes[$pnode] = ['node' => $pnode, 'title' => ucfirst($pnode), 'pnode' => '', 'checked' => in_array($pnode, $checkeds)];
        }
        return DataExtend::arr2tree(array_reverse($nodes), 'node', 'pnode', '_sub_');
    }

    /**
     * 初始化用户权限
     * @param boolean $force 强刷权限
     * @return $this
     */
    public function apply(bool $force = false): AdminService
    {
        if ($force) $this->clearCache();
        if (($uid = $this->getUserId()) <= 0) return $this;
        $user = SystemUser::where(['id' => $uid])->findOrEmpty()->toArray();
        if (!$this->isSuper() && count($aids = $user['authorize']) > 0) {
            $aids = SystemAuth::where(['status' => 1])->whereIn('id', $aids)->column('id');
            if (!empty($aids)) $nodes = SystemNode::distinct(true)->whereIn('auth', $aids)->column('node');
        }
        $user['nodes'] = $nodes ?? [];
        $this->app->session->set('user', $user);
        return $this;
    }

    /**
     * 清理节点缓存
     * @return $this
     */
    public function clearCache(): AdminService
    {
        $this->app->cache->delete('SystemAuthNode');
        return $this;
    }
}
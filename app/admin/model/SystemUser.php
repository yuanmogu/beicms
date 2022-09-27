<?php

declare (strict_types=1);

namespace app\admin\model;

use think\Model;

/**
 * 系统用户模型
 * Class SystemUser
 * @package think\admin\model
 */
class SystemUser extends Model
{

    public function getAuthorizeAttr($value,$data)
    {
        return str2arr($value);
    }


    public function setAuthorizeAttr($value,$data)
    {
        return arr2str($value);
    }


    public function setPasswordAttr($value,$data)
    {
        return md5(md5($value) . config('app.password_salt'));
    }

    /**
     * 获取用户数据
     * @param mixed $map 数据查询规则
     * @param array $data 用户数据集合
     * @param string $field 原外连字段
     * @param string $target 关联目标字段
     * @param string $fields 关联数据字段
     * @return array
     */
    public static function items($map, array &$data = [], string $field = 'uuid', string $target = 'user_info', string $fields = 'username,nickname,headimg,status,delete_time'): array
    {
        $query = static::where($map)->order('sort desc,id desc');
        if (count($data) > 0) {
            $users = $query->whereIn('id', array_unique(array_column($data, $field)))->column($fields, 'id');
            foreach ($data as &$vo) $vo[$target] = $users[$vo[$field]] ?? [];
            return $users;
        } else {
            return $query->column($fields, 'id');
        }
    }


    /**
     * 默认头像处理
     * @param mixed $value
     * @return string
     */
    public function getHeadimgAttr($value): string
    {
        if (empty($value)) try {
            $host = sysconf('site.host') ?: '';
            return "{$host}/static/theme/img/headimg.png";
        } catch (\Exception $exception) {
            return "/static/theme/img/headimg.png";
        } else {
            return $value;
        }
    }

    /**
     * 格式化登录时间
     * @param string $value
     * @return string
     */
    public function getLoginAtAttr(string $value): string
    {
        return format_datetime($value);
    }

    /**
     * 格式化创建时间
     * @param string $value
     * @return string
     */
    public function getCreateAtAttr(string $value): string
    {
        return format_datetime($value);
    }
}
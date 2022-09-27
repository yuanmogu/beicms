<?php
namespace app\common\model;

use think\Model;


/**
 * 网站链接模型
 * Class DataUser
 * @package app\admin\model
 */
class SiteAddons extends Model
{

    public function getConfigAttr($value,$data)
    {
        return unserialize($value);
    }

    public function setConfigAttr($value,$data)
    {
        return serialize($value);
    }

}
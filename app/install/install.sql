

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";



-- --------------------------------------------------------

--
-- 表的结构 `cms_archives`
--

DROP TABLE IF EXISTS `cms_archives`;
CREATE TABLE IF NOT EXISTS `cms_archives` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '标题',
  `keywords` varchar(250) DEFAULT '' COMMENT '关键词',
  `description` varchar(500) DEFAULT '' COMMENT '描述',
  `image` varchar(200) DEFAULT '' COMMENT '图片',
  `flag` varchar(20) DEFAULT '' COMMENT '标志 h头条 c推荐 a特别推荐 s滚动 p图文',
  `model_id` int(10) NOT NULL DEFAULT '0' COMMENT '模型ID',
  `category_id` int(10) NOT NULL DEFAULT '0' COMMENT '栏目ID',
  `special_id` int(11) NOT NULL DEFAULT '0' COMMENT '专题ID',
  `admin_id` int(11) DEFAULT '0' COMMENT '管理员ID',
  `view` int(11) NOT NULL DEFAULT '0' COMMENT '浏览量',
  `sort` int(11) NOT NULL DEFAULT '100' COMMENT '排序',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态 0未审核 1正常 2待审核 3已删除',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `flag` (`flag`),
  KEY `category_id` (`category_id`),
  KEY `model_id` (`model_id`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS-文档' ROW_FORMAT=DYNAMIC;


--
-- 表的结构 `cms_attribute`
--

DROP TABLE IF EXISTS `cms_attribute`;
CREATE TABLE IF NOT EXISTS `cms_attribute` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `model_id` int(10) NOT NULL COMMENT '所属模型',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '字段名',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '字段注释',
  `length` varchar(100) NOT NULL DEFAULT '' COMMENT '字段定义',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '字段类型',
  `value` varchar(100) NOT NULL DEFAULT '' COMMENT '字段默认值',
  `remark` varchar(100) NOT NULL DEFAULT '' COMMENT '备注',
  `extra` varchar(500) NOT NULL DEFAULT '' COMMENT '参数',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
  `is_must` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否必填',
  `group_id` int(8) NOT NULL DEFAULT '1' COMMENT '分组',
  `sort` int(8) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_cms_model_id` (`model_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS-模型属性表' ROW_FORMAT=COMPACT;


-- --------------------------------------------------------

--
-- 表的结构 `cms_category`
--

DROP TABLE IF EXISTS `cms_category`;
CREATE TABLE IF NOT EXISTS `cms_category` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(60) NOT NULL DEFAULT '' COMMENT '标题',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键词',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '英文名称',
  `folder` varchar(100) NOT NULL DEFAULT '' COMMENT '目录',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父栏目ID',
  `type` varchar(20) NOT NULL DEFAULT 'archives' COMMENT '类型：archives/文档;single/单页;link/跳转',
  `link` varchar(200) NOT NULL DEFAULT '' COMMENT '跳转地址',
  `model_id` int(10) NOT NULL DEFAULT '0' COMMENT '模型',
  `sort` int(8) NOT NULL DEFAULT '100' COMMENT '排序',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '小图标',
  `template_list` varchar(100) NOT NULL DEFAULT '' COMMENT '列表模板',
  `template_show` varchar(100) NOT NULL DEFAULT '' COMMENT '内容模板',
  `pagesize` tinyint(3) NOT NULL DEFAULT '20' COMMENT '分页显示数量',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（前后台状态）',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS-栏目表' ROW_FORMAT=COMPACT;


-- --------------------------------------------------------

--
-- 表的结构 `cms_model`
--

DROP TABLE IF EXISTS `cms_model`;
CREATE TABLE IF NOT EXISTS `cms_model` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(30) NOT NULL DEFAULT '' COMMENT '模型名称',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '模型标识',
  `attribute_group` varchar(255) NOT NULL DEFAULT '1:基础' COMMENT '字段分组',
  `list_grid` text COMMENT '列表定义',
  `template_list` varchar(200) DEFAULT '' COMMENT '列表模板',
  `template_show` varchar(200) DEFAULT '' COMMENT '显示模板',
  `sort` int(6) NOT NULL DEFAULT '100' COMMENT '排序',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS-文档模型表' ROW_FORMAT=COMPACT;


-- --------------------------------------------------------

--
-- 表的结构 `cms_single`
--

DROP TABLE IF EXISTS `cms_single`;
CREATE TABLE IF NOT EXISTS `cms_single` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL COMMENT '模型名称',
  `genre` varchar(60) DEFAULT NULL COMMENT '内容体裁',
  `images` text COMMENT '图集',
  `media` varchar(200) NOT NULL DEFAULT '' COMMENT '媒体',
  `content` text COMMENT '内容',
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS-单页表' ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `cms_special`
--

DROP TABLE IF EXISTS `cms_special`;
CREATE TABLE IF NOT EXISTS `cms_special` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '标题',
  `keywords` varchar(250) DEFAULT '' COMMENT '关键词',
  `description` varchar(250) DEFAULT '' COMMENT '内容描述',
  `image` varchar(200) DEFAULT '' COMMENT '图片',
  `folder` varchar(60) NOT NULL COMMENT '目录',
  `type` varchar(60) NOT NULL DEFAULT '' COMMENT '分类',
  `view` int(11) NOT NULL DEFAULT '0' COMMENT '浏览量',
  `flag` varchar(30) DEFAULT '' COMMENT '标记',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态',
  `template` varchar(200) NOT NULL DEFAULT '' COMMENT '模板',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `flag` (`flag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CMS-专题' ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `site_addons`
--

DROP TABLE IF EXISTS `site_addons`;
CREATE TABLE `site_addons` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '插件标识名，英文字母',
  `title` varchar(50)  NOT NULL DEFAULT '' COMMENT '插件名称',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '插件描述',
  `author` varchar(20)  NOT NULL DEFAULT '' COMMENT '插件作者',
  `website` varchar(100) NOT NULL DEFAULT '' COMMENT '网站链接',
  `version` varchar(20) NOT NULL DEFAULT '' COMMENT '插件版本号',
  `config` text COMMENT '插件配置',
  `sort` int(6) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态;1:开启;0:禁用',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '安装时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='插件表';

-- --------------------------------------------------------

--
-- 表的结构 `site_links`
--

DROP TABLE IF EXISTS `site_links`;
CREATE TABLE IF NOT EXISTS `site_links` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '分类',
  `title` varchar(120) NOT NULL DEFAULT '' COMMENT '标题',
  `image` varchar(200) NOT NULL DEFAULT '' COMMENT '图片',
  `url` varchar(200) NOT NULL DEFAULT '' COMMENT '地址',
  `remarks` varchar(250) DEFAULT '' COMMENT '备注',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序 越大越前',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_site_type` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='站点-链接' ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `site_message`
--

DROP TABLE IF EXISTS `site_message`;
CREATE TABLE IF NOT EXISTS `site_message` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(120) NOT NULL DEFAULT '' COMMENT '主题',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '名称',
  `phone` varchar(60) NOT NULL DEFAULT '' COMMENT '电话',
  `content` varchar(500) DEFAULT '' COMMENT '内容',
  `reply` varchar(300) DEFAULT NULL COMMENT '回复',
  `is_show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否前台显示',
  `ip` varchar(50) DEFAULT '' COMMENT 'IP',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未读1已读2已回复',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '回复时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `status` (`status`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='站点-信息' ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `site_slider`
--

DROP TABLE IF EXISTS `site_slider`;
CREATE TABLE IF NOT EXISTS `site_slider` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '分类',
  `title` varchar(120) NOT NULL DEFAULT '' COMMENT '标题',
  `image` varchar(200) NOT NULL DEFAULT '' COMMENT '图片',
  `url` varchar(200) NOT NULL DEFAULT '' COMMENT '地址',
  `remarks` varchar(250) DEFAULT '' COMMENT '备注',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序 越大越前',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_site_type` (`type`) USING BTREE,
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='站点-幻灯片' ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `system_auth`
--

DROP TABLE IF EXISTS `system_auth`;
CREATE TABLE IF NOT EXISTS `system_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT '' COMMENT '权限名称',
  `utype` varchar(50) DEFAULT '' COMMENT '身份权限',
  `desc` varchar(500) DEFAULT '' COMMENT '备注说明',
  `sort` int(11) DEFAULT '0' COMMENT '排序权重',
  `status` tinyint(1) DEFAULT '1' COMMENT '权限状态(1使用,0禁用)',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_system_auth_status` (`status`) USING BTREE,
  KEY `idx_system_auth_title` (`title`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统-权限' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `system_auth_node`
--

DROP TABLE IF EXISTS `system_auth_node`;
CREATE TABLE IF NOT EXISTS `system_auth_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auth` int(11) DEFAULT '0' COMMENT '角色',
  `node` varchar(200) DEFAULT '' COMMENT '节点',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_system_auth_auth` (`auth`) USING BTREE,
  KEY `idx_system_auth_node` (`node`(191)) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统-授权' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `system_config`
--

DROP TABLE IF EXISTS `system_config`;
CREATE TABLE IF NOT EXISTS `system_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) DEFAULT '' COMMENT '配置分类',
  `name` varchar(100) DEFAULT '' COMMENT '配置名称',
  `value` varchar(2048) DEFAULT '' COMMENT '配置内容',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_system_config_type` (`type`) USING BTREE,
  KEY `idx_system_config_name` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统-配置' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `system_config`
--

INSERT INTO `system_config` VALUES(1, 'base', 'app_name', 'BSCms');
INSERT INTO `system_config` VALUES(2, 'base', 'app_theme', 'default');
INSERT INTO `system_config` VALUES(3, 'base', 'app_version', 'v1');
INSERT INTO `system_config` VALUES(4, 'base', 'login_image', '');
INSERT INTO `system_config` VALUES(5, 'compress', 'enable', '1');
INSERT INTO `system_config` VALUES(6, 'compress', 'height', '2048');
INSERT INTO `system_config` VALUES(7, 'compress', 'quality', '60');
INSERT INTO `system_config` VALUES(8, 'compress', 'size', '5');
INSERT INTO `system_config` VALUES(9, 'compress', 'width', '4096');
INSERT INTO `system_config` VALUES(10, 'contact', 'address', '深圳市福田区福华一路6号免税商务大厦30楼');
INSERT INTO `system_config` VALUES(11, 'contact', 'company', '深圳贝搜科技有限公司');
INSERT INTO `system_config` VALUES(12, 'contact', 'coordinates', '114.068196,22.546445');
INSERT INTO `system_config` VALUES(13, 'contact', 'email', '67180273@qq.com');
INSERT INTO `system_config` VALUES(14, 'contact', 'liaison', '张源1');
INSERT INTO `system_config` VALUES(15, 'contact', 'logo', '/static/images/app-icon.png');
INSERT INTO `system_config` VALUES(16, 'contact', 'phone', '15507580410');
INSERT INTO `system_config` VALUES(17, 'contact', 'telephone', '15507580410');
INSERT INTO `system_config` VALUES(18, 'database', 'compress', '1');
INSERT INTO `system_config` VALUES(19, 'database', 'level', '8');
INSERT INTO `system_config` VALUES(20, 'database', 'part', '2048000');
INSERT INTO `system_config` VALUES(21, 'database', 'path', './Data/');
INSERT INTO `system_config` VALUES(22, 'editor', 'download', '1');
INSERT INTO `system_config` VALUES(23, 'email', 'auth', 'ssl');
INSERT INTO `system_config` VALUES(24, 'email', 'formmail', 'i@beisoo.com');
INSERT INTO `system_config` VALUES(25, 'email', 'formname', '小蘑菇');
INSERT INTO `system_config` VALUES(26, 'email', 'password', '123456');
INSERT INTO `system_config` VALUES(27, 'email', 'port', '465');
INSERT INTO `system_config` VALUES(28, 'email', 'server', 'smtp.exmail.qq.com');
INSERT INTO `system_config` VALUES(29, 'email', 'test_mail', '67180273@qq.com');
INSERT INTO `system_config` VALUES(30, 'email', 'type', 'smtp');
INSERT INTO `system_config` VALUES(31, 'email', 'username', 'i@beisoo.com');
INSERT INTO `system_config` VALUES(32, 'message', 'interval', '1');
INSERT INTO `system_config` VALUES(33, 'message', 'toemail', '67180273@qq.com');
INSERT INTO `system_config` VALUES(34, 'site', 'copyright', '版权所有 2014-2022 贝搜科技');
INSERT INTO `system_config` VALUES(35, 'site', 'description', '深圳贝搜科技有限公司是专业的网站建设服务商。');
INSERT INTO `system_config` VALUES(36, 'site', 'favicon', '/static/images/favicon.png');
INSERT INTO `system_config` VALUES(38, 'site', 'keywords', '网站建设');
INSERT INTO `system_config` VALUES(39, 'site', 'logo', '/static/images/logo.png');
INSERT INTO `system_config` VALUES(40, 'site', 'miitbeian', '粤ICP备2021133624号');
INSERT INTO `system_config` VALUES(41, 'site', 'nisbeian', '-');
INSERT INTO `system_config` VALUES(42, 'site', 'slogon', '优秀的互联网+服务商');
INSERT INTO `system_config` VALUES(43, 'site', 'title', '贝搜科技');
INSERT INTO `system_config` VALUES(44, 'storage', 'alioss_http_protocol', 'http');
INSERT INTO `system_config` VALUES(45, 'storage', 'allow_exts', 'doc,gif,ico,jpg,jpeg,mp3,mp4,png,rar,xls,xlsx');
INSERT INTO `system_config` VALUES(46, 'storage', 'link_type', 'none');
INSERT INTO `system_config` VALUES(47, 'storage', 'local_http_domain', '');
INSERT INTO `system_config` VALUES(48, 'storage', 'local_http_protocol', 'follow');
INSERT INTO `system_config` VALUES(49, 'storage', 'name_type', 'xmd5');
INSERT INTO `system_config` VALUES(50, 'storage', 'type', 'local');
INSERT INTO `system_config` VALUES(51, 'template', 'pc', 'blog');
INSERT INTO `system_config` VALUES(52, 'watermark', 'color', '#e600ff');
INSERT INTO `system_config` VALUES(53, 'watermark', 'enable', '1');
INSERT INTO `system_config` VALUES(54, 'watermark', 'image', '/static/images/logo_white.png');
INSERT INTO `system_config` VALUES(55, 'watermark', 'opacity', '49');
INSERT INTO `system_config` VALUES(56, 'watermark', 'position', '8');
INSERT INTO `system_config` VALUES(57, 'watermark', 'size', '18');
INSERT INTO `system_config` VALUES(58, 'watermark', 'text', '贝搜科技');
INSERT INTO `system_config` VALUES(59, 'watermark', 'type', 'image');
INSERT INTO `system_config` VALUES(60, 'watermark', 'minwidth', '600');
INSERT INTO `system_config` VALUES(61, 'watermark', 'minheight', '400');

-- --------------------------------------------------------

--
-- 表的结构 `system_menu`
--

DROP TABLE IF EXISTS `system_menu`;
CREATE TABLE IF NOT EXISTS `system_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0' COMMENT '上级ID',
  `title` varchar(100) DEFAULT '' COMMENT '菜单名称',
  `icon` varchar(100) DEFAULT '' COMMENT '菜单图标',
  `node` varchar(100) DEFAULT '' COMMENT '节点代码',
  `url` varchar(400) DEFAULT '' COMMENT '链接节点',
  `params` varchar(500) DEFAULT '' COMMENT '链接参数',
  `target` varchar(20) DEFAULT '_self' COMMENT '打开方式',
  `sort` int(11) DEFAULT '0' COMMENT '排序权重',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态(0:禁用,1:启用)',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_system_menu_status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统-菜单' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `system_menu`
--

INSERT INTO `system_menu` VALUES(1, 0, '站点管理', '', '', '#', '', '_self', 100, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(2, 0, '内容管理', '', '', '#', '', '_self', 90, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(3, 0, '系统管理', '', '', '#', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(4, 0, '插件管理', '', '', '#', '', '_self', 80, 1, '2022-05-31 00:32:40');

INSERT INTO `system_menu` VALUES(10, 3, '系统配置', '', '', '#', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(11, 10, '系统参数配置', 'layui-icon layui-icon-set', '', 'admin/system.config/index', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(12, 10, '系统日志管理', 'layui-icon layui-icon-form', '', 'admin/system.oplog/index', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(13, 10, '系统菜单管理', 'layui-icon layui-icon-layouts', '', 'admin/system.menu/index', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(14, 3, '权限管理', '', '', '#', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(15, 14, '访问权限管理', 'layui-icon layui-icon-vercode', '', 'admin/system.auth/index', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(16, 14, '系统用户管理', 'layui-icon layui-icon-username', '', 'admin/system.user/index', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(17, 3, '高级管理', '', '', '#', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(18, 17, '数据库管理', 'layui-icon layui-icon-log', '', 'admin/system.database/index', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(19, 17, '数据库还原', 'layui-icon layui-icon-log', '', 'admin/system.database/restore', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(20, 4, '插件管理', 'bi-plug-fill', '', 'admin/site.addons/index', '', '_self', '0', '1', '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(21, 4, '插件列表', '', '', '#', '', '_self', '0', '1', '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(31, 1, '控制面板', '', '', '#', '', '_self', 100, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(32, 1, '站点配置', '', '', '#', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(33, 1, '运营管理', '', '', '#', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(34, 1, '信息管理', '', '', '#', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(35, 31, '控制台', 'bi-speedometer2', '', 'index/welcome', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(36, 32, '站点设置', 'layui-icon layui-icon-set', '', 'admin/site.config/index', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(37, 32, '模板管理', 'layui-icon layui-icon-template', '', 'admin/site.template/index', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(38, 33, '留言管理', 'layui-icon layui-icon-email', '', 'admin/site.message/index', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(39, 33, '链接管理', 'layui-icon layui-icon-link', '', 'site.links/index', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(40, 33, '幻灯片设置', 'layui-icon layui-icon-carousel', '', 'admin/site.slider/index', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(50, 2, '内容管理', '', '', '#', '', '_self', 90, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(51, 2, '内容设置', '', '', '#', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(52, 2, '信息管理', '', '', '#', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(53, 50, '内容管理', 'fa fa-language', '', 'admin/cms.archives/index', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(55, 51, '模型管理', 'layui-icon layui-icon-template', '', 'admin/cms.model/index', '', '_self', 0, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(57, 51, '栏目管理', 'layui-icon layui-icon-template-1', '', 'admin/cms.category/index', '', '_self', 3, 1, '2022-05-31 00:32:40');
INSERT INTO `system_menu` VALUES(58, 52, '专题管理', '', '', 'admin/cms.special/index', '', '_self', 0, 1, '2022-05-31 00:32:40');

-- --------------------------------------------------------

--
-- 表的结构 `system_oplog`
--

DROP TABLE IF EXISTS `system_oplog`;
CREATE TABLE IF NOT EXISTS `system_oplog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node` varchar(200) NOT NULL DEFAULT '' COMMENT '当前操作节点',
  `geoip` varchar(15) NOT NULL DEFAULT '' COMMENT '操作者IP地址',
  `action` varchar(200) NOT NULL DEFAULT '' COMMENT '操作行为名称',
  `content` varchar(1024) NOT NULL DEFAULT '' COMMENT '操作内容描述',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人用户名',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统-日志' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `system_user`
--

DROP TABLE IF EXISTS `system_user`;
CREATE TABLE IF NOT EXISTS `system_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT '' COMMENT '用户账号',
  `password` varchar(32) DEFAULT '' COMMENT '用户密码',
  `nickname` varchar(50) DEFAULT '' COMMENT '用户昵称',
  `headimg` varchar(255) DEFAULT '' COMMENT '头像地址',
  `authorize` varchar(255) DEFAULT '' COMMENT '权限授权',
  `contact_qq` varchar(20) DEFAULT '' COMMENT '联系QQ',
  `contact_mail` varchar(20) DEFAULT '' COMMENT '联系邮箱',
  `contact_phone` varchar(20) DEFAULT '' COMMENT '联系手机',
  `login_ip` varchar(255) DEFAULT '' COMMENT '登录地址',
  `login_at` varchar(20) DEFAULT '' COMMENT '登录时间',
  `login_num` int(11) DEFAULT '0' COMMENT '登录次数',
  `describe` varchar(255) DEFAULT '' COMMENT '备注说明',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态(0禁用,1启用)',
  `sort` int(11) DEFAULT '0' COMMENT '排序权重',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_system_user_status` (`status`) USING BTREE,
  KEY `idx_system_user_username` (`username`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统-用户' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `system_user`
--

INSERT INTO `system_user` VALUES(10000, 'admin', 'c041d6418f2ee60d345e19010fdf9be4', '系统管理员', '', ',,', '67180273', '67180273@qq.com', '15507580410', '127.0.0.1', '2022-09-03 13:56:05', 76, '', 1, 1, '2022-05-31 00:31:45');
COMMIT;


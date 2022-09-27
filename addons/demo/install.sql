CREATE TABLE IF NOT EXISTS `__PREFIX__addons_demo` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(120) NOT NULL DEFAULT '' COMMENT '标题',
  `image` varchar(200) NOT NULL DEFAULT '' COMMENT '图片',
  `url` varchar(200) NOT NULL DEFAULT '' COMMENT '地址',
  `remarks` varchar(250) DEFAULT '' COMMENT '备注',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序 越大越前',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='插件-演示';
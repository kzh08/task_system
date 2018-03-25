/*
Navicat MySQL Data Transfer

Source Server         : root
Source Server Version : 50537
Source Host           : 10.10.10.5:3306
Source Database       : task_center

Target Server Type    : MYSQL
Target Server Version : 50537
File Encoding         : 65001

Date: 2015-11-23 15:21:22
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for task
-- ----------------------------
DROP TABLE IF EXISTS `task`;
CREATE TABLE `task` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '任务id',
  `task_system` varchar(20) NOT NULL COMMENT '任务系统(account,shop,bbs等)',
  `cli_name` varchar(50) NOT NULL COMMENT 'cli名称',
  `cli_version` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'cli版本号',
  `cli_func` varchar(50) NOT NULL COMMENT 'cli方法',
  `extra_param` varchar(256) NOT NULL COMMENT '附属参数',
  `run_start_time` int(10) DEFAULT NULL COMMENT '运行开始时间戳',
  `run_end_time` int(10) DEFAULT NULL COMMENT '运行结束时间',
  `interval_time` int(10) DEFAULT '0' COMMENT '间隔时间(单位秒)',
  `once_num` int(10) NOT NULL DEFAULT '1' COMMENT '执行多少次',
  `allow_ip` varchar(16) DEFAULT NULL COMMENT '允许IP',
  `content` varchar(256) DEFAULT '' COMMENT '任务说明',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `statu` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态 1 ：正常 0：删除',
  PRIMARY KEY (`id`),
  KEY `idx_run_start_time` (`run_start_time`) USING BTREE,
  KEY `idx_interval_time` (`interval_time`),
  KEY `idx_run_end_time` (`run_end_time`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='任务表';

-- ----------------------------
-- Records of task
-- ----------------------------
INSERT INTO `task` VALUES ('1', 'baby', 'Test', '1', 'start', '版本10', '1445588400', '1446110100', '3', '20', '', 'test2', '2015-10-23 16:16:35', '2015-10-29 15:02:17', '1');
INSERT INTO `task` VALUES ('2', 'baby', 'Test', '1', 'start', '一次性任务', '1446103200', '0', '0', '0', '', '', '2015-10-23 16:54:04', '2015-10-29 15:01:24', '1');

-- ----------------------------
-- Table structure for xy_admin
-- ----------------------------
DROP TABLE IF EXISTS `xy_admin`;
CREATE TABLE `xy_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `name` varchar(20) DEFAULT NULL,
  `gender` varchar(5) DEFAULT NULL,
  `tel` varchar(20) DEFAULT NULL COMMENT '手机',
  `email` varchar(50) DEFAULT NULL COMMENT '邮箱',
  `indate` datetime DEFAULT NULL COMMENT '添加时间',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态',
  `add_user` varchar(40) DEFAULT NULL COMMENT '添加账号的用户',
  `face` varchar(100) DEFAULT NULL COMMENT '头像',
  `login_num` smallint(6) DEFAULT '0' COMMENT '登录次数',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后一次的登陆时间',
  `style` varchar(20) DEFAULT NULL COMMENT '皮肤颜色',
  `type` tinyint(50) DEFAULT '0' COMMENT '用户类型 0普通,1管理员',
  `privilege` varchar(255) NOT NULL COMMENT '权限',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of xy_admin
-- ----------------------------
INSERT INTO `xy_admin` VALUES ('1', 'zs', '3ced2d472086fd4b716a9e4b05fba56a', '张嵩', '男', '1', 'zs1379@qq.com', '2014-08-31 15:54:45', '1', '张嵩', null, '710', '2015-10-29 08:54:24', '333333', null, '-1');

-- ----------------------------
-- Table structure for xy_menu
-- ----------------------------
DROP TABLE IF EXISTS `xy_menu`;
CREATE TABLE `xy_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `num` varchar(30) DEFAULT NULL COMMENT '编号',
  `name` varchar(100) DEFAULT NULL,
  `pid` smallint(8) DEFAULT NULL,
  `url` varchar(100) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `sort` int(10) NOT NULL DEFAULT '0',
  `indate` datetime NOT NULL,
  `icons` varchar(50) DEFAULT NULL COMMENT '图标',
  `level` tinyint(4) DEFAULT '1' COMMENT '级别',
  `color` varchar(20) DEFAULT NULL COMMENT '颜色',
  `ispir` tinyint(4) DEFAULT '1' COMMENT '是否权限验证',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=97 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of xy_menu
-- ----------------------------
INSERT INTO `xy_menu` VALUES ('1', '', '系统管理', '0', '', '1', '2', '2014-03-31 15:02:07', 'cog', '1', '', '1');
INSERT INTO `xy_menu` VALUES ('2', 'menu', '栏目管理', '1', 'menu,system', '1', '0', '2013-12-25 17:21:58', null, '2', null, '1');
INSERT INTO `xy_menu` VALUES ('3', 'user', '人员管理', '1', 'user,system', '1', '10', '2014-09-01 15:43:54', 'userm', '2', '', '1');
INSERT INTO `xy_menu` VALUES ('4', '', '个人中心', '0', '', '0', '1', '2014-09-09 15:27:14', 'userm', '1', '', '0');
INSERT INTO `xy_menu` VALUES ('5', 'pass', '修改密码', '4', 'pass,system', '1', '0', '2014-03-28 09:42:33', '', '2', '', '0');
INSERT INTO `xy_menu` VALUES ('94', '', '任务管理', '0', '', '1', '10', '2015-10-22 14:04:14', 'files', '1', '', '1');
INSERT INTO `xy_menu` VALUES ('93', 'extend', '权限管理', '1', 'extent,system,type=um', '1', '15', '2014-09-01 15:47:04', 'files', '2', '', '1');
INSERT INTO `xy_menu` VALUES ('95', '', '任务列表', '94', 'getList,Task', '1', '0', '2015-10-22 14:08:30', 'files', '2', '', '1');
INSERT INTO `xy_menu` VALUES ('96', '', '队列列表', '94', 'getTaskList,Task', '1', '0', '2015-10-29 11:03:04', 'files', '2', '', '1');

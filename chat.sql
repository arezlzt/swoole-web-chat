/*
Navicat MySQL Data Transfer

Source Server         : 腾讯服务器
Source Server Version : 50642
Source Host           : 45.40.253.161:3306
Source Database       : chat

Target Server Type    : MYSQL
Target Server Version : 50642
File Encoding         : 65001

Date: 2018-11-29 13:56:16
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for chat_friend
-- ----------------------------
DROP TABLE IF EXISTS `chat_friend`;
CREATE TABLE `chat_friend` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT '0' COMMENT 'uid',
  `friend_id` int(10) NOT NULL DEFAULT '0' COMMENT '好友id',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_uid_fid` (`uid`,`friend_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chat_friend
-- ----------------------------
INSERT INTO `chat_friend` VALUES ('5', '2', '3', '1543384919');
INSERT INTO `chat_friend` VALUES ('6', '2', '4', '1543387473');
INSERT INTO `chat_friend` VALUES ('7', '2', '5', '1543387494');

-- ----------------------------
-- Table structure for chat_message
-- ----------------------------
DROP TABLE IF EXISTS `chat_message`;
CREATE TABLE `chat_message` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `from_uid` int(10) NOT NULL DEFAULT '0' COMMENT '发送者',
  `to_uid` int(10) NOT NULL DEFAULT '0' COMMENT '接受者',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '消息内容',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0.等待回应  1.已添加  2.已拒绝',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chat_message
-- ----------------------------
INSERT INTO `chat_message` VALUES ('14', '2', '3', '请求添加你为好友', '1543384912', '1');
INSERT INTO `chat_message` VALUES ('15', '2', '5', '请求添加你为好友', '1543387432', '1');
INSERT INTO `chat_message` VALUES ('16', '2', '4', '请求添加你为好友', '1543387434', '1');
INSERT INTO `chat_message` VALUES ('17', '2', '6', '请求添加你为好友', '1543387436', '0');
INSERT INTO `chat_message` VALUES ('18', '2', '7', '请求添加你为好友', '1543387438', '0');
INSERT INTO `chat_message` VALUES ('19', '2', '8', '请求添加你为好友', '1543387439', '0');
INSERT INTO `chat_message` VALUES ('20', '2', '9', '请求添加你为好友', '1543387441', '0');

-- ----------------------------
-- Table structure for chat_record
-- ----------------------------
DROP TABLE IF EXISTS `chat_record`;
CREATE TABLE `chat_record` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `from_uid` int(10) NOT NULL DEFAULT '0' COMMENT '发送人',
  `to_uid` int(10) NOT NULL DEFAULT '0' COMMENT '发送给谁',
  `content` varchar(1000) NOT NULL DEFAULT '' COMMENT '发送内容',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '发送时间',
  PRIMARY KEY (`id`),
  KEY `idx_ct` (`create_time`) USING BTREE,
  KEY `idx_fuid` (`from_uid`) USING BTREE,
  KEY `idx_tuid` (`to_uid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chat_record
-- ----------------------------
INSERT INTO `chat_record` VALUES ('1', '2', '3', 'hi! 本周的任务整的咋样了', '1543391254');
INSERT INTO `chat_record` VALUES ('2', '3', '2', '不怎么样', '1543391255');
INSERT INTO `chat_record` VALUES ('3', '2', '3', '那你什么时候能完成', '1543391256');
INSERT INTO `chat_record` VALUES ('4', '3', '2', '明天吧', '1543391257');
INSERT INTO `chat_record` VALUES ('5', '2', '3', '还原度挺高的，就是有几个问题，有些按钮的功能还未实现，而且目前只有样式，头像也是写死的，也就只能看看，希望后续能把功能加上去，这个东西我不急，你尽快弄！', '1543391258');
INSERT INTO `chat_record` VALUES ('45', '3', '2', '是啊', '1543458485');
INSERT INTO `chat_record` VALUES ('46', '2', '3', '那你什么时候能做好', '1543458807');
INSERT INTO `chat_record` VALUES ('47', '3', '2', '明天吧', '1543458815');
INSERT INTO `chat_record` VALUES ('48', '2', '3', '你确定吗', '1543458820');
INSERT INTO `chat_record` VALUES ('49', '3', '2', '我确定啊', '1543458827');
INSERT INTO `chat_record` VALUES ('50', '2', '3', '去你大爷', '1543458831');
INSERT INTO `chat_record` VALUES ('51', '3', '2', 'qu去你大爷的', '1543458842');
INSERT INTO `chat_record` VALUES ('52', '2', '3', '哈哈哈', '1543458849');
INSERT INTO `chat_record` VALUES ('53', '3', '2', '你笑个锤子', '1543458873');
INSERT INTO `chat_record` VALUES ('54', '2', '3', '我就笑了 你能拿我怎么样', '1543458888');
INSERT INTO `chat_record` VALUES ('55', '3', '2', '自己跟自己聊 好没意思', '1543458904');
INSERT INTO `chat_record` VALUES ('56', '2', '3', '对对', '1543458908');
INSERT INTO `chat_record` VALUES ('57', '2', '3', '222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222', '1543459086');
INSERT INTO `chat_record` VALUES ('58', '2', '3', '222', '1543461503');
INSERT INTO `chat_record` VALUES ('59', '2', '3', '333', '1543461513');
INSERT INTO `chat_record` VALUES ('60', '2', '3', '111', '1543463682');
INSERT INTO `chat_record` VALUES ('61', '2', '3', '8888888888', '1543463741');

-- ----------------------------
-- Table structure for chat_user
-- ----------------------------
DROP TABLE IF EXISTS `chat_user`;
CREATE TABLE `chat_user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '账号',
  `password` varchar(50) NOT NULL DEFAULT '' COMMENT '密码',
  `online` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否在线',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `fid` int(10) NOT NULL DEFAULT '0' COMMENT '聊天fid',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chat_user
-- ----------------------------
INSERT INTO `chat_user` VALUES ('2', '刘天正', 'liutianzheng', '123456', '0', '/static/img/avatar/qianxing.jpg', '0', '1543284877', '27');
INSERT INTO `chat_user` VALUES ('3', '测试001', 'test001', '123456', '0', '/static/img/avatar/qingsong.jpg', '1543298053', '1543298053', '28');
INSERT INTO `chat_user` VALUES ('4', '测试002', 'test002', '123456', '0', '/static/img/avatar/wangnima.jpg', '1543298086', '1543298086', '46');
INSERT INTO `chat_user` VALUES ('5', '测试003', 'test003', '123456', '0', '/static/img/avatar/qianxing.jpg', '1543298100', '1543298100', '47');
INSERT INTO `chat_user` VALUES ('6', '测试004', 'test004', '123456', '0', '/static/img/avatar/redsun.gif', '1543298118', '1543298118', '0');
INSERT INTO `chat_user` VALUES ('7', '测试005', 'test005', '123456', '0', '/static/img/avatar/qianxing.jpg', '1543298132', '1543298132', '0');
INSERT INTO `chat_user` VALUES ('8', '测试006', 'test006', '123456', '0', '/static/img/avatar/haijiaoluoluo.jpg', '1543298146', '1543298146', '0');
INSERT INTO `chat_user` VALUES ('9', '测试007', 'test007', '123456', '0', '/static/img/avatar/haijiaoluoluo.jpg', '1543298162', '1543298162', '0');

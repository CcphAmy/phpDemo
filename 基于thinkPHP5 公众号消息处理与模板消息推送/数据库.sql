-- --------------------------------------------------------
-- 主机:                           120.78.128.180
-- 服务器版本:                        5.5.57-log - Source distribution
-- 服务器操作系统:                      Linux
-- HeidiSQL 版本:                  9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- 导出 pzxzs 的数据库结构
CREATE DATABASE IF NOT EXISTS `pzxzs` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `pzxzs`;

-- 导出  表 pzxzs.xzs_wx_subscribe 结构
CREATE TABLE IF NOT EXISTS `xzs_wx_subscribe` (
  `openid` varchar(50) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `alias` varchar(50) DEFAULT NULL,
  `type` int(5) NOT NULL COMMENT '类型: 0 普通订阅小助手 1 订阅课程推送',
  `atime` int(11) DEFAULT NULL,
  PRIMARY KEY (`openid`),
  KEY `openid` (`openid`),
  KEY `name` (`name`),
  KEY `type` (`type`),
  KEY `alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订阅小助手列表';

-- 数据导出被取消选择。
-- 导出  表 pzxzs.xzs_wx_token 结构
CREATE TABLE IF NOT EXISTS `xzs_wx_token` (
  `appid` varchar(50) NOT NULL,
  `appsecret` varchar(50) DEFAULT NULL,
  `access_token` varchar(512) DEFAULT NULL COMMENT 'access_token',
  `expires_in` varchar(50) DEFAULT NULL,
  `atime` int(12) DEFAULT NULL,
  PRIMARY KEY (`appid`),
  KEY `appid` (`appid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='token';

-- 数据导出被取消选择。
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

--
--  ============
--  PlaatProtect
--  ============
--
--  Created by wplaat
--
--  For more information visit the following website.
--  Website : www.plaatsoft.nl 
--
--  Or send an email to the following address.
--  Email   : info@plaatsoft.nl
--
--  All copyrights reserved (c) 2008-2016 PlaatSoft
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `config` (
  `id` int(11) NOT NULL,
  `category` int(11) NOT NULL,
  `token` varchar(32) NOT NULL,
  `value` varchar(128) NOT NULL,
  `options` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `readonly` tinyint(1) NOT NULL,
  `rebuild` int(11) NOT NULL,
  `encrypt` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `event` (
  `eid` int(11) NOT NULL,
  `timestamp` datetime NOT NULL,
  `nodeid` int(11) NOT NULL,
  `event` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `zwave` (
  `zid` int(11) NOT NULL,
  `nodeid` int(11) NOT NULL,
  `vendor` varchar(32) NOT NULL,
  `description` varchar(128) NOT NULL,
  `location` varchar(128) NOT NULL,
  `home` int(11) NOT NULL,
  `sleep` int(11) NOT NULL,
  `away` int(11) NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `session` (
  `sid` int(11) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `session_id` varchar(50) NOT NULL,
  `timestamp` datetime NOT NULL,
  `requests` int(11) NOT NULL,
  `language` varchar(10) DEFAULT NULL,
  `theme` varchar(10) DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `sensor` (
  `sid` int(11) NOT NULL,
  `nodeid` int(11) NOT NULL,
  `timestamp` datetime NOT NULL,
  `temperature` double NOT NULL,
  `luminance` double NOT NULL,
  `humidity` double NOT NULL,
  `ultraviolet` double NOT NULL,
  `battery` int(11) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `hue` (
  `hid` int(11) NOT NULL,
  `home` int(11) NOT NULL,
  `sleep` int(11) NOT NULL,
  `away` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `notification` (
`nid` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `home` int(11) NOT NULL,
  `sleep` int(11) NOT NULL,
  `away` int(11) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

ALTER TABLE `config` ADD PRIMARY KEY (`id`);
ALTER TABLE `event` ADD PRIMARY KEY (`eid`);
ALTER TABLE `zwave` ADD PRIMARY KEY (`zid`);
ALTER TABLE `session` ADD PRIMARY KEY (`sid`);
ALTER TABLE `sensor` ADD PRIMARY KEY (`sid`);
ALTER TABLE `hue` ADD PRIMARY KEY (`hid`);
ALTER TABLE `notification` ADD PRIMARY KEY (`nid`);

ALTER TABLE `event` ADD INDEX(`timestamp`);

INSERT INTO `config` (`id`, `category`, `token`, `value`, `options`, `date`, `readonly`, `rebuild`, `encrypt`) VALUES
(1, 0, 'database_version', '0.1', '', '2016-06-04', 1, 0, 0),
(2, 51, 'home_password', '', '', '2016-06-21', 0, 0, 1),
(3, 51, 'settings_password', '', '', '2016-06-04', 0, 0, 1),
(4, 52, 'system_name', '', '', '2016-06-21', 0, 0, 0),
(5, 61, 'webcam_name', '', '', '2016-06-04', 0, 0, 0),
(6, 61, 'webcam_description', '', '', '0000-00-00', 0, 0, 0),
(7, 61, 'webcam_resolution', '320x240', '320x240,640x480', '2016-06-27', 0, 0, 0),
(8, 61, 'webcam_present', 'false', 'true,false', '2016-06-21', 0, 0, 0),
(9, 61, 'webcam_device', '/dev/video0', '/dev/video0,/dev/video1,/dev/video2', '0000-00-00', 0, 0, 0),
(10, 62, 'webcam_name', '', '', '0000-00-00', 0, 0, 0),
(11, 62, 'webcam_description', '', '', '0000-00-00', 0, 0, 0),
(12, 62, 'webcam_resolution', '320x240', '320x240,640x480', '0000-00-00', 0, 0, 0),
(13, 62, 'webcam_present', 'false', 'true,false', '0000-00-00', 0, 0, 0),
(14, 62, 'webcam_device', '/dev/video1', '/dev/video0,/dev/video1,/dev/video2', '0000-00-00', 0, 0, 0),
(15, 71, 'hue_description', '', '', '0000-00-00', 0, 0, 0),
(16, 71, 'hue_present', 'false', 'true,false', '2016-06-04', 0, 0, 0),
(17, 71, 'hue_ip_address', '', '', '2016-06-21', 0, 0, 0),
(18, 71, 'hue_key', '', '', '2016-06-21', 0, 0, 0),
(19, 81, 'notification_present', 'false', 'true,false', '2016-06-27', 0, 0, 0),
(20, 81, 'notification_nma_key', '', '', '2016-06-22', 0, 0, 0),
(21, 11, 'zwave_present', 'false', 'true,false', '2016-06-27', 0, 0, 0),
(22, 61, 'webcam_fps', '1.2', '0.1,0.2,0.25,0.5,0.75,1,1.2,1.5,1.75,2', '2016-06-27', 0, 0, 0),
(23, 62, 'webcam_fps', '0.25', '0.1,0.2,0.25,0.5,0.75,1,1.2,1.5,1.75,2', '2016-06-27', 0, 0, 0),
(24, 61, 'webcam_no_motion_area', '', '', '2016-07-01', 0, 0, 0),
(25, 62, 'webcam_no_motion_area', '', '', '2016-07-01', 0, 0, 0);

INSERT INTO `notification` (`nid`, `type`, `home`, `sleep`, `away`) VALUES
(1, 1, 1, 0, 1),
(2, 2, 1, 0, 1);

INSERT INTO `plaatprotect`.`config` (`id`, `category`, `token`, `value`, `options`, `date`, `readonly`, `rebuild`, `encrypt`) 
VALUES ('2', '0', 'alarm_scenario', '0', '', '2016-07-03', '1', '0', '0');

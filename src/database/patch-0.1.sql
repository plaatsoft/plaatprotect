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

CREATE TABLE IF NOT EXISTS `zwave-config` (
  `cid` int(11) NOT NULL,
  `nodeid` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `description` varchar(128) NOT NULL,
  `location` varchar(128) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `session` (
  `sid` int(11) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `session_id` varchar(50) NOT NULL,
  `timestamp` datetime NOT NULL,
  `requests` int(11) NOT NULL,
  `language` varchar(10) DEFAULT NULL,
  `theme` varchar(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `config` ADD PRIMARY KEY (`id`);
ALTER TABLE `event` ADD PRIMARY KEY (`eid`);
ALTER TABLE `zwave-config` ADD PRIMARY KEY (`cid`);
ALTER TABLE `session` ADD PRIMARY KEY (`sid`);

ALTER TABLE `event` MODIFY `eid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE `zwave-config` MODIFY `cid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
ALTER TABLE `session` AUTO_INCREMENT=1;

INSERT INTO `config` (`id`, `category`, `token`, `value`, `options`, `date`, `readonly`, `rebuild`, `encrypt`) VALUES
(1, 0, 'database_version', '0.1', '', '2016-06-04', 1, 0, 0),
(24, 51, 'home_password', '', '', '2016-06-04', 0, 0, 1),
(25, 51, 'settings_password', '', '', '2016-06-04', 0, 0, 1),
(41, 52, 'system_name', '', '', '0000-00-00', 0, 0, 0),
(49, 61, 'webcam_name', 'studeerkamer', '', '2016-06-04', 0, 0, 0),
(50, 61, 'webcam_description', '', '', '0000-00-00', 0, 0, 0),
(51, 61, 'webcam_resolution', '320x240', '320x240,640x480', '2016-06-04', 0, 0, 0),
(52, 61, 'webcam_present', 'true', 'true,false', '2016-06-04', 0, 0, 0),
(53, 61, 'webcam_device', '/dev/video0', '/dev/video0,/dev/video1,/dev/video2', '0000-00-00', 0, 0, 0),
(54, 62, 'webcam_name', '', '', '0000-00-00', 0, 0, 0),
(55, 62, 'webcam_description', '', '', '0000-00-00', 0, 0, 0),
(56, 62, 'webcam_resolution', '320x240', '320x240,640x480', '0000-00-00', 0, 0, 0),
(57, 62, 'webcam_present', 'false', 'true,false', '0000-00-00', 0, 0, 0),
(58, 62, 'webcam_device', '/dev/video1', '/dev/video0,/dev/video1,/dev/video2', '0000-00-00', 0, 0, 0),
(59, 71, 'hue_description', '', '', '0000-00-00', 0, 0, 0),
(60, 71, 'hue_present', 'true', 'true,false', '2016-06-04', 0, 0, 0),
(61, 71, 'hue_ip_address', '', '', '0000-00-00', 0, 0, 0),
(62, 71, 'hue_key', '', '', '0000-00-00', 0, 0, 0);
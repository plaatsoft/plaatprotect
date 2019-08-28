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

ALTER TABLE `session` CHANGE `sid` `sid` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `event` CHANGE `eid` `eid` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `config` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;

UPDATE config SET value="0.4" WHERE token='database_version';

ALTER TABLE `cron` ADD `step` INT NOT NULL AFTER `last_run`;

INSERT INTO cron (`cid`, `note`, `last_run`, `step`) VALUES ('2', 'hue_sensors', '2019-08-25 00:00:00', 60);
UPDATE `cron` SET `step` = '86400' WHERE `cron`.`cid` = 1;


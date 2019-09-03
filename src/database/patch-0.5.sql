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
--  All copyrights reserved (c) 1996-2019 PlaatSoft
--

-- config table
UPDATE config SET value="0.5" WHERE token='database_version';

-- cron table
INSERT INTO cron (`cid`, `note`, `last_run`, `every_x_mins`) VALUES ('5', 'database_backup', '2019-09-01 00:00:00', 1440);
INSERT INTO cron (`cid`, `note`, `last_run`, `every_x_mins`) VALUES ('6', 'hue_battery_sensor', '2019-09-01 00:00:00', 5);

INSERT INTO config (category, token, value, options, date, readonly, rebuild, encrypt) VALUES (0, 'alarm_duration', '300', '', '0000-00-00', 0, 0, 0);
INSERT INTO config (category, token, value, options, date, readonly, rebuild, encrypt) VALUES (21, 'email_present', 'false', 'true,false', '0000-00-00', 0, 0, 0);
INSERT INTO config (category, token, value, options, date, readonly, rebuild, encrypt) VALUES (21, 'email_address', '', '', '0000-00-00', 0, 0, 0);







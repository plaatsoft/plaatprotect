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

UPDATE config SET value="0.6" WHERE token='database_version';

INSERT INTO config (category, token, value, options, date, readonly, rebuild, encrypt) VALUES (0, 'enable_pressure_view', 'false', 'true,false', '0000-00-00', 0, 0, 0);

UPDATE `config` SET `category` = '11' WHERE token="device_offline_timeout";
UPDATE `config` SET `category` = '10' WHERE token="alarm_duration";

INSERT INTO config (category, token, value, options, date, readonly, rebuild, encrypt) VALUES (10, 'enable_motion_alarm', 'true', 'true,false', '0000-00-00', 0, 0, 0);
INSERT INTO config (category, token, value, options, date, readonly, rebuild, encrypt) VALUES (10, 'enable_temperature_alarm', 'false', 'true,false', '0000-00-00', 0, 0, 0);
INSERT INTO config (category, token, value, options, date, readonly, rebuild, encrypt) VALUES (10, 'alarm_high_temperature', '40.0', '', '0000-00-00', 0, 0, 0);
INSERT INTO config (category, token, value, options, date, readonly, rebuild, encrypt) VALUES (10, 'alarm_low_temperature', '0.0', '', '0000-00-00', 0, 0, 0);
INSERT INTO config (category, token, value, options, date, readonly, rebuild, encrypt) VALUES (0, 'temperature_alarm_on', '0', '0,1', '0000-00-00', '1', '0', '0');

INSERT INTO config (category, token, value, options, date, readonly, rebuild, encrypt) VALUES (91, 'weather_present', 'false', 'true,false', '0000-00-00', 0, 0, 0);
INSERT INTO config (category, token, value, options, date, readonly, rebuild, encrypt) VALUES (91, 'weather_city', '', '', '0000-00-00', 0, 0, 0);
INSERT INTO config (category, token, value, options, date, readonly, rebuild, encrypt) VALUES (91, 'weather_country', '', '', '0000-00-00', 0, 0, 0);
INSERT INTO config (category, token, value, options, date, readonly, rebuild, encrypt) VALUES (91, 'weather_api_key', '', '', '0000-00-00', 0, 0, 0);

INSERT INTO cron (`cid`, `note`, `last_run`, `every_x_mins`) VALUES ('7', 'current_weather', '2019-09-07 00:00:00', 5);
INSERT INTO `zigbee` (`zid`, `vendor`, `type`, `version`, `location`, `state`) VALUES ('100', 'OpenWeatherMap.org', 1, '?', 'buiten', 0);
INSERT INTO `zigbee` (`zid`, `vendor`, `type`, `version`, `location`, `state`) VALUES ('101', 'OpenWeatherMap.org', 7, '?', 'buiten', 0);
INSERT INTO `zigbee` (`zid`, `vendor`, `type`, `version`, `location`, `state`) VALUES ('102', 'OpenWeatherMap.org', 5, '?', 'buiten', 0);

-- 14-09-2019
INSERT INTO config (category, token, value, options, date, readonly, rebuild, encrypt) VALUES (0, 'enable_windspeed_view', 'false', 'true,false', '0000-00-00', 0, 0, 0);
INSERT INTO `zigbee` (`zid`, `vendor`, `type`, `version`, `location`, `state`) VALUES ('103', 'OpenWeatherMap.org', 8, '?', 'buiten', 0);
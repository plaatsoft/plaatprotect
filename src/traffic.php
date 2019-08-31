<?php

/* 
**  ===========
**  PlaatProtect
**  ===========
**
**  Created by wplaat
**
**  For more information visit the following website.
**  Website : www.plaatsoft.nl 
**
**  Or send an email to the following address.
**  Email   : info@plaatsoft.nl
**
**  All copyrights reserved (c) 2008-2016 PlaatSoft
*/

/**
 * @file
 * @brief test data page
 */
 
include "config.inc";
include "general.inc";
include "database.inc";

plaatprotect_db_connect($dbhost, $dbuser, $dbpass, $dbname) ;

$nodeId = 1;

#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"vendor":"Aeon", "device":"Controller"}');

$nodeId = 2;

#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"vendor":"Aeon", "device":"Sirene"}');

$nodeId = 3;

plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "battery":99}');
plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "temperature":25.6}');
plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "luminance":200}');
plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "humidity":60}');
plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "ultraviolet":4}');
plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"notification", "value":"wakeup"}');
plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"motion"}');
plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"vibration"}');
plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"off"}');
plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"vendor":"Aeotec", "device":"Sensor"}');

$nodeId = 4;

#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "battery":99}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "temperature":25.6}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "luminance":200}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "humidity":60}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "ultraviolet":4}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"notification", "value":"wakeup"}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"motion"}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"vibration"}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"off"}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"vendor":"Aeotec", "device":"Sensor"}');

$nodeId = 6;

#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "battery":99}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "temperature":25.6}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "luminance":200}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "humidity":60}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "ultraviolet":4}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"notification", "value":"wakeup"}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"motion"}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"vibration"}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"off"}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"vendor":"Aeotec", "device":"Sensor"}');

$nodeId = 7;

#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "battery":99}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "temperature":25.6}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "luminance":200}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "humidity":60}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "ultraviolet":4}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"notification", "value":"wakeup"}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"motion"}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"vibration"}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"off"}');
#plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"vendor":"Aeotec", "device":"Sensor"}');

echo 'traffic created';
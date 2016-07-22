<?php

include "config.inc";
include "general.inc";
include "database.inc";

plaatprotect_db_connect($dbhost, $dbuser, $dbpass, $dbname) ;

$nodeId = 1;

#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"vendor":"Aeon", "device":"Controller"}');

$nodeId = 2;

#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"vendor":"Aeon", "device":"Sirene"}');

$nodeId = 3;

#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "battery":99}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "temperature":25.6}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "luminance":200}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "humidity":60}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "ultraviolet":4}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"notification", "value":"wakeup"}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"motion"}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"vibration"}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"off"}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"vendor":"Aeotec", "device":"Sensor"}');

$nodeId = 4;

#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "battery":99}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "temperature":25.6}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "luminance":200}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "humidity":60}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "ultraviolet":4}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"notification", "value":"wakeup"}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"motion"}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"vibration"}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"off"}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"vendor":"Aeotec", "device":"Sensor"}');

$nodeId = 6;

#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "battery":99}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "temperature":25.6}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "luminance":200}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "humidity":60}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "ultraviolet":4}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"notification", "value":"wakeup"}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"motion"}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"vibration"}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"off"}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"vendor":"Aeotec", "device":"Sensor"}');

$nodeId = 7;

#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "battery":99}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "temperature":25.6}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "luminance":200}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "humidity":60}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "ultraviolet":4}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"notification", "value":"wakeup"}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"motion"}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"vibration"}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"off"}');
#plaatprotect_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"vendor":"Aeotec", "device":"Sensor"}');

echo 'traffic created';
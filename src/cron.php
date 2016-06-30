<?php

/* 
**  ============
**  PlaatProtect
**  ============
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
 * @brief contain cron page
 */
  
$time_start = microtime(true);

include "config.inc";
include "general.inc";
include "database.inc";

/*
** ---------------------
** CRON
** ---------------------
*/

plaatprotect_db_connect($dbhost, $dbuser, $dbpass, $dbname);

#$zwave_present = plaatprotect_db_get_config_item('zwave_present', ZWAVE);
#if ($zwave_present=="true") {
#   exec('php '.BASE_DIR.'/interfaces/zwave.php > /dev/null 2>&1 &');
#}

$webcam_present_1 = plaatprotect_db_get_config_item('webcam_present', WEBCAM_1);
if ($webcam_present_1=="true") {
   exec('php '.BASE_DIR.'/interfaces/webcam.php 1 > /dev/null 2>&1 &');
}

plaatprotect_db_close();

// Calculate to page render time
$time_end = microtime(true);
$time = $time_end - $time_start;

echo "cron took ".round($time,2)." secs";


/*
** ---------------------
** THE END
** ---------------------
*/

?> 

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
**  All copyrights reserved (c) 1996-2019 PlaatSoft
*/

/**
 * @file
 * @brief contain cron page
 */
  
$time_start = microtime(true);

include "config.php";
include "general.php";
include "database.php";

/*
** ---------------------
** CRON
** ---------------------
*/

plaatprotect_db_connect($dbhost, $dbuser, $dbpass, $dbname);

$zwave_present = plaatprotect_db_config_value('zwave_present', CATEGORY_ZWAVE);
if ($zwave_present=="true") {
   exec('cd '.BASE_DIR.'/interfaces;php zwave.php > /dev/null 2>&1 &');
}

$webcam_present_1 = plaatprotect_db_config_value('webcam_present', CATEGORY_WEBCAM_1);
if ($webcam_present_1=="true") {
   exec('cd '.BASE_DIR.'/interfaces; php webcam.php 1 > /dev/null 2>&1 &');	
}

exec('cd '.BASE_DIR.'/interfaces; php event.php > /dev/null 2>&1 &');

exec('cd '.BASE_DIR.'/interfaces; php zigbee.php > /dev/null 2>&1 &');	

$query  = 'select cid from cron where DATE(last_run)!="'.date("Y-m-d").'"'; 
$result = plaatprotect_db_query($query);	
if ($data = plaatprotect_db_fetch_object($result)) {
	
		/* Event handler */
		switch ($data->cid) {
		
			case 1:
				// Delete old webcam recording
				$dir = BASE_DIR.'/webcam/'.date('Y-m-d', strtotime('-30 days'));
				exec('rm -rf '.$dir);
				plaatprotect_db_cron_update($data->cid);
				break;
		}
}

plaatprotect_db_close();

// Calculate to page render time
$time_end = microtime(true);
$time = $time_end - $time_start;

if (DEBUG==1) {
   echo "cron took ".round($time,2)." secs";
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>
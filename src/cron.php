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

define('LOG', 0);

/*
** ---------------------
** CRON
** ---------------------
*/

plaatprotect_db_connect($dbhost, $dbuser, $dbpass, $dbname);

// Run ones
$zwave_present = plaatprotect_db_config_value('zwave_present', CATEGORY_ZWAVE);
if ($zwave_present=="true") {
   exec('cd '.BASE_DIR.'/interfaces;php zwave.php > /dev/null 2>&1 &');
}

$webcam_present_1 = plaatprotect_db_config_value('webcam_present', CATEGORY_WEBCAM_1);
if ($webcam_present_1=="true") {
   exec('cd '.BASE_DIR.'/interfaces; php webcam.php 1 > /dev/null 2>&1 &');	
}

//exec('cd '.BASE_DIR.'/interfaces; php event.php > /dev/null 2>&1 &');

// Run every X minutes
$query  = 'select cid, UNIX_TIMESTAMP(last_run) as last_run, every_x_mins from cron order by cid'; 
$result = plaatprotect_db_query($query);	
while ( $row=plaatprotect_db_fetch_object($result) ) {
	
	
	$value = $row->last_run + ($row->every_x_mins*60);
		
	if ( time() > ($row->last_run+($row->every_x_mins*60))) {
	
		if (LOG == 1) {
			echo "start cron job ".$row->cid."\r\n";
		}

		/* Event handler */
		switch ($row->cid) {
		
			case 1: // Delete old webcam recording
					$dir = BASE_DIR.'/webcam/'.date('Y-m-d', strtotime('-30 days'));
					exec('rm -rf '.$dir);				
					plaatprotect_db_cron_update($data->cid);
					break;
			
			case 2: exec('cd '.BASE_DIR.'/interfaces; php hue_luminance.php > /dev/null 2>&1 &');
					break;
					
			case 3: exec('cd '.BASE_DIR.'/interfaces; php hue_motion.php > /dev/null 2>&1 &');
					break;
					
			case 4: exec('cd '.BASE_DIR.'/interfaces; php hue_temperature.php > /dev/null 2>&1 &');
					break;
					
			case 5: exec('cd '.BASE_DIR.'; php backup.php > /dev/null 2>&1 &');
					break;
		}
		plaatprotect_db_cron_update($row->cid);
	}
	
}

plaatprotect_db_close();

// Calculate to page render time
$time_end = microtime(true);
$time = $time_end - $time_start;

if (LOG == 1) {
   echo "cron took ".round($time,2)." secs\r\n";
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>
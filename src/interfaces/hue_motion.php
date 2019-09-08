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

include "../general.php";
include "../database.php";
include "../config.php";

define('LOG', 0);

/*
** ---------------------
** PARAMETERS
** ---------------------
*/

define( 'LOCK_FILE', "/tmp/".basename( $argv[0], ".php" ).".lock" ); 
if( plaatprotect_islocked() ) die( "Already running.\n" ); 

/**
 ********************************
 * Zigbee functions
 ********************************
 */
    
function plaatprotect_zigbee_get_data($zid) {
	
	$zigbee_ip = plaatprotect_db_config_value('zigbee_ip_address',CATEGORY_ZIGBEE);
 	$zigbee_key = plaatprotect_db_config_value('zigbee_key',CATEGORY_ZIGBEE);
    $zigbee_url = "http://".$zigbee_ip."/api/".$zigbee_key."/sensors/".$zid;
	
	$json = file_get_contents($zigbee_url);
	
	$data = json_decode($json);
	
	if (LOG == 1) {	
		print_r($data);
	}
	
	$value = ($data->state->status);
	
	$enable_motion_alarm = plaatprotect_db_config_value('enable_motion_alarm',CATEGORY_ALARM);
	
	if ($enable_motion_alarm=="true") {
		$timestamp = date('Y-m-d H:i:s');
	
		$sql = 'select value from sensor where zid='.$zid.' order by timestamp desc limit 0,1';	
		$result = plaatprotect_db_query($sql);
		$row = plaatprotect_db_fetch_object($result);
	
		if (!isset($row->value) || ($row->value!=$value)) {
			plaatprotect_db_sensor_insert($zid, $timestamp, $value);
			
			if ($value>0) {
				if (LOG == 1) {	
					echo $zid." alarm ON\r\n";
				}
				plaatprotect_db_event_onramp_insert(CATEGORY_ZIGBEE, '{"zid":'.$zid.', "type":"set", "alarm":"motion"}');
			} else {
				if (LOG == 1) {	
					echo $zid." alarm OFF\r\n";
				}
				plaatprotect_db_event_onramp_insert(CATEGORY_ZIGBEE, '{"zid":'.$zid.', "type":"set", "alarm":"off"}');
			}
			
		} else {
		
			if (LOG == 1) {	
				echo $zid." alarm idle\r\n";
			}
		}
	}
}

/**
 ********************************
 * State Machine
 ********************************
 */
		
plaatprotect_db_connect($dbhost, $dbuser, $dbpass, $dbname);
	
for ($i=0; $i<11; $i++) {	

	if (LOG == 1) {	
		echo "Loop ".$i."\r\n";
	}	
		
	$sql = 'select zid from zigbee where type='.ZIGBEE_TYPE_MOTION;
	$result = plaatprotect_db_query($sql);

	while ($row=plaatprotect_db_fetch_object($result)) {
				
		plaatprotect_zigbee_get_data($row->zid);	
	}
	
	if (LOG == 1) {	
		echo "sleep 5 seconds\r\n";
	}	
	sleep(5);
}

unlink( LOCK_FILE ); 
exit(0); 

/**
 ********************************
 * The End
 ********************************
 */
 
?>

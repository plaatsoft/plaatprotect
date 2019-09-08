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
	
	$enable_temperature_alarm = plaatprotect_db_config_value('enable_temperature_alarm',CATEGORY_ALARM);
	$alarm_high_temperature = plaatprotect_db_config_value('alarm_high_temperature',CATEGORY_ALARM);
	$alarm_low_temperature = plaatprotect_db_config_value('alarm_low_temperature',CATEGORY_ALARM);
	$temperature_alarm_on = plaatprotect_db_config_value('temperature_alarm_on',CATEGORY_GENERAL);
		
	$json = file_get_contents($zigbee_url);	
	$data = json_decode($json);
	
	if (LOG == 1) {	
		print_r($data);
	}
	
	$value = ($data->state->temperature/100);
	
	if ($enable_temperature_alarm) {
	
		if (LOG == 1) {	
			echo $zid."temperature alarm enabled\r\n";
		}
			
		if ($temperature_alarm_on==0) {
			if (($value<=$alarm_low_temperature) || ($value>=$alarm_high_temperature)) {
			
				$config = plaatprotect_db_config("temperature_alarm_on", CATEGORY_GENERAL);
				$config->value = 1;
				plaatprotect_db_config_update($config);
				
				plaatprotect_db_event_onramp_insert(CATEGORY_ZIGBEE, '{"zid":'.$zid.', "type":"set", "alarm":"temperature"}');
				
				if (LOG == 1) {	
					echo $zid."temperature alarm ON\r\n";
				}
		
			} 
		} else if (($value>$alarm_low_temperature) && ($value<$alarm_high_temperature)) {
			
			$config = plaatprotect_db_config("temperature_alarm_on", CATEGORY_GENERAL);
			$config->value = 0;
			plaatprotect_db_config_update($config);
			
			plaatprotect_db_event_onramp_insert(CATEGORY_ZIGBEE, '{"zid":'.$zid.', "type":"set", "alarm":"off"}');
			
			if (LOG == 1) {	
				echo $zid."temperature alarm OFF\r\n";
			}
		} else {
			echo $zid."temperature alarm IN PROGRESS\r\n";
		}
	}

	$timestamp = date('Y-m-d H:i:00');
	
	plaatprotect_db_sensor_insert($zid, $timestamp, $value);
}

/**
 ********************************
 * State Machine
 ********************************
 */
		
plaatprotect_db_connect($dbhost, $dbuser, $dbpass, $dbname);

$sql = 'select zid from zigbee where type='.ZIGBEE_TYPE_TEMPERATURE.' and zid<100';
$result = plaatprotect_db_query($sql);
		
while ($row=plaatprotect_db_fetch_object($result)) {
	plaatprotect_zigbee_get_data($row->zid);
}

unlink( LOCK_FILE ); 
exit(0); 

/**
 ********************************
 * The End
 ********************************
 */
 
?>

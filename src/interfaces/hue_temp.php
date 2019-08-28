<?php

include "../general.php";
include "../database.php";
include "../config.php";

/*
** ---------------------
** PARAMETERS
** ---------------------
*/

define( 'LOCK_FILE', "/tmp/".basename( $argv[0], ".php" ).".lock" ); 
if( plaatprotect_islocked() ) die( "Already running.\n" ); 

/**
 ********************************
 * HUE functions
 ********************************
 */
    
function plaatprotect_store_hue_data($hue_device) {
		
	$hue_ip = plaatprotect_db_config_value('hue_ip_address',CATEGORY_ZIGBEE);
 	$hue_key = plaatprotect_db_config_value('hue_key',CATEGORY_ZIGBEE);
	
    $hue_url = "http://".$hue_ip."/api/".$hue_key."/sensors/".$hue_device;
	
	@$json = file_get_contents($hue_url);
	
	$data = json_decode($json);
	
	print_r($data);
	
	$temperature = ($data->state->temperature/100);
	$timestamp = date('Y-m-d H:i:s');
	$battery = ($data->config->battery);
	
	plaatprotect_db_sensor_insert($hue_device, $timestamp, 0, $temperature, 0, 0, $battery);
}

/**
 ********************************
 * State Machine
 ********************************
 */

plaatprotect_db_connect($dbhost, $dbuser, $dbpass, $dbname);

plaatprotect_store_hue_data(6);
plaatprotect_store_hue_data(10);


unlink( LOCK_FILE ); 
exit(0); 

/**
 ********************************
 * The End
 ********************************
 */
 
?>

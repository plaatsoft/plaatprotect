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
	
	//print_r($data);
	
	$value = ($data->state->lightlevel);
	$timestamp = date('Y-m-d H:i:00');
	
	plaatprotect_db_sensor_insert($zid, $timestamp, $value);
}

/**
 ********************************
 * State Machine
 ********************************
 */
		
plaatprotect_db_connect($dbhost, $dbuser, $dbpass, $dbname);

$sql = 'select zid from zigbee where type='.ZIGBEE_TYPE_LUMINANCE;
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

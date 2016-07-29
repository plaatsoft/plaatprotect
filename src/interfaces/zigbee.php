<?php

include "../general.inc";
include "../database.inc";
include "../config.inc";

/*
** ---------------------
** SETTINGS
** ---------------------
*/

define('DEMO', 0);

/*
** ---------------------
** PARAMETERS
** ---------------------
*/

define( 'LOCK_FILE', "/tmp/".basename( $argv[0], ".php" ).".lock" ); 
if( plaatprotect_islocked() ) die( "Already running.\n" ); 

$sleep = 1000000;
$stop = false;

/**
 ********************************
 * HUE functions
 ********************************
 */
 
function plaatprotect_hue_state($state) {

	if ($state->reachable==1) {
		if ($state->on==1) {
			return HUE_STATE_ON;
		} else {
			return HUE_STATE_OFF;
		}
	} else {
		return HUE_STATE_OFFLINE;
	}
}
	
function plaatprotect_set_hue_state($hid, $value) {
	
 	$hue_ip = plaatprotect_db_config_value('hue_ip_address',CATEGORY_ZIGBEE);
 	$hue_key = plaatprotect_db_config_value('hue_key',CATEGORY_ZIGBEE);
	
   $hue_url = "http://".$hue_ip."/api/".$hue_key."/lights/".$hid."/state";

   $json = @file_get_contents($hue_url, false, stream_context_create(["http" => [
      "method" => "PUT", "header" => "Content-type: application/json",
      "content" => "{\"on\":". $value."}"
    ]]));
	 
	echo $json;
}
    
function plaatprotect_get_hue_state() {
		
 	$hue_ip = plaatprotect_db_config_value('hue_ip_address',CATEGORY_ZIGBEE);
 	$hue_key = plaatprotect_db_config_value('hue_key',CATEGORY_ZIGBEE);
	
   $hue_url = "http://".$hue_ip."/api/".$hue_key."/lights/";
	
	@$json = file_get_contents($hue_url);

	echo $json;
	
	if (DEMO==1) {
	
		$json= '{ "1": { "name": "Livingroom",
				         "type": "Dimmable light",
		               "manufacturername": "Philips",
						   "swversion" : "5.38.15095",
							"state": {
											"reachable" : "1",
											"on": "0"
										}
							},
				"2": { "name": "Kitchen",
				         "type": "Color Light",
		               "manufacturername": "Philips",
						   "swversion" : "5.23.13452",
							"state": {
											"reachable" : "1",
											"on": "1"
										}
						},
				"3": { "name": "Garage",
				         "type": "Dimmable light",
		               "manufacturername": "Philips",
						   "swversion" : "5.38.15095",
							"state": {
											"reachable" : "1",
											"on": "0"
										}
						},
				"4": { "name": "Bedroom Parents",
				        "type": "Dimmable light",
		               "manufacturername": "Philips",
						   "swversion" : "5.38.15095",
							"state": {
											"reachable" : "1",
											"on": "0"
										}
						},
				"5": { "name": "Bedroom Kid 1",
				         "type": "Dimmable light",
		               "manufacturername": "Philips",
						   "swversion" : "5.38.15095",
							"state": {
											"reachable" : "1",
											"on": "0"
										}
						},
				"6": { "name": "Bedroom kid 2",
				         "type": "Color Light",
		               "manufacturername": "Philips",
						   "swversion" : "5.23.13452",
							"state": {
											"reachable" : "0",
											"on": "0"
										}
						},
				"7": { "name": "Bedroom kid 3",
				         "type": "Dimmable light",
		               "manufacturername": "Philips",
						   "swversion" : "5.38.15095",
							"state": {
											"reachable" : "0",
											"on": "0"
										}						
						}
				}';
	}
	
	$data = json_decode($json);
		
	foreach($data as $hid => $bulb ) {

		$row = plaatprotect_db_hue($hid);
		if (isset($row->hid)) {
			
			$row->vendor = $bulb->manufacturername;
			$row->type = $bulb->type;
			$row->version = $bulb->swversion;
			$row->location =  $bulb->name;
			$row->state = plaatprotect_hue_state($bulb->state);
			
			plaatprotect_db_hue_update($row);
			
		} else {
		
			plaatprotect_db_hue_insert($hid, $bulb->manufacturername, $bulb->type, $bulb->swversion, $bulb->name, plaatprotect_hue_state($bulb->state));
		}
	}
}

/**
 ********************************
 * State Machine
 ********************************
 */

function plaatprotect_hue_state_machine() {

	global $sleep;
	
	$row = plaatprotect_db_event(CATEGORY_ZIGBEE);		
	
	if (isset($row->eid)) {

		plaatprotect_log("Hue action event [".$row->action."]");
		$data = json_decode($row->action);
		
		$row->processed=1;
		plaatprotect_db_event_update($row);
		
		if (isset($data->hid)) {
			
			if ($data->action=="set") {
				
				// Set Hue Light bulb state
				plaatprotect_set_hue_state($data->hid, $data->value);
				
			} else if ($data->action=="get") {
				
				// Get Hue light bulb state
				plaatprotect_get_hue_state();
				
			} else if ($data->action=="exit") {
				
				// hue process exit command
				return true;
			}
		}
		
	} else {
	
		// no events waiting sleep X micro seconds
		plaatprotect_log("Hue sleep event");
		usleep($sleep);
	}

	return false;
}

plaatprotect_db_connect($dbhost, $dbuser, $dbpass, $dbname);

plaatprotect_log("HUE engine starting.....");

while ( !$stop ) {

	$stop = plaatprotect_hue_state_machine();
}

plaatprotect_log(" engine stopping.....");

unlink( LOCK_FILE ); 
exit(0); 

/**
 ********************************
 * The End
 ********************************
 */
 
?>
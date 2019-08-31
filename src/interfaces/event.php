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
 * @brief contain event engine
 */
 
include "../general.php";
include "../database.php";
include "../config.php";
include "android.php";
include "email.php";

define('EVENT_ALARM_ON',        10);
define('EVENT_ALARM_OFF',       11);

define('STATE_INIT',        	20);
define('STATE_IDLE',        	21);
define('STATE_ALARM',      		22);

$stop = false;
$state = STATE_INIT;
$sleep = 1000000;
$data = "";
$expire = 0;
$counter = 0;

define( 'LOCK_FILE', "/tmp/".basename( $argv[0], ".php" ).".lock" ); 
if( plaatprotect_islocked() ) die( "Already running.\n" ); 

/*
** ---------------------
** Counter measures
** ---------------------
*/

function plaatprotect_hue_alarm_group($event) {

	$scenario = plaatprotect_db_config_value('alarm_scenario', CATEGORY_GENERAL);
	$panic_on = plaatprotect_db_config_value('panic_on', CATEGORY_GENERAL);

	$sql = 'select aid from actor where type=0 ';

	switch ($scenario) {
	
		case SCENARIO_HOME: 
			$sql .= 'and home=1 ';
			break;
			
		case SCENARIO_SLEEP: 
			$sql .= 'and sleep=1 ';
			break;		
			
		case SCENARIO_AWAY: 
			$sql .= 'and away=1 ';
			break;
	}
	
	if  ($panic_on==1) {
		$sql .= 'or panic=1';
	}
	
	echo $sql;

	$result = plaatprotect_db_query($sql);
	while ($row = plaatprotect_db_fetch_object($result)) {	
		if ($event==EVENT_ALARM_ON) {
			plaatprotect_set_hue_state($row->aid, "true");
		} else {
			plaatprotect_set_hue_state($row->aid, "false");
		}
		plaatprotect_log("Hue Light ".$row->aid.' on');		
	}
}

function plaatprotect_set_hue_state($hid, $value) {
	
 	$hue_ip = plaatprotect_db_config_value('zigbee_ip_address',CATEGORY_ZIGBEE);
 	$hue_key = plaatprotect_db_config_value('zigbee_key',CATEGORY_ZIGBEE);
	
    $hue_url = "http://".$hue_ip."/api/".$hue_key."/lights/".$hid."/state";

    $json = @file_get_contents($hue_url, false, stream_context_create(["http" => [
      "method" => "PUT", "header" => "Content-type: application/json",
      "content" => "{\"on\":". $value."}"
    ]]));
	 
	echo $json;
}


function plaatprotect_zwave_alarm_group($event, $zid=0) {

	$scenario = plaatprotect_db_config_value('alarm_scenario', CATEGORY_GENERAL);
	$panic_on = plaatprotect_db_config_value('panic_on', CATEGORY_GENERAL);

	$sql = 'select zid from zwave where ';

	switch ($scenario) {
	
		case SCENARIO_HOME: 
			$sql .= '(home=1 and type="Sirene") ';
			break;
			
		case SCENARIO_SLEEP: 
			$sql .= '(sleep=1 and type="Sirene") ';
			break;		
			
		case SCENARIO_AWAY: 
			$sql .= '(away=1 and type="Sirene")';
			break;
	}

	if  ($panic_on==1) {
		$sql .= 'or (panic=1 and type="Sirene")';
	}
	
	$result = plaatprotect_db_query($sql);
	while ($row = plaatprotect_db_fetch_object($result)) {	
		if ($event==EVENT_ALARM_ON) {
			$command = '{"zid":'.$row->zid.', "action":"sirene", "value":"on"}';
		} else {
			$command = '{"zid":'.$row->zid.', "action":"sirene", "value":"off"}';
		}		
		plaatprotect_log("Outbound zwave event: ".$command);
		plaatprotect_db_event_insert(CATEGORY_ZWAVE_CONTROL, $command);		
	}
}

function plaatprotect_mobile_alarm_group($event, $zid=0) {

	$scenario = plaatprotect_db_config_value('alarm_scenario', CATEGORY_GENERAL);
	$panic_on = plaatprotect_db_config_value('panic_on', CATEGORY_GENERAL);

	$sql = 'select aid from actor ';

	switch ($scenario) {
	
		case SCENARIO_HOME: 
			$sql .= 'where (home=1 and type=101) ';
			break;
			
		case SCENARIO_SLEEP: 
			$sql .= 'where (sleep=1 and type=101) ';
			break;		
			
		case SCENARIO_AWAY: 
			$sql .= 'where (away=1 and type=101) ';
			break;
	}
	
	if  ($panic_on==1) {
		$sql .= 'or (panic=1 and type=1)';
	}
	
	$result = plaatprotect_db_query($sql);
   
	while ($row = plaatprotect_db_fetch_object($result)) {
	
		// Notication to mobile
		$subject =  "PlaatProtect Alarm";
		
		$body ="Alarm Location=";
		$data = plaatprotect_db_zigbee($zid);
		if ( isset($data->location) ) {
			$body .= $data->location.' ';
		} else {
			$body .= 'Unknown ';
		}
			
		if ($event==EVENT_ALARM_ON) {
			$body .= 'Event=on';
		} else {
			$body .= 'Event=off';
		}
	
		plaatprotect_log("Outbound mobile event: ".$body);
		plaatprotect_mobile_notification($subject, $body, 2);
	}
}

function plaatprotect_email_alarm_group($event, $zid=0) {

	$scenario = plaatprotect_db_config_value('alarm_scenario', CATEGORY_GENERAL);
	$panic_on = plaatprotect_db_config_value('panic_on', CATEGORY_GENERAL);

	$sql = 'select aid from actor ';

	switch ($scenario) {
	
		case SCENARIO_HOME: 
			$sql .= 'where (home=1 and type=102) ';
			break;
			
		case SCENARIO_SLEEP: 
			$sql .= 'where (sleep=1 and type=102) ';
			break;		
			
		case SCENARIO_AWAY: 
			$sql .= 'where (away=1 and type=102) ';
			break;
	}
	
	if  ($panic_on==1) {
		$sql .= 'or (panic=1 and type=102)';
	}
	
	$result = plaatprotect_db_query($sql);
   
	while ($row = plaatprotect_db_fetch_object($result)) {
	
		// Notication to mobile
		$subject =  "PlaatProtect Alarm";
		$body = "Alarm Location=";
	
		$data = plaatprotect_db_zwave($zid);
		if ( isset($data->location) ) {
			$body .= $data->location.' ';
		} else {
			$body .= 'Unknown ';
		}
		
		if ($event==EVENT_ALARM_ON) {
			$body .= 'Event=on';
		} else {
			$body .= 'Event=off';
		}
	
		plaatprotect_log("Outbound email event: ".$body);
		plaatprotect_email_notification($subject, $body);
	}
}

/*
** ---------------------
** Actions
** ---------------------
*/

function plaatprotect_zwave_alive($data) {

	if (isset($data->zid)) {
		plaatprotect_db_zwave_alive($data->zid);
	}
}

function plaatprotect_zwave_vendor($data) {
		
	if (isset($data->zid) && isset($data->vendor) && isset($data->device)) {
			
		$zwave = plaatprotect_db_zwave($data->zid);
		$zwave->vendor = $data->vendor;
		$zwave->type = $data->device;
		
		plaatprotect_db_zwave_update($zwave);
	}
}	
	
function plaatprotect_zwave_sensor($data) {
	
	if (isset($data->type) && ($data->type=="report")) {
			
		$timestamp = date('Y-m-d H:i:00');
			
		$sensor = plaatprotect_db_sensor_last($data->zid, $timestamp);
		if (isset($sensor->sid)) {
			
			if (isset($data->luminance)) {
				$sensor->luminance = $data->luminance;
			}
			
			if (isset($data->temperature)) {
				$sensor->temperature = $data->temperature;
			}
			
			if (isset($data->humidity)) {
				$sensor->humidity = $data->humidity;
			}
			
			if (isset($data->ultraviolet)) {
				$sensor->ultraviolet = $data->ultraviolet;
			}
			
			if (isset($data->battery)) {
				$sensor->battery = $data->battery;
			}
				
			plaatprotect_db_sensor_update($sensor);
			
		} else {
			
			$luminance=0;
			$temperature=0;
			$humidity=0;
			$ultraviolet=0;
			$battery=0;
			
			if (isset($data->luminance)) {
				$luminance = $data->luminance;
			}
			
			if (isset($data->temperature)) {
				$temperature = $data->temperature;
			}
			
			if (isset($data->humidity)) {
				$humidity = $data->humidity;
			}
			
			if (isset($data->ultraviolet)) {
				$ultraviolet = $data->ultraviolet;
			}
			
			if (isset($data->battery)) {
				$battery = $data->battery;
			}
			
			plaatprotect_db_sensor_insert($data->zid, $timestamp, $luminance, $temperature, $humidity, $ultraviolet, $battery);	
		}
	}
}
	
function plaatprotect_alarm_on($data) {

	$expire=0;
	
	if (isset($data->type) && ($data->type=="set")) {
		if (($data->alarm=="motion" || $data->alarm=="vibration")) {
							
			$duration = plaatprotect_db_config_value("alarm_duration", CATEGORY_GENERAL);
			$expire = time() + $duration;
		}
	}
	
	return $expire;
}

function plaatprotect_manual_panic($data) {

	global $expire;
	
	if ( (isset($data->action)) && ($data->action=="panic")) {
	
		if ($data->value=="on") {
				
			$duration = plaatprotect_db_config_value("alarm_duration", CATEGORY_GENERAL);
			$expire = time() + $duration;
			
		} else {
		
			// Swith alarm off
			$expire = time();
		}
	}
}
		
/*
** ---------------------
** State machine
** ---------------------
*/

function plaatprotect_event_init() {
	
	global $state;
	
	plaatprotect_log("StateMachine = Init");
	
	// Hue
	//$event = '{"hid":"all", "action":"get", "value":"init"}';
	//plaatprotect_db_event_insert(CATEGORY_ZIGBEE, $event);		

	//$sql = 'select zid from zigbee ';
	//$result = plaatprotect_db_query($sql);
	//while ($row = plaatprotect_db_fetch_object($result)) {	
	//	$command = '{"zid":'.$row->zid.', "action":"set", "value":"false"}';
	//	plaatprotect_log("Outbound zigbee event: ".$command);
	//	plaatprotect_db_event_insert(CATEGORY_ZIGBEE, $command);		
	//}
		
	//plaatprotect_zwave_alarm_group(EVENT_ALARM_OFF);
	
	$subject =  "INFO";
	$body =  "Event process (re)start!";
	plaatprotect_log("Outbound mobile event: ".$body);
	plaatprotect_mobile_notification($subject, $body, 0);
	
	$state = STATE_IDLE;
}

function plaatprotect_event_idle() {

	global $sleep;
	global $state;
	global $expire;
	
	plaatprotect_log("StateMachine = Idle");
	
	/*$row = plaatprotect_db_event(CATEGORY_ZWAVE);			
	if (isset($row->eid)) {

		plaatprotect_log("Inbound zwave event: ".$row->action);
	
		$data = json_decode($row->action);
		
		plaatprotect_zwave_alive($data);		
		plaatprotect_zwave_vendor($data);		
		plaatprotect_zwave_sensor($data);		
		plaatprotect_alarm_on($data);
		plaatprotect_manual_panic($data);
	
		if (($expire-time())>0) {
	
			$state = STATE_ALARM;
		
			$zid = 0;
			if (isset($data->zid)) {
				$zid = $data->zid;
			}
			
			plaatprotect_hue_alarm_group(EVENT_ALARM_ON);			
			plaatprotect_zwave_alarm_group(EVENT_ALARM_ON, $zid);
			plaatprotect_mobile_alarm_group(EVENT_ALARM_ON, $zid);
			plaatprotect_email_alarm_group(EVENT_ALARM_ON, $zid);
		}
	
		$row->processed=1;
		plaatprotect_db_event_update($row);
	}*/
	
	$row = plaatprotect_db_event(CATEGORY_ZIGBEE);			
	if (isset($row->eid)) {

		plaatprotect_log("Inbound zigbee event: ".$row->action);
	
		$data = json_decode($row->action);
	
		$expire=plaatprotect_alarm_on($data);
	
		if ($expire>0) {
	
			$state = STATE_ALARM;
		
			$zid = 0;
			if (isset($data->zid)) {
				$zid = $data->zid;
			}
			
			plaatprotect_hue_alarm_group(EVENT_ALARM_ON);			
			plaatprotect_zwave_alarm_group(EVENT_ALARM_ON, $zid);
			plaatprotect_mobile_alarm_group(EVENT_ALARM_ON, $zid);
			plaatprotect_email_alarm_group(EVENT_ALARM_ON, $zid);
		}
	
		$row->processed=1;
		plaatprotect_db_event_update($row);
	
	} else {
		usleep($sleep);
	}
}
 
function plaatprotect_event_alarm() {

	global $sleep;
	global $state;
	global $expire;
	
	plaatprotect_log("StateMachine = Alarm [".($expire-time())." sec]");

	/*$row = plaatprotect_db_event(CATEGORY_ZWAVE);			
	if (isset($row->eid)) {

		plaatprotect_log("Inbound zwave event: ".$row->action);
		
		$data = json_decode($row->action);
		
		plaatprotect_zwave_alive($data);
		plaatprotect_zwave_vendor($data);
		plaatprotect_zwave_sensor($data);
		plaatprotect_alarm_on($data);
		plaatprotect_manual_panic($data);
				
		$row->processed=1;
		plaatprotect_db_event_update($row);
		
	} else {
		usleep($sleep);
	}*/	
		
	if (($expire-time())<=0) {
	
		$state = STATE_IDLE;
		
		plaatprotect_hue_alarm_group(EVENT_ALARM_OFF);	
		plaatprotect_zwave_alarm_group(EVENT_ALARM_OFF);
		plaatprotect_mobile_alarm_group(EVENT_ALARM_OFF);
		plaatprotect_email_alarm_group(EVENT_ALARM_OFF);
    }
	else {
		usleep($sleep);
	}
}

function plaatprotect_event_state_machine() {

	global $state;
	global $counter;
	
	$counter++;
	if ($counter > (60*15)) {
	
		$command = '{"zid":0, "action":"reset"}';
		plaatprotect_log("Outbound zwave event: ".$command);
		plaatprotect_db_event_insert(CATEGORY_ZWAVE_CONTROL, $command);	
		
		$counter=0;
	}
	
	$stop = false;
	switch ($state) {
	
		case STATE_INIT: 
		      plaatprotect_event_init();
				break;
				
		case STATE_IDLE: 
		      plaatprotect_event_idle();
				break;
				
		case STATE_ALARM:
				plaatprotect_event_alarm();
				break;
				
		default: 
				plaatprotect_log("Error: unknown state");
				$stop = true;
				break;
	}
	
	return $stop;
}

/*
** ---------------------
** Main
** ---------------------
*/

plaatprotect_db_connect($dbhost, $dbuser, $dbpass, $dbname);

plaatprotect_log("Event engine starting.....");

while ( !$stop ) {

	$stop = plaatprotect_event_state_machine();
}

plaatprotect_log("Event engine stopping.....");

unlink( LOCK_FILE ); 
exit(0); 

/*
** ---------------------
** The End
** ---------------------
*/

?>
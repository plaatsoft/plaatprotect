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

include "android_notification.php";
include "email_notification.php";

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

define( 'LOCK_FILE', "/tmp/".basename( $argv[0], ".php" ).".lock" ); 
if( plaatprotect_islocked() ) die( "Already running.\n" ); 

/*
** ---------------------
** Counter measures
** ---------------------
*/

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

/*
** ---------------------
** Actions
** ---------------------
*/

function plaatprotect_alarm_off($data) {

	$expire=0;
	
	if (isset($data->type) && ($data->type=="set")) {
		if ($data->alarm=="off")  {
							
			return true;
		}
	}
	return false;
}

	
function plaatprotect_alarm_on($data) {

	if (isset($data->type) && ($data->type=="set")) {
		if (($data->alarm=="motion" || $data->alarm=="vibration")) {
			return true;
		}
	}
	return false;
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

function plaatprotect_event_idle() {

	global $sleep;
	global $state;
	global $expire;
	
	plaatprotect_log("StateMachine = Idle");
		
	$row = plaatprotect_db_event(CATEGORY_ZIGBEE, CATEGORY_ZWAVE);				
	if (isset($row->eid)) {

		plaatprotect_log("Inbound action: ".$row->action);	
		$data = json_decode($row->action);
		
		if (plaatprotect_alarm_on($data)) {
	
			$expire = time() + plaatprotect_db_config_value("alarm_duration", CATEGORY_GENERAL);		
			$state = STATE_ALARM;
			$zid = $data->zid;
			
			plaatprotect_email_alarm_group(EVENT_ALARM_ON, $zid);
			
			//plaatprotect_hue_alarm_group(EVENT_ALARM_ON);			
			//plaatprotect_zwave_alarm_group(EVENT_ALARM_ON, $zid);
			//plaatprotect_mobile_alarm_group(EVENT_ALARM_ON, $zid);
		}
	
		$row->processed=1;
		plaatprotect_db_event_update($row);
	
	} else {
		plaatprotect_log("sleep");	
		usleep($sleep);
	}
}
 
function plaatprotect_event_alarm() {

	global $sleep;
	global $state;
	global $expire;
	
	plaatprotect_log("StateMachine = Alarm [".($expire-time())." sec]");
	
	$row = plaatprotect_db_event(CATEGORY_ZIGBEE, CATEGORY_ZWAVE);				
	if (isset($row->eid)) {

		plaatprotect_log("Inbound action: ".$row->action);	
		$data = json_decode($row->action);
		
		if (plaatprotect_alarm_on($data)) {
	
			$expire = time() + plaatprotect_db_config_value("alarm_duration", CATEGORY_GENERAL);		
			$state = STATE_ALARM;
		}
		
		if (plaatprotect_alarm_off($data)) {
	
			$state = STATE_IDLE;
			$zid = $data->zid;
			
			plaatprotect_email_alarm_group(EVENT_ALARM_OFF, $zid);
			
			//plaatprotect_hue_alarm_group(EVENT_ALARM_OFF);			
			//plaatprotect_zwave_alarm_group(EVENT_ALARM_OFF, $zid);
			//plaatprotect_mobile_alarm_group(EVENT_ALARM_OFF, $zid);
		}
	
		$row->processed=1;
		plaatprotect_db_event_update($row);
		return;
	}
		
	if (($expire-time())<=0) {
	
		$state = STATE_IDLE;
		
		//plaatprotect_hue_alarm_group(EVENT_ALARM_OFF);	
		//plaatprotect_zwave_alarm_group(EVENT_ALARM_OFF);
		//plaatprotect_mobile_alarm_group(EVENT_ALARM_OFF);
		plaatprotect_email_alarm_group(EVENT_ALARM_OFF);
    }
	else {
		plaatprotect_log("sleep");	
		usleep($sleep);
	}
}


function plaatprotect_event_init() {
	
	global $state;
	
	plaatprotect_log("StateMachine = Init");
	
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


function plaatprotect_event_state_machine() {

	global $state;

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
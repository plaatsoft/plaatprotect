<?php

include "general.inc";
include "database.inc";
include "config.inc";
include "interfaces/zigbee.php";
include "interfaces/android.php";

define('EVENT_IDLE',        		10);
define('EVENT_ALARM_ON',   		11);
define('EVENT_ALARM_OFF', 	  		12);

define('STATE_INIT',        		20);
define('STATE_IDLE',        		21);
define('STATE_ALARM',      		22);

$stop = false;
$state = STATE_INIT;
$sleep = 1000000;
$data = "";
$expire= 0;

define( 'LOCK_FILE', "/tmp/".basename( $argv[0], ".php" ).".lock" ); 
if( plaatprotect_islocked() ) die( "Already running.\n" ); 

/**
 ********************************
 * Counter measures
 ********************************
 */

function plaatprotect_hue_alarm_group($event) {

	$scenario = plaatprotect_db_config_value('alarm_scenario', CATEGORY_GENERAL);

	switch ($scenario) {
	
		case SCENARIO_HOME: 
			$sql = 'select hid from hue where home=1';
			break;
			
		case SCENARIO_SLEEP: 
			$sql = 'select hid from hue where sleep=1';
			break;		
			
		case SCENARIO_AWAY: 
			$sql = 'select hid from hue where away=1';
			break;
	}

	$result = plaatprotect_db_query($sql);
	while ($row = plaatprotect_db_fetch_object($result)) {	
		if ($event==EVENT_ALARM_ON) {
			plaatprotect_set_hue($row->hid, "true");
		} else {
			plaatprotect_set_hue($row->hid, "false");
		}
	}
}

function plaatprotect_mobile_alarm_group($event, $zid=0) {

	$scenario = plaatprotect_db_config_value('alarm_scenario', CATEGORY_GENERAL);

	switch ($scenario) {
	
		case SCENARIO_HOME: 
			$sql = 'select nid from notification where home=1 and type=1';
			break;
			
		case SCENARIO_SLEEP: 
			$sql = 'select nid from notification where sleep=1 and type=1';
			break;		
			
		case SCENARIO_AWAY: 
			$sql = 'select nid from notification where away=1 and type=1';
			break;
	}
	
	$result = plaatprotect_db_query($sql);
   
	while ($row = plaatprotect_db_fetch_object($result)) {
	
		// Notication to mobile
		$subject =  "Alarm";
		$body = "Event=";
	
		if ($event==EVENT_ALARM_ON) {
			$body .= 'on';
		} else {
			$body .= 'off';
		}
		
		$data = plaatprotect_db_zwave($zid);
		if ( isset($data->location) ) {
			$body .= ' '.$data->location;
		}
		
		plaatprotect_mobile_notification($subject, $body, 2);
	}
}

function plaatprotect_zwave_alarm_group($event,$zid=0) {

	$scenario = plaatprotect_db_config_value('alarm_scenario', CATEGORY_GENERAL);

	switch ($scenario) {
	
		case SCENARIO_HOME: 
			$sql = 'select zid from zwave where home=1 and type="Sirene"';
			break;
			
		case SCENARIO_SLEEP: 
			$sql = 'select zid from zwave where sleep=1 and type="Sirene"';
			break;		
			
		case SCENARIO_AWAY: 
			$sql = 'select zid from zwave where away=1 and type="Sirene"';
			break;
	}

	$result = plaatprotect_db_query($sql);
	while ($row = plaatprotect_db_fetch_object($result)) {	
		if ($event==EVENT_ALARM_ON) {
			$event = '{"nodeid":'.$row->zid.', "action":"sirene", "value":"on"}';
		} else {
			$event = '{"nodeid":'.$row->zid.', "action":"sirene", "value":"off"}';
		}
		plaatprotect_event_insert(CATEGORY_ZWAVE_CONTROL, $event);		
	}
}

/**
 ********************************
 * State machine
 ********************************
 */

function plaatprotect_event_init() {
	
	global $state;
	
	plaatprotect_log("StateMachine = Init");
	
	plaatprotect_hue_all_off();
	plaatprotect_zwave_alarm_group(EVENT_ALARM_OFF);
	
	$subject =  "INFO";
	$body =  "Event process (re)start!";
	plaatprotect_mobile_notification($subject, $body, 0);
	
	$state = STATE_IDLE;
}

function plaatprotect_event_idle() {

	global $sleep;
	global $state;
	global $expire;
	
	plaatprotect_log("StateMachine = Idle");
	
	$row = plaatprotect_db_event(CATEGORY_ZWAVE);			
	if (isset($row->eid)) {
	
		$data = json_decode($row->action);
		plaatprotect_log("Event found!");
		
		if (isset($data->alarm)) {
			if (($data->alarm=="motion" || $data->alarm=="vibration")) {
				$state = STATE_ALARM;
				$duration = plaatprotect_db_config_value("alarm_duration", CATEGORY_GENERAL);
				$expire = time() + $duration;
				
				plaatprotect_hue_alarm_group(EVENT_ALARM_ON);
				plaatprotect_mobile_alarm_group(EVENT_ALARM_ON, $data->nodeid);
				plaatprotect_zwave_alarm_group(EVENT_ALARM_ON, $data->nodeid);
			}
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

	$row = plaatprotect_db_event(CATEGORY_ZWAVE);			
	if (isset($row->eid)) {
		
		$data = json_decode($row->action);
		if (isset($data->alarm)) {
			if (($data->alarm=="motion" || $data->alarm=="vibration")) {
				$duration = plaatprotect_db_config_value("alarm_duration", CATEGORY_GENERAL);
				$expire = time() + $duration;
			}
		}
		
		$row->processed=1;
		plaatprotect_db_event_update($row);
	}
	
	if (($expire-time())<=0) {
	
		$state = STATE_IDLE;
		
		plaatprotect_hue_alarm_group(EVENT_ALARM_OFF);
		plaatprotect_mobile_alarm_group(EVENT_ALARM_OFF);
		plaatprotect_zwave_alarm_group(EVENT_ALARM_OFF);

	} else {
		usleep($sleep);
	}
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

/**
 ********************************
 * Database
 ********************************
 */
 
plaatprotect_db_connect($dbhost, $dbuser, $dbpass, $dbname);

plaatprotect_log("Event engine starting.....");

while ( !$stop ) {

	$stop = plaatprotect_event_state_machine();
}

plaatprotect_log("Event engine stopping.....");

unlink( LOCK_FILE ); 
exit(0); 

?>
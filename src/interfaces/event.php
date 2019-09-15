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
include "hue_notification.php";

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
$alarm_device_id = 0;

define( 'LOCK_FILE', "/tmp/".basename( $argv[0], ".php" ).".lock" ); 
if( plaatprotect_islocked() ) die( "Already running.\n" ); 

/*
** ---------------------
** Utils
** ---------------------
*/

function plaatprotect_alarm_off($data) {

	$expire=0;
	
	// Only human can switch off alarm
	if (isset($data->type) && ($data->type=="set") && $data->zid==0) {
		if ($data->alarm=="off")  {
			return true;
		}
	}
	return false;
}

	
function plaatprotect_alarm_on($data) {

	if (isset($data->type) && ($data->type=="set")) {
		if (($data->alarm=="motion" || $data->alarm=="vibration" || $data->alarm=="temperature" || $data->alarm=="panic")) {
			return true;
		}
	}
	return false;
}

/*
** ---------------------
** State machine
** ---------------------
*/

function plaatprotect_event_alarm() {

	global $sleep;
	global $state;
	global $expire;
	global $alarm_device_id;
	
	plaatprotect_log("StateMachine = Alarm [".($expire-time())." sec]");
	
	$row = plaatprotect_db_event_onramp_oldest();				
	if (isset($row->eid)) {

		plaatprotect_db_event_offramp_insert($row->category, $row->action, $row->timestamp);
		plaatprotect_db_event_onramp_delete($row->eid);		
		
		plaatprotect_log($row->action);	
		$message = json_decode($row->action);
		
		// New alarm, reset count down timer
		if (plaatprotect_alarm_on($message)) {
	
			$expire = time() + plaatprotect_db_config_value("alarm_duration", CATEGORY_ALARM);		
			$alarm_device_id = $message->zid;
		}
		
		// Human disable alarm, switch alarm off
		if (plaatprotect_alarm_off($message)) {
	
			$state = STATE_IDLE;

			plaatprotect_email_alarm_group(EVENT_ALARM_OFF, $message);			
			plaatprotect_hue_alarm_group(EVENT_ALARM_OFF);			
			//plaatprotect_zwave_alarm_group(EVENT_ALARM_OFF, $zid);
			//plaatprotect_mobile_alarm_group(EVENT_ALARM_OFF, $zid);
		}
		return;
	}
	
	// Alarm period expired, switch alarm off	
	if (($expire-time())<=0) {
	
		$state = STATE_IDLE;
		
		$json = '{"zid":"'.$alarm_device_id.'", "type":"set", "alarm":"off"}';
		$message = json_decode($json);
		
		plaatprotect_email_alarm_group(EVENT_ALARM_OFF, $message);
		plaatprotect_hue_alarm_group(EVENT_ALARM_OFF);			
		//plaatprotect_zwave_alarm_group(EVENT_ALARM_OFF, $zid);
		//plaatprotect_mobile_alarm_group(EVENT_ALARM_OFF, $zid);
	
    }
	else {
		usleep($sleep);
	}
}

function plaatprotect_event_idle() {

	global $sleep;
	global $state;
	global $expire;
	global $alarm_device_id;
	
	plaatprotect_log("StateMachine = Idle");
		
	$row = plaatprotect_db_event_onramp_oldest();				
	if (isset($row->eid)) {

		plaatprotect_db_event_offramp_insert($row->category, $row->action, $row->timestamp);
		plaatprotect_db_event_onramp_delete($row->eid);		
		
		plaatprotect_log($row->action);	
		$message = json_decode($row->action);
		
		// Alarm detected, enable alarm
		if (plaatprotect_alarm_on($message)) {
	
			$expire = time() + plaatprotect_db_config_value("alarm_duration", CATEGORY_ALARM);		
			$state = STATE_ALARM;
			$alarm_device_id = $message->zid;
						
			plaatprotect_email_alarm_group(EVENT_ALARM_ON, $message);			
			plaatprotect_hue_alarm_group(EVENT_ALARM_ON);	
			//plaatprotect_zwave_alarm_group(EVENT_ALARM_ON, $zid);
			//plaatprotect_mobile_alarm_group(EVENT_ALARM_ON, $zid);
		}
	} else {
		usleep($sleep);
	}
}
 
function plaatprotect_event_init() {
	
	global $state;
	
	plaatprotect_log("StateMachine = Init");
	
	$event = '{"message":"event process (re)start"}';		
	plaatprotect_db_event_offramp_insert(CATEGORY_GENERAL, $event);
		
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
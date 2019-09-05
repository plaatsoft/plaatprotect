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
 
function plaatprotect_email_notification($subject, $body, $alarm) {

	$email_present = plaatprotect_db_config_value('email_present', CATEGORY_EMAIL);

	if ($email_present=="true" ) {
		$email = plaatprotect_db_config_value('email_address', CATEGORY_EMAIL);
		
		$header  = "From: PlaatProtect\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	
		if (mail($email, $subject, $body, $header)) {			
			$event = '{"email":"delivered", "alarm":"'.$alarm.'"}';
		} else {		
			$event = '{"email":"failed", "alarm":"'.$alarm.'"}';
		}
		plaatprotect_db_event_offramp_insert(CATEGORY_EMAIL, $event);
		
	} else {
		plaatprotect_log('Outbound email disabled');
	}
}

function plaatprotect_email_alarm_group($event, $zid=0) {

	$scenario = plaatprotect_db_config_value('alarm_scenario', CATEGORY_GENERAL);
	$panic_on = plaatprotect_db_config_value('panic_on', CATEGORY_GENERAL);

	$sql = 'select aid from actor ';

	switch ($scenario) {
	
		case SCENARIO_HOME: 
			$sql .= 'where (home=1 and type='.ACTOR_TYPE_EMAIL.') ';
			break;
			
		case SCENARIO_SLEEP: 
			$sql .= 'where (sleep=1 and type='.ACTOR_TYPE_EMAIL.') ';
			break;		
			
		case SCENARIO_AWAY: 
			$sql .= 'where (away=1 and type='.ACTOR_TYPE_EMAIL.') ';
			break;
	}
	
	if  ($panic_on==1) {
		$sql .= 'or (panic=1 and type='.ACTOR_TYPE_EMAIL.')';
	}
		
	$result = plaatprotect_db_query($sql);
	$row = plaatprotect_db_fetch_object($result);
	
	if (isset($row)) {
	
		$systemName = plaatprotect_db_config_value('system_name', CATEGORY_GENERAL);

		// Notication to mobile
		$subject =  "PlaatProtect Alarm ";

		if ($event==EVENT_ALARM_ON) {
			$subject .= "On ";
		} else {
			$subject .= "Off ";
		}
		
		$subject .= $systemName;
		
		$body  = "<html>";
		$body .= "<body>";
		$body .= "<h1>".$subject.'</h1>';
				
		$body .= "<p>";
		$body .= "Location=";	
		$data = plaatprotect_db_zigbee($zid);
		if ( isset($data->location) ) {
			$body .= $data->location;
		} else {
			$body .= "Unknown";
		}
		$body .= "</p>";
		
		$body .= "<p>";
		if ($event==EVENT_ALARM_ON) {
			$body .= "Alarm=on\r\n";
			$alarm = "on";
		} else {
			$body .= "Alarm=off\r\n";
			$alarm = "off";
		}
		$body .= "</p>";
		$body .= "</html>";
	
		plaatprotect_email_notification($subject, $body, $alarm);
	}
}

?>
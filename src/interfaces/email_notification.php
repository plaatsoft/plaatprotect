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
 
function plaatprotect_email_notification($subject, $body) {

	$email_present = plaatprotect_db_config_value('email_present', CATEGORY_EMAIL);

	if ($email_present=="true" ) {
		$email = plaatprotect_db_config_value('email_address', CATEGORY_EMAIL);
		
		$header  = "From: PlaatProtect\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	
		if (mail($email, $subject, $body, $header)) {			
			$event = '{"email":"delivered"}';
		} else {		
			$event = '{"email":"failed"}';
		}
		plaatprotect_db_event_offramp_insert(CATEGORY_EMAIL, $event);
		
	} else {
		plaatprotect_log('Outbound email disabled');
	}
}

function plaatprotect_email_alarm_group($event, $message) {

	$scenario = plaatprotect_db_config_value('alarm_scenario', CATEGORY_GENERAL);

	$sql = 'select aid from actor where type='.ACTOR_TYPE_EMAIL.' and ';

	switch ($scenario) {
	
		case SCENARIO_HOME: 
			$sql .= 'home=1';
			break;
			
		case SCENARIO_SLEEP: 
			$sql .= 'sleep=1';
			break;		
			
		case SCENARIO_AWAY: 
			$sql .= 'away=1';
			break;
			
		case SCENARIO_PANIC: 
			$sql .= 'panic=1';
			break;
	}
		
	$result = plaatprotect_db_query($sql);
	$row = plaatprotect_db_fetch_object($result);
	
	if (isset($row)) {
	
		$systemName = plaatprotect_db_config_value('system_name', CATEGORY_GENERAL);

		// Notication to mobile
		$subject =  "PlaatProtect Alarm ".$message->alarm.' '.$systemName;
		
		$body  = "<html>";
		$body .= "<body>";
		$body .= "<h1>".$subject.'</h1>';
				
		$body .= "<p>";
		$body .= "Device Id=".$message->zid;
		$body .= "</p>";
		
		$body .= "<p>";
		$body .= "Location=";	
		$data = plaatprotect_db_zigbee($message->zid);
		if ( isset($data->location) ) {
			$body .= $data->location;
		} else {
			$body .= "Unknown";
		}
		$body .= "</p>";
		
		$body .= "<p>";
		$body .= "Alarm=".$message->alarm."\r\n";
		$body .= "</p>";
		$body .= "</html>";
	
		plaatprotect_email_notification($subject, $body);
	}
}

?>
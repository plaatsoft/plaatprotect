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
 * @brief contain android push logic
 */

/*
** -----------
** NOTIFCATION
** serverity
** 2 = top
** 1 = high
** 0 = normal
** -1 = low
** -2 = none
** -----------
*/

function plaatprotect_mobile_notification($topic, $content, $severity=0) {

  $mobile_present = plaatprotect_db_config_value('mobile_present', CATEGORY_MOBILE);

  if ($mobile_present=="true" ) {
  
		$nma_key = plaatprotect_db_config_value('mobile_nma_key', CATEGORY_MOBILE);

		require_once 'nmaApi.class.php';

		$nma = new nmaApi(array('apikey' => $nma_key));
		if($nma->verify()) {
			$nma->notify('PlaatProtect', $topic, $content, $severity );
			plaatprotect_log("Android push message sent!");
		} else {
			plaatprotect_log("Android push message failed!");
		}
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

?>
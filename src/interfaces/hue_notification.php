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

function plaatprotect_set_hue_state($hid, $value) {
	
 	$hue_ip = plaatprotect_db_config_value('zigbee_ip_address',CATEGORY_ZIGBEE);
 	$hue_key = plaatprotect_db_config_value('zigbee_key',CATEGORY_ZIGBEE);
	
    $hue_url = "http://".$hue_ip."/api/".$hue_key."/lights/".$hid."/state";

    $json = @file_get_contents($hue_url, false, stream_context_create(["http" => [
      "method" => "PUT", "header" => "Content-type: application/json",
      "content" => "{\"on\":". $value."}"
    ]]));
	 
	//echo $json;
}

function plaatprotect_hue_alarm_group($event) {

	$scenario = plaatprotect_db_config_value('alarm_scenario', CATEGORY_GENERAL);
	$panic_on = plaatprotect_db_config_value('panic_on', CATEGORY_GENERAL);

	$sql = 'select aid from actor where (type='.ACTOR_TYPE_BULB.' ';

	switch ($scenario) {
	
		case SCENARIO_HOME: 
			$sql .= 'and home=1) ';
			break;
			
		case SCENARIO_SLEEP: 
			$sql .= 'and sleep=1) ';
			break;		
			
		case SCENARIO_AWAY: 
			$sql .= 'and away=1) ';
			break;
	}
	
	if  ($panic_on==1) {
		$sql .= 'or (panic=1 and type='.ACTOR_TYPE_BULB.') ';
	}
	
	$result = plaatprotect_db_query($sql);
	while ($row = plaatprotect_db_fetch_object($result)) {	
		if ($event==EVENT_ALARM_ON) {
			plaatprotect_set_hue_state($row->aid, "true");
			plaatprotect_log("Hue Light ".$row->aid.' on');		
		} else {
			plaatprotect_set_hue_state($row->aid, "false");
			plaatprotect_log("Hue Light ".$row->aid.' off');		
		}		
	}
}

/**
 ********************************
 * The End
 ********************************
 */
 
?>



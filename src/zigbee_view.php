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

/*
** ---------------------
** ACTION
** ---------------------
*/

function plaatprotect_refresh_actor_configuration() {
		
 	$zigbee_ip = plaatprotect_db_config_value('zigbee_ip_address',CATEGORY_ZIGBEE);
 	$zigbee_key = plaatprotect_db_config_value('zigbee_key',CATEGORY_ZIGBEE);	
    $zigbee_url = "http://".$zigbee_ip."/api/".$zigbee_key."/sensors/";
	
	@$json = file_get_contents($zigbee_url);
	
	$data = json_decode($json);
		
	$location = "";
		
	foreach($data as $zid => $sensor ) {

		$type = ZIGBEE_TYPE_UNKNOWN;
		
		if ($sensor->type=="ZLLPresence") {
			$type = ZIGBEE_TYPE_BATTERY;
			$location =  $sensor->name;
		}
		
		if ($sensor->type=="ZLLTemperature") {
			$type = ZIGBEE_TYPE_TEMPERATURE;
		}
		
		if ($sensor->type=="ZLLLightLevel") {
			$type = ZIGBEE_TYPE_LUMINANCE;			
		}
		
		if ($sensor->type=="ZLLSwitch") {
			$type = ZIGBEE_TYPE_BATTERY;
			$location =  $sensor->name;
		}	
			
		if (($sensor->type=="CLIPGenericStatus") && (strpos($sensor->name,"MotionSensor")!==false)) {
			$type = ZIGBEE_TYPE_MOTION;
			
			$key = $sensor->uniqueid;
			$key = str_replace("MotionSensor ", "", $key);
			$key = str_replace(".Companion", "", $key);
			
			$row = plaatprotect_db_zigbee($key);
			$location =  $row->location;			
		}
		
		if ($type != ZIGBEE_TYPE_UNKNOWN) {
	
			$row = plaatprotect_db_zigbee($zid);
		
			if (isset($row->zid)) {
			
				$row->type = $type;
				$row->vendor = $sensor->manufacturername;
				$row->version = $sensor->swversion;
				$row->location = $location;
			
				plaatprotect_db_zigbee_update($row);
			
			} else {
		
				plaatprotect_db_zigbee_insert($zid, $sensor->manufacturername, $type, $sensor->swversion, $location);
			}
		}
	}
}

/*
** ---------------------
** PAGE
** ---------------------
*/

/**
 * plaatprotect zigbee page
 * @return HTML block which page contain.
 */
function plaatprotect_zigbee_page() {

	global $pid;
	
	//$event = '{"zid":"all", "action":"get"}';
	//plaatprotect_db_event_insert(CATEGORY_ZIGBEE, $event);
		
	$page ="<style>input[type='checkbox']{width:24px;height:24px}</style>";
	$page .= '<h1>'.t('TITLE_ZIGBEE').'</h1>';

	$page .= '<table>';
	$page .= '<thead>';
	$page .= '<tr>';
	
	$page .= '<th>';
	$page .= t('ZIGBEE_ID');
	$page .= '</th>';
	
	$page .= '<th>';
	$page .= t('ZIGBEE_LOCATION');
	$page .= '</th>';
	
	$page .= '<th>';
	$page .= t('ZIGBEE_TYPE');
	$page .= '</th>';
	
	$page .= '<th>';
	$page .= t('ZIGBEE_VENDOR');
	$page .= '</th>';
	
	$page .= '<th>';
	$page .= t('ZIGBEE_VERSION');
	$page .= '</th>';
	
	$page .= '</tr>';
	$page .= '</thead>';
	$page .= '<tbody>';
	
	$sql = 'select zid, vendor, type, version, location, state from zigbee order by zid';
	$result = plaatprotect_db_query($sql);
	while ($row = plaatprotect_db_fetch_object($result)) {
	
		$page .= '<tr>';
		$page .= '<td>' . $row->zid . '</td>';
		$page .= '<td>' . $row->location . '</td>';
		
		$page .= '<td>';
		$page .= t('SENSOR_TYPE_'.$row->type);
		$page .= '</td>';
		
		$page .= '<td>' . $row->vendor . '</td>';
		$page .= '<td>' . $row->version . '</td>';

		$page .= '</tr>';
	}
	
	$page .= '</table>';
		
	$page .= '<div class="nav">';
	$page .= plaatprotect_link('pid='.PAGE_HOME, t('LINK_HOME'));
	$page .= plaatprotect_link('pid='.$pid.'&eid='.EVENT_REFRESH, t('LINK_REFRESH'));
	$page .=  '</div>';
	
	//$page .= '<script>setTimeout(link,2500,\'pid='.$pid.'\');</script>';
		
	return $page;
}

/*
** ---------------------
** HANDLER
** ---------------------
*/

/**
 * plaatprotect about handler
 * @return HTML block which page contain.
 */
function plaatprotect_zigbee() {

	/* input */
	global $pid;
	global $eid;
	global $zid;
	global $sid;
  
	/* Event handler */
	switch ($eid) {
  
		case EVENT_REFRESH: 
			plaatprotect_refresh_actor_configuration();
			break;
	}
      
	/* Page handler */
	switch ($pid) {

		case PAGE_ZIGBEE:
			return plaatprotect_zigbee_page();
			break;
	}
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>
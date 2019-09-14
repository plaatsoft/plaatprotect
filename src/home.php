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
 * @brief contain home page
 */
 
/*
** ---------------------
** PARAMETERS
** ---------------------
*/

$name = plaatprotect_db_config_value('system_name', CATEGORY_GENERAL);
$version = plaatprotect_db_config_value('database_version', CATEGORY_GENERAL);
$username = plaatprotect_post("username", "");
$password = plaatprotect_post("password", "");

$webcam_present1 = plaatprotect_db_config_value('webcam_present', CATEGORY_WEBCAM_1);
$webcam_present2 = plaatprotect_db_config_value('webcam_present', CATEGORY_WEBCAM_2);
$zigbee_present = plaatprotect_db_config_value('zigbee_present', CATEGORY_ZIGBEE);
$zwave_present = plaatprotect_db_config_value('zwave_present', CATEGORY_ZWAVE);

$enable_battery_view = plaatprotect_db_config_value('enable_battery_view', CATEGORY_GENERAL);
$enable_temperature_view = plaatprotect_db_config_value('enable_temperature_view', CATEGORY_GENERAL);
$enable_luminance_view = plaatprotect_db_config_value('enable_luminance_view', CATEGORY_GENERAL);
$enable_humidity_view = plaatprotect_db_config_value('enable_humidity_view', CATEGORY_GENERAL);
$enable_motion_view = plaatprotect_db_config_value('enable_motion_view', CATEGORY_GENERAL);
$enable_pressure_view = plaatprotect_db_config_value('enable_pressure_view', CATEGORY_GENERAL);
$enable_windspeed_view = plaatprotect_db_config_value('enable_windspeed_view', CATEGORY_GENERAL);

/*
** ---------------------
** UTILS
** ---------------------
*/

/**
 * Check if energy converter is online.
 * @return HTML block with actual status of solar converter.
 */
function check_zwave_network() {
  
   global $zwave_present;

	$page = "";	
	
	if ($zwave_present=="true") {
	
	   $sql = 'select zid, last_update from zwave where type="Sensor"';			
		$result = plaatprotect_db_query($sql);
		
		while ($row = plaatprotect_db_fetch_object($result)) {
		  
		   $sql2 = 'select temperature from sensor where zid='.$row->zid.' and temperature>0 order by sid desc limit 0,1';
		   $result2 = plaatprotect_db_query($sql2);
			$row2 = plaatprotect_db_fetch_object($result2);
			
			$sql3 = 'select humidity from sensor where zid='.$row->zid.' and humidity>0 order by sid desc limit 0,1';
		   $result3 = plaatprotect_db_query($sql3);
			$row3 = plaatprotect_db_fetch_object($result3);
		
			$value = time()-strtotime($row->last_update);
			if ($value<(60*20)) {
			
				$page .= '<div class="checker good">';
				$page .= plaatprotect_db_zwave($row->zid)->location.': ';
				if (isset($row2->temperature)) {
					$page .= ' '.$row2->temperature.'&deg;C ';
				}
				if (isset($row3->humidity)) {
					$page .= ' '.$row3->humidity.'% ';
				}
				$page .= '</div> ';
				
			} else {
			
				$page .= '<div class="checker bad" >';
				$page .= plaatprotect_db_zwave($row->zid)->location.' ';
				$page .= $row->last_update;
				$page .= '</div> ';
			}
		}
   }	
	return $page;
}

/*
** ---------------------
** EVENTS
** ---------------------
*/

function plaatprotect_home_login_event() {

	global $pid;
	global $session;
	global $password;
	global $username;
	global $ip;
		
	$home_password = plaatprotect_db_config_value('home_password',CATEGORY_SECURITY);
	$home_username = plaatprotect_db_config_value('home_username',CATEGORY_SECURITY);
	
	if ($home_password==md5($password) && ($home_username==$username)) {
	
		$session = plaatprotect_db_get_session($ip, true);
		$pid = PAGE_HOME;
		
		$event = '{"login":"succesfull", "user":"'.$username.'", "ip":"'.$ip.'" }';
				
	} else {
	
		$event = '{"login":"failed", "user":"'.$username.'", "ip":"'.$ip.'" }';
	}
	
	plaatprotect_db_event_offramp_insert(CATEGORY_GENERAL, $event);
}

/*
** ---------------------------------------------------------------- 
** PAGE
** ---------------------------------------------------------------- 
*/

function plaatprotect_home_login_page() {

	// input	
	global $id;
	global $name;
	global $version;
			
	$page = '<h1>';
	$page .= t('TITLE').' ' ;
	$page .= '<span id="version">'.$version."</span>";
	if (strlen($name)>0) {
		$page .= ' ('.$name.') ';
	} 	
	$page .= '</h1>';

	$page .= '<fieldset>';
	
	$page .= '<br/>';
   $page .= '<label>'.t('LABEL_USERNAME').'</label>';
   $page .= '<input type="text" name="username" size="20" maxlength="20"/>';
   $page .= '<br/>';
	
   $page .= '<br/>';
   $page .= '<label>'.t('LABEL_PASSWORD').'</label>';
   $page .= '<input type="password" name="password" size="20" maxlength="20" autofocus/>';
   $page .= '<br/>';
  
   $page .= '<div class="nav">';   
   $page .= '<input type="hidden" name="token" value="pid='.PAGE_HOME_LOGIN.'&eid='.EVENT_LOGIN.'"/>';
   $page .= '<input type="submit" name="Submit" id="normal_link" value="'.t('LINK_LOGIN').'"/>';
   $page .= '</div>';
	
	$page .= '</fieldset>';
	
	$page .= '<br/>';
	$page .= '<div class="upgrade" id="upgrade"></div>';
	$page .= '<script type="text/javascript" src="js/version1.js"></script>';
	
   return $page;
}

/**
 * Home Page
 * @return HTML block which contain home page.
 */
function plaatprotect_home_page() {

	// input	
	global $pid;
	global $webcam_present1;	
	global $webcam_present2;	
	global $zwave_present;
	global $zigbee_present;
	global $name;
	global $session;
	global $version;
	
	global $enable_battery_view;
	global $enable_temperature_view;
	global $enable_luminance_view;
	global $enable_humidity_view;
	global $enable_motion_view;
	global $enable_pressure_view;
	global $enable_windspeed_view;
	
	$page = '<h1>';
	$page .= t('TITLE').' ';
	$page .= '<span id="version">'.$version."</span>";
	if (strlen($name)>0) {
		$page .= ' ('.$name.') ';
	} 	
	$page .= '</h1>';

	$page .= '<div class="home">';

	$page .= '<div class="menu">';
	if (($webcam_present1=="true") || ($webcam_present2=="true")) {
		$page .= plaatprotect_link('pid='.PAGE_WEBCAM, t('LINK_WEBCAM'));
	}	

	if ($zigbee_present=="true") {
		$page .= plaatprotect_link('pid='.PAGE_ZIGBEE, t('LINK_ZIGBEE'));
	}
	$page .= plaatprotect_link('pid='.PAGE_EVENT_VIEW.'&id=0', t('LINK_LOGGING'));

	$page .= plaatprotect_link('pid='.PAGE_ACTOR, t('LINK_ACTOR'));
	if ($zwave_present=="true") {
		$page .= plaatprotect_link('pid='.PAGE_ZWAVE, t('LINK_ZWAVE'));
	}
	$page .= '</div>';
		
	// ---------------------------
	
	$page .= '<div class="menu">';

	if ($enable_motion_view=="true") {
		$page .= plaatprotect_link('pid='.PAGE_MOTION, t('LINK_MOTION'));
	}
	
	if ($enable_battery_view=="true") {
		$page .= plaatprotect_link('pid='.PAGE_BATTERY, t('LINK_BATTERY'));
	}
	
	$page .= '</div>';
	
	// ---------------------------
	
	$page .= '<div class="menu">';

	if ($enable_temperature_view=="true") {
		$page .= plaatprotect_link('pid='.PAGE_TEMPERATURE, t('LINK_TEMPERATURE'));
	}
	
	if ($enable_luminance_view=="true") {
		$page .= plaatprotect_link('pid='.PAGE_LUMINANCE, t('LINK_LUMINANCE'));
	}

	if ($enable_humidity_view=="true") {
		$page .= plaatprotect_link('pid='.PAGE_HUMIDITY, t('LINK_HUMIDITY'));
	}	
	
	if ($enable_pressure_view=="true") {
		$page .= plaatprotect_link('pid='.PAGE_PRESSURE, t('LINK_PRESSURE'));
	}	

	if ($enable_windspeed_view=="true") {
		$page .= plaatprotect_link('pid='.PAGE_WINDSPEED, t('LINK_WINDSPEED'));
	}	
	
	$page .= '</div>';

	// ---------------------------

	$page .= '<div class="menu">';
	
	$settings_password = plaatprotect_db_config_value('settings_password',CATEGORY_SECURITY);		
	if (strlen($settings_password)>0) {
		$page .= plaatprotect_link('pid='.PAGE_SETTING_LOGIN, t('LINK_SETTINGS')); 
	} else {
		$page .= plaatprotect_link('pid='.PAGE_SETTING_CATEGORY, t('LINK_SETTINGS')); 
	}
	
	$page .= '</div>';

	// ---------------------------
	
	$page .= '<div class="menu">';
	$page .= plaatprotect_link('pid='.PAGE_DONATE, t('LINK_DONATE'));
	$page .= plaatprotect_link('pid='.PAGE_ABOUT, t('LINK_ABOUT'));				
	$page .= plaatprotect_link('pid='.PAGE_RELEASE_NOTES, t('LINK_RELEASE_NOTES'));			
	$page .= '</div>';
	
	// ---------------------------
	
	$page .= '<div class="menu">';
	$page .= '&nbsp;';	
	$page .= '</div>';
	
	// ---------------------------
	
	$page .= '<div class="menu">';
		
	switch (plaatprotect_db_config_value('alarm_scenario',CATEGORY_GENERAL)) {

		case SCENARIO_SLEEP: 
			$page .= plaatprotect_link_confirm('pid='.$pid.'&sid='.SCENARIO_SLEEP.'&eid='.EVENT_SWITCH_SCENARIO, t('SCENARIO_SLEEP'), t('ARE_YOU_SURE'));
			break;
					
		case SCENARIO_AWAY: 
			$page .= plaatprotect_link_confirm('pid='.$pid.'&sid='.SCENARIO_AWAY.'&eid='.EVENT_SWITCH_SCENARIO, t('SCENARIO_AWAY'), t('ARE_YOU_SURE'));
		   break;
			
		default: 
			$page .= plaatprotect_link_confirm('pid='.$pid.'&sid='.SCENARIO_HOME.'&eid='.EVENT_SWITCH_SCENARIO, t('SCENARIO_HOME'), t('ARE_YOU_SURE'));
			break;
	}
		
	switch (plaatprotect_db_config_value('panic_on',CATEGORY_GENERAL)) {
	
		case PANIC_OFF: 
			$page .= plaatprotect_link_confirm('pid='.$pid.'&eid='.EVENT_ON, t('LINK_PANIC_ON'), t('ARE_YOU_SURE'));
			break;
					
		case PANIC_ON: 
			$page .= plaatprotect_link_confirm('pid='.$pid.'&eid='.EVENT_OFF, t('LINK_PANIC_OFF'), t('ARE_YOU_SURE'));
		   break;
	}
	$page .= '</div>';
	
	$page .= '</div>';
	
	$tmp = check_zwave_network();
	if (strlen($tmp)>0) {
		$page .= '<br/><br/>';
		$page .=  $tmp;
		$page .= '<br/><br/>';
	}
	
	$page .= '<br/>';
	$page .= '<div class="upgrade" id="upgrade"></div>';
	$page .= '<script type="text/javascript" src="js/version1.js"></script>';
	
	return $page;
}

/*
** ---------------------
** HANDLER
** ---------------------
*/

/**
 * Home Page Handler
 * @return HTML block which contain home page.
 */
function plaatprotect_home() {

	/* input */
	global $pid;
	global $eid;
	global $sid;
	
	/* Event handler */
	switch ($eid) {

		case EVENT_LOGIN:
			plaatprotect_home_login_event();
			break;		
			
		case EVENT_ON:
			$config = plaatprotect_db_config('panic_on');
			$config->value = PANIC_ON;
			plaatprotect_db_config_update($config);
			$event = '{"zid":"0", "action":"panic", "value":"on"}';
			plaatprotect_db_event_offramp_insert(CATEGORY_ZWAVE, $event);
			break;
			
		case EVENT_OFF:
			$config = plaatprotect_db_config('panic_on');
			$config->value = PANIC_OFF;
			plaatprotect_db_config_update($config);
			$event = '{"zid":"0", "action":"panic", "value":"off"}';
			plaatprotect_db_event_offramp_insert(CATEGORY_ZWAVE, $event);
			break;
			
		case EVENT_SWITCH_SCENARIO:
			$config = plaatprotect_db_config('alarm_scenario');
			switch ($sid) {	
				case SCENARIO_HOME: 
					$config->value = SCENARIO_SLEEP;
					break;
					
				case SCENARIO_SLEEP: 
					$config->value = SCENARIO_AWAY;
					break;
					
				case SCENARIO_AWAY: 
					$config->value = SCENARIO_HOME;
					break;
			}
			plaatprotect_db_config_update($config);
			break;		
   }
		
	/* Page handler */
	switch ($pid) {
		
		case PAGE_HOME_LOGIN:
			return plaatprotect_home_login_page();
			break;
			
		case PAGE_HOME:
			return plaatprotect_home_page();
			break;
	}
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>

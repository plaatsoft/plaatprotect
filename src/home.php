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
**  All copyrights reserved (c) 2008-2016 PlaatSoft
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

$name = plaatprotect_db_get_config_item('system_name', LOOK_AND_FEEL);
$version = plaatprotect_db_get_config_item('database_version');
$webcam_present = plaatprotect_db_get_config_item('webcam_present', WEBCAM_1);
$hue_present = plaatprotect_db_get_config_item('hue_present', HUE_1);

$password = plaatprotect_post("password", "");

/*
** ---------------------
** UTILS
** ---------------------
*/

/**
 * Check if weather station is online.
 * @return HTML block with actual status of weather station.
 */
function check_weather_station() {

   global $weather_station_present;
    
   $page = "";
	
   if ($weather_station_present=="true") {
  
		$timestamp = date("Y-m-d H:i:s", strtotime("-3 minutes"));
		$sql = 'select humidity from weather where timestamp >= "'.$timestamp.'"';	
		$result = plaatprotect_db_query($sql);
		$count = plaatprotect_db_num_rows($result);
		
		if ($count>0){
		 
			$page  = '<div class="checker good">';
			$page .= t('WEATHER_METER_CONNECTION_UP');
			$page .= '</div>';
			
		} else {
		
			$page = '<div class="checker bad" title="'.t('NO_MEASUREMENT_ERROR').'">';
			$page .= t('WEATHER_METER_CONNECTION_DOWN');
			$page .= '</div>';
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
	global $ip;
		
	$home_password = plaatprotect_db_get_config_item('home_password',SECURITY);
	
	if ($home_password==md5($password)) {
	
		$session = plaatprotect_db_get_session($ip, true);
		$pid = PAGE_HOME;
	}
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
   $page .= t('TITLE');
	$page .= ' <div id="version" style="display: inline">';
	$page .= $version;
	$page .= "</div>";
	if (strlen($name)>0) {
		$page .= ' ('.$name.') ';
	} 	
	$page .= '</h1>';

   $page .= '<br/>';
   $page .= '<label>'.t('LABEL_PASSWORD').'</label>';
   $page .= '<input type="password" name="password" size="20" autofocus/>';
   $page .= '<br/>';
  
   $page .= '<div class="nav">';   
   $page .= '<input type="hidden" name="token" value="pid='.PAGE_HOME_LOGIN.'&eid='.EVENT_LOGIN.'"/>';
   $page .= '<input type="submit" name="Submit" id="normal_link" value="'.t('LINK_LOGIN').'"/>';
   $page .= '</div>';
	
   $page .= '<script type="text/javascript">var ip="'.$_SERVER['SERVER_ADDR'].'";var name="'.$name.'";var version="'.$version.'";</script>';
   $page .= '<script type="text/javascript" src="js/version.js"></script>';
	
   return $page;
}

/**
 * Home Page
 * @return HTML block which contain home page.
 */
function plaatprotect_home_page() {

	// input	
	global $webcam_present;
	global $hue_present;
	global $name;
	global $session;
	global $version;
	
	$page = '<h1>';
   $page .= t('TITLE');
	$page .= ' <div id="version" style="display: inline">';
	$page .= $version;
	$page .= "</div>";
	if (strlen($name)>0) {
		$page .= ' ('.$name.') ';
	} 	
	$page .= '</h1>';

	if ( !file_exists ( "config.inc" )) {
		$page .= '<br/><br/>';
		$page .= t('CONGIG_BAD');
		$page .= '<br/><br/>';
		
	} else {

		$page .= '<div class="home">';

		$page .= '<table>';
		
		$page .= '<tr>';
		$page .= '<th width="25%"></th>';
		$page .= '<th width="25%"></th>';
		$page .= '<th width="25%"></th>';
		$page .= '</tr>';
		
		$page .= '<tr>';
		$page .= '<td>';
		if ($webcam_present=="true") {
			$page .= plaatprotect_link('pid='.PAGE_LOGGING, t('LINK_LOGGING'));
		}
		$page .= '</td>';		
		$page .= '<td>';
		if ($webcam_present=="true") {
			$page .= plaatprotect_link('pid='.PAGE_WEBCAM, t('LINK_WEBCAM'));
		} 
		$page .= '</td>';		
		$page .= '<td>';
		if ($hue_present=="true") {
			$page .= plaatprotect_link('pid='.PAGE_HUE, t('LINK_HUE'));
		} 
		$page .= '</td>';		
		$page .= '</tr>';
		
				
		$page .= '<tr>';
		$page .= '<td>';
		$page .= '</td>';		
		$page .= '<td>';
		$settings_password = plaatprotect_db_get_config_item('settings_password',SECURITY);		
		if (strlen($settings_password)>0) {
			$page .= plaatprotect_link('pid='.PAGE_SETTING_LOGIN, t('LINK_SETTINGS')); 
		} else {
			$page .= plaatprotect_link('pid='.PAGE_SETTING_CATEGORY, t('LINK_SETTINGS')); 
		}
		$page .= '</td>';		
		$page .= '<td>';
		$page .= '</td>';		
		$page .= '</tr>';

		$page .= '<tr>';
		$page .= '<td>';
		$page .= plaatprotect_link('pid='.PAGE_DONATE, t('LINK_DONATE'));
		$page .= '</td>';
		$page .= '<td>';
		$page .= plaatprotect_link('pid='.PAGE_ABOUT, t('LINK_ABOUT'));				
		$page .= '</td>';
		$page .= '<td>';
		$page .= plaatprotect_link('pid='.PAGE_RELEASE_NOTES, t('LINK_RELEASE_NOTES'));		
		$page .= '</td>';
		$page .= '</tr>';
				
		$page .= '</table>';
		$page .= '</div>';

		$page .= '<script type="text/javascript">var ip="'.$_SERVER['SERVER_ADDR'].'";var name="'.$name.'";var version="'.$version.'";</script>';
		$page .= '<script type="text/javascript" src="js/version.js"></script>';
	}
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
	
	/* Event handler */
	switch ($eid) {

		case EVENT_LOGIN:
			plaatprotect_home_login_event();
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

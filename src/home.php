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
$password = plaatprotect_post("password", "");

$webcam_present = plaatprotect_db_get_config_item('webcam_present', WEBCAM_1);
$hue_present = plaatprotect_db_get_config_item('hue_present', HUE);
$zwave_present = plaatprotect_db_get_config_item('zwave_present', ZWAVE);

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
	
	   $sql = 'select zid, nodeid, last_update from zwave where type=3';			
		$result = plaatprotect_db_query($sql);
		
			
		while ($row = plaatprotect_db_fetch_object($result)) {
		  
		   $sql2 = 'select temperature from sensor where nodeid='.$row->nodeid.' and temperature>0 order by sid desc limit 0,1';
		   $result2 = plaatprotect_db_query($sql2);
			$row2 = plaatprotect_db_fetch_object($result2);
			
			$sql3 = 'select humidity from sensor where nodeid='.$row->nodeid.' and humidity>0 order by sid desc limit 0,1';
		   $result3 = plaatprotect_db_query($sql3);
			$row3 = plaatprotect_db_fetch_object($result3);
		
			$value = time()-strtotime($row->last_update);
			if ($value<(60*60*2)) {
			
				$page .= '<div class="checker good">';
				$page .= 'Sensor '.$row->nodeid.': ';
				if (isset($row2->temperature)) {
					$page .= ' '.$row2->temperature.'&deg;C';
				}
				if (isset($row3->humidity)) {
					$page .= ' '.$row3->humidity.'%';
				}
				$page .= '</div> ';
				
			} else {
			
				$page .= '<div class="checker bad" >';
				$page .= 'Sensor '.$row->nodeid;
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
	global $pid;
	global $webcam_present;	
	global $zwave_present;
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
				$page .= plaatprotect_link('pid='.PAGE_WEBCAM, t('LINK_WEBCAM'));
			}
		$page .= '</td>';	
		$page .= '<td>';
		if ($zwave_present=="true") {
			$page .= plaatprotect_link('pid='.PAGE_ZWAVE, t('LINK_ZWAVE'));
		} 
		$page .= '</td>';		
		$page .= '<td>';
		if ($hue_present=="true") {
			$page .= plaatprotect_link('pid='.PAGE_HUE, t('LINK_HUE'));
		} 
		$page .= '</td>';		
		$page .= '<td>';
		$page .= plaatprotect_link('pid='.PAGE_NOTIFICATION, t('LINK_NOTIFICATION'));
		$page .= '</td>';		
		$page .= '</tr>';
		
		
		$page .= '<tr>';	
		$page .= '<td>';
		$page .= '</td>';		
		$page .= '<td>';		
		$page .= plaatprotect_link('pid='.PAGE_CHART, t('LINK_CHART'));
		$page .= '</td>';
		$page .= '<td>';
		if ($zwave_present=="true") {
			$page .= plaatprotect_link('pid='.PAGE_LOGGING, t('LINK_LOGGING'));
		}
		$page .= '</td>';		
		$page .= '<td>';
		$page .= '</td>';
		$page .= '</tr>';
				
		$page .= '<tr>';	
		$page .= '<td>';
		$settings_password = plaatprotect_db_get_config_item('settings_password',SECURITY);		
		if (strlen($settings_password)>0) {
			$page .= plaatprotect_link('pid='.PAGE_SETTING_LOGIN, t('LINK_SETTINGS')); 
		} else {
			$page .= plaatprotect_link('pid='.PAGE_SETTING_CATEGORY, t('LINK_SETTINGS')); 
		}
		$page .= '</td>';		
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
		
		$page .= '<br/>';	
			
		$page .= '<table>';
				
		$page .= '<tr>';	
		$page .= '<td width="30%">';		
		switch (plaatprotect_db_get_config_item('alarm_scenario')) {
	
			case SLEEP: 
				$page .= plaatprotect_link('pid='.$pid.'&sid='.SLEEP.'&eid='.EVENT_SWITCH_SCENARIO, t('SCENARIO_SLEEP'));
				break;
						
			case AWAY: 
				$page .= plaatprotect_link('pid='.$pid.'&sid='.AWAY.'&eid='.EVENT_SWITCH_SCENARIO, t('SCENARIO_AWAY'));
			   break;
				
			default: 
				$page .= plaatprotect_link('pid='.$pid.'&sid='.HOME.'&eid='.EVENT_SWITCH_SCENARIO, t('SCENARIO_HOME'));
				break;
		}
		$page .= '</td>';
		$page .= '<tr>';

		$page .= '</table>';
		
		$page .= '</div>';
		
		$page .= '<br/><br/>';
		$page .= check_zwave_network();
		$page .= '<br/><br/>';
			
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
	global $sid;
	
	/* Event handler */
	switch ($eid) {

		case EVENT_LOGIN:
			plaatprotect_home_login_event();
			break;		
			
		case EVENT_SWITCH_SCENARIO:
			switch ($sid) {	
		
				case HOME: 
					$sid = SLEEP;
					break;
					
				case SLEEP: 
					$sid = AWAY;
					break;
					
				case AWAY: 
					$sid = HOME;
					break;
			}
			plaatprotect_db_set_config_item('alarm_scenario', $sid);
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

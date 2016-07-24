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
 * @brief contain zwave page
 */

$location = plaatprotect_post("location", "");

/*
** ---------------------
** ACTION
** ---------------------
*/

function plaatprotect_zwave_save_action($id) {

	global $location;
	
	$zwave = plaatprotect_db_zwave($id);
	if (!isset($zwave->zid)) {
		return false;
	}
	
	$zwave->location = $location;
	
	plaatprotect_db_zwave_update($zwave);
	
	return true;
}

function plaatprotect_zwave_update_action($id, $scenario) {

	$row = plaatprotect_db_zwave($id);
	
	switch ($scenario) {
		
		case SCENARIO_HOME: 	
			$row->home = plaatprotect_flip($row->home);
			break;
					
		case SCENARIO_SLEEP: 	
			$row->sleep = plaatprotect_flip($row->sleep);
			break;
					
		case SCENARIO_AWAY: 	
			$row->away = plaatprotect_flip($row->away);
			break;
			
		case SCENARIO_PANIC: 	
			$row->panic = plaatprotect_flip($row->panic);
			break;
	}
	
   plaatprotect_db_zwave_update($row);
	
	return true;
}

/*
** ---------------------
** PAGE
** ---------------------
*/

function plaatprotect_zwave_edit_page($id) {

	$page = '<h1>'.t('TITLE_ZWAVE').'</h1>';

	$row = plaatprotect_db_zwave($id);
	
	$page .= '<br/>';
   $page .= '<label>'.t('ZWAVE_LOCATION').':</label>';
   $page .= '<input type="input" name="location" size="30" value="'.$row->location.'" />';
   $page .= '<br/>';
	$page .= '<br/>';

	$page .= '<div class="nav">';
	$page .= plaatprotect_link('pid='.PAGE_ZWAVE, t('LINK_CANCEL'));
	$page .= plaatprotect_link('pid='.PAGE_ZWAVE.'&eid='.EVENT_SAVE.'&id='.$id, t('LINK_SAVE'));
	$page .=  '</div>';
	
	return $page;
}

function plaatprotect_zwave_page() {

	global $pid;

	$device_offline_timeout = plaatprotect_db_config_value('device_offline_timeout',CATEGORY_GENERAL);

   $page ="<style>input[type='checkbox']{width:24px;height:24px}</style>";
	$page .= '<h1>'.t('TITLE_ZWAVE').'</h1>';
	
	$page .= '<br>';
		
	$page .= '<table>';
	
	$page .= '<tr>';
	
	$page .= '<th width="10%">';
	$page .= t('ZWAVE_ID');
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= t('ZWAVE_LOCATION');
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= t('ZWAVE_TYPE');
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= t('ZWAVE_VENDOR');
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= t('ZWAVE_VERSION');
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= t('ZWAVE_STATE');
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= t('ZWAVE_HOME');
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= t('ZWAVE_SLEEP');
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= t('ZWAVE_AWAY');
	$page .= '</th>';

	$page .= '<th width="10%">';
	$page .= t('ZWAVE_PANIC');
	$page .= '</th>';
	
	$page .= '</tr>';
		
	$sql = 'select zid, vendor, version, type, location, home, sleep, away, panic, last_update from zwave';
	$result = plaatprotect_db_query($sql);
	while ($row = plaatprotect_db_fetch_object($result)) {

		$page .= '<tr>';
		
		$page .= '<td>';
		$page .= plaatprotect_normal_link('pid='.PAGE_ZWAVE_EDIT.'&id='.$row->zid, $row->zid);
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= $row->location;
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= $row->type;
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= $row->vendor;
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= $row->version;
		$page .= '</td>';
		
		$page .= '<td>';
		$value = time()-strtotime($row->last_update);
		if ($value < $device_offline_timeout) {
			$page .= '<div class="online">ONLINE</div>';
		} else {
			$page .= '<div class="offline">OFFLINE</div>';
		}
		$page .= '</td>';
		
		$page .= '<td>';
		if ($row->type=="Controller") {
			$page .= '<input type="checkbox" disabled checked readonly>';	
		} else {
			$page .= '<input type="checkbox" ';
			if ($row->home==1) { $page .= "checked"; }
			$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SCENARIO_HOME.'&id='.$row->zid.'\');">';
		} 
		$page .= '</td>';
		
		$page .= '<td>';
		if ($row->type=="Controller") {
			$page .= '<input type="checkbox" disabled checked readonly>';	
		} else {
			$page .= '<input type="checkbox" ';
			if ($row->sleep==1) { $page .= "checked"; }
			$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SCENARIO_SLEEP.'&id='.$row->zid.'\');">';
		} 
		$page .= '</td>';
		
		$page .= '<td>';
		if ($row->type=="Controller") {
			$page .= '<input type="checkbox" disabled checked readonly>';	
		} else {
			$page .= '<input type="checkbox" ';
			if ($row->away==1) { $page .= "checked"; }
			$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SCENARIO_AWAY.'&id='.$row->zid.'\');">';
		}
		$page .= '</td>';
		
		$page .= '<td>';
		if ($row->type=="Controller") {
			$page .= '<input type="checkbox" disabled checked readonly>';	
		} else {
			$page .= '<input type="checkbox" ';
			if ($row->panic==1) { $page .= "checked"; }
			$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SCENARIO_PANIC.'&id='.$row->zid.'\');">';
		}
		$page .= '</td>';
		
		$page .= '</tr>';
	}
	
	$page .= '</table>';
		
	$page .= '<div class="nav">';
	$page .= plaatprotect_link('pid='.PAGE_HOME, t('LINK_HOME'));
	$page .=  '</div>';
	
	//$page .= '<script>setTimeout(link,5000,\'pid='.$pid.'\');</script>';
		
	return $page;
}

/*
** ---------------------
** HANDLER
** ---------------------
*/

/**
 * plaatprotect 
 * @return HTML block which page content.
 */
function plaatprotect_zwave() {

  /* input */
  global $pid;
  global $eid;
  global $id;
  global $sid;
    
   /* Event handler */
  switch ($eid) {
  
		case EVENT_SAVE: 
			plaatprotect_zwave_save_action($id);
			break;
			
		case EVENT_UPDATE: 
			plaatprotect_zwave_update_action($id, $sid);
			break;
	}
    
  /* Page handler */
  switch ($pid) {

     case PAGE_ZWAVE:
        return plaatprotect_zwave_page();
        break;
		  
	   case PAGE_ZWAVE_EDIT:
        return plaatprotect_zwave_edit_page($id);
        break;
		  
  }
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>

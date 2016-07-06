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
 * @brief contain hue page
 */

/*
** ---------------------
** HUE
** ---------------------
*/

function plaatprotect_get_inventory_hue() {
		
 	$hue_ip = plaatprotect_db_get_config_item('hue_ip_address',HUE);
 	$hue_key = plaatprotect_db_get_config_item('hue_key',HUE);
	
   $hue_url = "http://".$hue_ip."/api/".$hue_key."/lights/";
	
   $json = file_get_contents($hue_url);
	
	$data = json_decode($json);

   return $data;
}

/*
** ---------------------
** DATABASE
** ---------------------
*/

function plaatprotect_set_hue_state($id, $scenario) {

	$sql = 'select hid, home, sleep, away from hue where hid='.$id;
	$result = plaatprotect_db_query($sql);
	$row = plaatprotect_db_fetch_object($result);
	
	if (!isset($row->hid)) {
		$sql = 'insert into hue (hid, home, sleep, away) value ('.$id.',0,0,0)';
		plaatprotect_db_query($sql);
	}
	
	$sql = 'select hid, home, sleep, away from hue where hid='.$id;
	$result = plaatprotect_db_query($sql);
	$row = plaatprotect_db_fetch_object($result);
	
	$value = 0;
	switch ($scenario) {
		
		case HOME: 	
			$value= $row->home;
			break;
					
		case SLEEP: 	
			$value= $row->sleep;
			break;
					
		case AWAY: 	
			$value= $row->away;
			break;
	}
		
	if ($value==1) {
		$value=0;
	} else {
		$value=1;
	}
	
	$sql ="";
	switch ($scenario) {
		
		case HOME: 	
			$sql = 'update hue set home='.$value.' where hid='.$id;
			break;
					
		case SLEEP: 	
			$sql = 'update hue set sleep='.$value.' where hid='.$id;
			break;
					
		case AWAY: 	
			$sql = 'update hue set away='.$value.' where hid='.$id;
			break;
	}
	plaatprotect_db_query($sql);
}

/*
** ---------------------
** PAGE
** ---------------------
*/

/**
 * plaatprotect hue page
 * @return HTML block which page contain.
 */
function plaatprotect_hue_zigbee() {

	global $pid;

   $page ="<style>input[type='checkbox']{width:24px;height:24px}</style>";
	$page .= '<h1>'.t('TITLE_ZIGBEE').'</h1>';

	$data  = plaatprotect_get_inventory_hue();

	$page .= '<table>';
	$page .= '<thead>';
	$page .= '<tr>';
	$page .= '<th width="10%">ID</th>';
	$page .= '<th width="10%">Name</th>';
	$page .= '<th width="10%">Type</th>';
	$page .= '<th width="10%">Vendor</th>';
	$page .= '<th width="10%">Version</th>';
   $page .= '<th width="10%">State</th>';
	$page .= '<th width="10%">Home</th>';
	$page .= '<th width="10%">Sleep</th>';
	$page .= '<th width="10%">Away</th>';
	$page .= '</tr>';
	$page .= '</thead>';
	$page .= '<tbody>';

	foreach($data as $id => $bulb ) {
		$page .= '<tr>';
		$page .= '<td>' . $id . '</td>';
		$page .= '<td>' . $bulb->name . '</td>';
		$page .= '<td>' . $bulb->type . '</td>';
		$page .= '<td>' . $bulb->manufacturername . '</td>';
		$page .= '<td>' . $bulb->swversion . '</td>';
		$page .= ($bulb->state->reachable ? ($bulb->state->on ? '<td class="on">ON</td>' : '<td class="off">OFF</td>') : '<td class="not">OFFLINE</td>') . '</td>';
		
		$sql = 'select hid, home, sleep, away from hue where hid='.$id;
		$result = plaatprotect_db_query($sql);
		$row = plaatprotect_db_fetch_object($result);
	
		$page .= '<td>';
		$page .= '<input type="checkbox" ';		
		if ((isset($row->hid)) && ($row->home==1)) { 
			$page .= "checked"; 
		}		
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.HOME.'&id='.$id.'\');">';			
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= '<input type="checkbox" ';		
		if ((isset($row->hid)) && ($row->sleep==1)) { 
			$page .= "checked"; 
		}		
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SLEEP.'&id='.$id.'\');">';			
		$page .= '</td>';

		$page .= '<td>';
		$page .= '<input type="checkbox" ';		
		if ((isset($row->hid)) && ($row->away==1)) { 
			$page .= "checked"; 
		}		
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.AWAY.'&id='.$id.'\');">';			
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
 * plaatprotect about handler
 * @return HTML block which page contain.
 */
function plaatprotect_zigbee() {

	/* input */
  global $pid;
  global $eid;
  global $id;
  global $sid;
    
   /* Event handler */
  switch ($eid) {
  
		case EVENT_UPDATE: 
			plaatprotect_set_hue_state($id, $sid);
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

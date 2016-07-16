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
** ACTIONS
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
		
		case SCENARIO_HOME: 	
			$value= $row->home;
			break;
					
		case SCENARIO_SLEEP: 	
			$value= $row->sleep;
			break;
					
		case SCENARIO_AWAY: 	
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
		
		case SCENARIO_HOME: 	
			$sql = 'update hue set home='.$value.' where hid='.$id;
			break;
					
		case SCENARIO_SLEEP: 	
			$sql = 'update hue set sleep='.$value.' where hid='.$id;
			break;
					
		case SCENARIO_AWAY: 	
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
function plaatprotect_zigbee_page() {

	global $pid;

   $page ="<style>input[type='checkbox']{width:24px;height:24px}</style>";
	$page .= '<h1>'.t('TITLE_ZIGBEE').'</h1>';

	$data  = plaatprotect_get_inventory_hue();

	$page .= '<table>';
	$page .= '<thead>';
	$page .= '<tr>';
	
	$page .= '<th width="10%">';
	$page .= t('ZIGBEE_ID');
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= t('ZIGBEE_LOCATION');
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= t('ZIGBEE_TYPE');
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= t('ZIGBEE_VENDOR');
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= t('ZIGBEE_VERSION');
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= t('ZIGBEE_STATE');
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= t('ZIGBEE_HOME');
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= t('ZIGBEE_SLEEP');
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= t('ZIGBEE_AWAY');
	$page .= '</th>';
	
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
							
		if ($bulb->state->reachable==1) {
			if ($bulb->state->on==1) {
				$page .= '<td><div class="online">'.plaatprotect_normal_link('pid='.$pid.'&id='.$id.'&eid='.EVENT_OFF,t('LINK_ON')).'</div></td>';
			} else {
				$page .= '<td><div class="online">'.plaatprotect_normal_link('pid='.$pid.'&id='.$id.'&eid='.EVENT_ON,t('LINK_OFF')).'</div></td>';
			} 
		} else {
			$page .= '<td><div class="offline">OFFLINE</div></td>';
		}
			
		$sql = 'select hid, home, sleep, away from hue where hid='.$id;
		$result = plaatprotect_db_query($sql);
		$row = plaatprotect_db_fetch_object($result);
	
		$page .= '<td>';
		$page .= '<input type="checkbox" ';		
		if ((isset($row->hid)) && ($row->home==1)) { 
			$page .= "checked"; 
		}		
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SCENARIO_HOME.'&id='.$id.'\');">';			
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= '<input type="checkbox" ';		
		if ((isset($row->hid)) && ($row->sleep==1)) { 
			$page .= "checked"; 
		}		
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SCENARIO_SLEEP.'&id='.$id.'\');">';			
		$page .= '</td>';

		$page .= '<td>';
		$page .= '<input type="checkbox" ';		
		if ((isset($row->hid)) && ($row->away==1)) { 
			$page .= "checked"; 
		}		
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SCENARIO_AWAY.'&id='.$id.'\');">';			
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
  
		case EVENT_ON: 
			plaatprotect_set_hue($id, "true");
			break;
			
		case EVENT_OFF: 
			plaatprotect_set_hue($id, "false");
			break;
						
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
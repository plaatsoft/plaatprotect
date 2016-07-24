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

function plaatprotect_set_hue_state($hid, $scenario) {

	$row = plaatprotect_db_hue($hid);

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
	plaatprotect_db_hue_update($row);
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
	
	$event = '{"hid":"all", "action":"get"}';
	plaatprotect_event_insert(CATEGORY_ZIGBEE, $event);
		
   $page ="<style>input[type='checkbox']{width:24px;height:24px}</style>";
	$page .= '<h1>'.t('TITLE_ZIGBEE').'</h1>';

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
	
	$page .= '<th width="10%">';
	$page .= t('ZIGBEE_PANIC');
	$page .= '</th>';
	
	$page .= '</tr>';
	$page .= '</thead>';
	$page .= '<tbody>';
	
	$sql = 'select hid, vendor, type, version, location, state, home, sleep, away, panic from hue order by hid';
	$result = plaatprotect_db_query($sql);
	while ($row = plaatprotect_db_fetch_object($result)) {
	
		$page .= '<tr>';
		$page .= '<td>' . $row->hid . '</td>';
		$page .= '<td>' . $row->location . '</td>';
		$page .= '<td>' . $row->type . '</td>';
		$page .= '<td>' . $row->vendor . '</td>';
		$page .= '<td>' . $row->version . '</td>';
						
		if ($row->state==HUE_STATE_ON) {
			$page .= '<td><div id="hid'.$row->hid.'" class="online">'.plaatprotect_normal_link('pid='.$pid.'&hid='.$row->hid.'&eid='.EVENT_OFF,t('LINK_ON')).'</div></td>';
		} else if ($row->state==HUE_STATE_OFF) {
			$page .= '<td><div id="hid'.$row->hid.'" class="online">'.plaatprotect_normal_link('pid='.$pid.'&hid='.$row->hid.'&eid='.EVENT_ON,t('LINK_OFF')).'</div></td>';
		} else {
			$page .= '<td><div id="hid'.$row->hid.'" class="offline">OFFLINE</div></td>';
		}
		
		$page .= '<td>';
		$page .= '<input type="checkbox" ';		
		if ($row->home==1) { 
			$page .= "checked"; 
		}		
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SCENARIO_HOME.'&hid='.$row->hid.'\');">';			
		$page .= '</td>';
	
		$page .= '<td>';
		$page .= '<input type="checkbox" ';		
		if ($row->sleep==1) { 
			$page .= "checked"; 
		}		
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SCENARIO_SLEEP.'&hid='.$row->hid.'\');">';			
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= '<input type="checkbox" ';		
		if ($row->away==1) { 
			$page .= "checked"; 
		}		
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SCENARIO_AWAY.'&hid='.$row->hid.'\');">';			
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= '<input type="checkbox" ';		
		if ($row->panic==1) { 
			$page .= "checked"; 
		}		
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SCENARIO_PANIC.'&hid='.$row->hid.'\');">';			
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
  global $hid;
  global $sid;
      
   /* Event handler */
  switch ($eid) {
  
		case EVENT_ON: 
			$event = '{"hid":'.$hid.', "action":"set", "value":"on"}';
			plaatprotect_event_insert(CATEGORY_ZIGBEE, $event);
			break;
			
		case EVENT_OFF: 
			$event = '{"hid":'.$hid.', "action":"set", "value":"off"}';
			plaatprotect_event_insert(CATEGORY_ZIGBEE, $event);	
			break;
						
		case EVENT_UPDATE: 
			plaatprotect_set_hue_state($hid, $sid);
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
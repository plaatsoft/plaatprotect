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
** ACTIONS
** ---------------------
*/

function plaatprotect_set_actor_state($id, $scenario) {

	$sql = 'select aid, type, home, sleep, away, panic from actor where aid='.$id;
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
			
		case SCENARIO_PANIC: 	
			$value= $row->panic;
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
			$sql = 'update actor set home='.$value.' where aid='.$id;
			break;
					
		case SCENARIO_SLEEP: 	
			$sql = 'update actor set sleep='.$value.' where aid='.$id;
			break;
					
		case SCENARIO_AWAY: 	
			$sql = 'update actor set away='.$value.' where aid='.$id;
			break;
			
		case SCENARIO_PANIC: 	
			$sql = 'update actor set panic='.$value.' where aid='.$id;
			break;
	}
	plaatprotect_db_query($sql);
}

function plaatprotect_refresh_actor_configuration() {
		
 	$zigbee_ip = plaatprotect_db_config_value('zigbee_ip_address',CATEGORY_ZIGBEE);
 	$zigbee_key = plaatprotect_db_config_value('zigbee_key',CATEGORY_ZIGBEE);	
    $zigbee_url = "http://".$zigbee_ip."/api/".$zigbee_key."/lights/";
	
	@$json = file_get_contents($zigbee_url);
	
	$data = json_decode($json);
		
	foreach($data as $aid => $bulb ) {

		$row = plaatprotect_db_actor($aid);
		if (isset($row->aid)) {
			
			$row->vendor = $bulb->manufacturername;
			$row->version = $bulb->swversion;
			$row->location =  $bulb->name;
			
			plaatprotect_db_actor_update($row);
			
		} else {
		
			plaatprotect_db_actor_insert($aid, $bulb->manufacturername, 0, $bulb->swversion, $bulb->name);
		}
	}
}

/*
** ---------------------
** PAGE
** ---------------------
*/

function plaatprotect_actor_page() {

	global $pid;

	$page = '<h1>'.t('TITLE_ACTOR').'</h1>';
			
	$page .= '<table>';
	
	$page .= '<tr>';
	
	$page .= '<th>';
	$page .= 'Id';
	$page .= '</th>';
	
	$page .= '<th>';
	$page .= t('ZIGBEE_LOCATION');
	$page .= '</th>';
	
	$page .= '<th>';
	$page .= 'Type';
	$page .= '</th>';
		
	$page .= '<th>';
	$page .= 'Vendor';
	$page .= '</th>';

	$page .= '<th>';
	$page .= 'Version';
	$page .= '</th>';
	
	$page .= '<th>';
	$page .= 'Home';
	$page .= '</th>';
	
	$page .= '<th>';
	$page .= 'Sleep';
	$page .= '</th>';
	
	$page .= '<th>';
	$page .= 'Away';
	$page .= '</th>';
			
	$page .= '<th>';
	$page .= 'Panic';
	$page .= '</th>';
	
	$page .= '</tr>';
		
	$sql = 'select aid, location, type, vendor, version, home, sleep, away, panic from actor order by aid';
	$result = plaatprotect_db_query($sql);
	while ($row = plaatprotect_db_fetch_object($result)) {

		$page .= '<tr>';
		
		$page .= '<td>';
		$page .= $row->aid;
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= $row->location;
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= t('ACTOR_TYPE_'.$row->type);
		$page .= '</td>';
	
		$page .= '<td>';
		$page .= $row->vendor;
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= $row->version;
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= '<input type="checkbox" ';
		if ($row->home==1) { $page .= "checked"; }
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SCENARIO_HOME.'&id='.$row->aid.'\');">';
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= '<input type="checkbox" ';
		if ($row->sleep==1) { $page .= "checked"; }
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SCENARIO_SLEEP.'&id='.$row->aid.'\');">';
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= '<input type="checkbox" ';
		if ($row->away==1) { $page .= "checked"; }
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SCENARIO_AWAY.'&id='.$row->aid.'\');">';
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= '<input type="checkbox" ';
		if ($row->panic==1) { $page .= "checked"; }
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SCENARIO_PANIC.'&id='.$row->aid.'\');">';
		$page .= '</td>';
		
		$page .= '</tr>';
	}
	
	$page .= '</table>';
		
	$page .= '<div class="nav">';
	$page .= plaatprotect_link('pid='.PAGE_HOME, t('LINK_HOME'));
	$page .= plaatprotect_link('pid='.$pid.'&eid='.EVENT_REFRESH, t('LINK_REFRESH'));
	$page .=  '</div>';
			
	return $page;
}

/*
** ---------------------
** HANDLER
** ---------------------
*/

function plaatprotect_actor() {

  /* input */
  global $pid;
  global $eid;
  global $id;
  global $sid;
    
   /* Event handler */
  switch ($eid) {
  
		case EVENT_UPDATE: 
			plaatprotect_set_actor_state($id, $sid);
			break;
			
		case EVENT_REFRESH: 
			plaatprotect_refresh_actor_configuration();
			break;
	}

  /* Page handler */
  switch ($pid) {

     case PAGE_ACTOR:
        return plaatprotect_actor_page();
        break;
  }
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>

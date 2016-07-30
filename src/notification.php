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
** DATABASE
** ---------------------
*/

function plaatprotect_set_notification_state($id, $scenario) {

	$sql = 'select nid, type, home, sleep, away, panic from notification where nid='.$id;
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
			$sql = 'update notification set home='.$value.' where nid='.$id;
			break;
					
		case SCENARIO_SLEEP: 	
			$sql = 'update notification set sleep='.$value.' where nid='.$id;
			break;
					
		case SCENARIO_AWAY: 	
			$sql = 'update notification set away='.$value.' where nid='.$id;
			break;
			
		case SCENARIO_PANIC: 	
			$sql = 'update notification set panic='.$value.' where nid='.$id;
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
 * plaatprotect zwave overview page
 * @return HTML block which page contain.
 */
function plaatprotect_notification_page() {

	global $pid;

   $page ="<style>input[type='checkbox']{width:24px;height:24px}</style>";
	$page .= '<h1>'.t('TITLE_NOTIFICATION').'</h1>';
	
	$page .= '<br>';
		
	$page .= '<table>';
	
	$page .= '<tr>';
	
	$page .= '<th width="15%">';
	$page .= 'ID';
	$page .= '</th>';
	
	$page .= '<th width="15%">';
	$page .= 'Description';
	$page .= '</th>';
		
	$page .= '<th width="15%">';
	$page .= 'Home';
	$page .= '</th>';
	
	$page .= '<th width="15%">';
	$page .= 'Sleep';
	$page .= '</th>';
	
	$page .= '<th width="15%">';
	$page .= 'Away';
	$page .= '</th>';
			
	$page .= '<th width="15%">';
	$page .= 'Panic';
	$page .= '</th>';
	
	$page .= '</tr>';
		
	$sql = 'select nid, type, home, sleep, away, panic from notification';
	$result = plaatprotect_db_query($sql);
	while ($row = plaatprotect_db_fetch_object($result)) {

		$page .= '<tr>';
		
		$page .= '<td>';
		$page .= $row->nid;
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= t('NOTIFICATION_'.$row->type);
		$page .= '</td>';
				
		$page .= '<td>';
		$page .= '<input type="checkbox" ';
		if ($row->home==1) { $page .= "checked"; }
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SCENARIO_HOME.'&id='.$row->nid.'\');">';
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= '<input type="checkbox" ';
		if ($row->sleep==1) { $page .= "checked"; }
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SCENARIO_SLEEP.'&id='.$row->nid.'\');">';
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= '<input type="checkbox" ';
		if ($row->away==1) { $page .= "checked"; }
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SCENARIO_AWAY.'&id='.$row->nid.'\');">';
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= '<input type="checkbox" ';
		if ($row->panic==1) { $page .= "checked"; }
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SCENARIO_PANIC.'&id='.$row->nid.'\');">';
		$page .= '</td>';
		
		$page .= '</tr>';
	}
	
	$page .= '</table>';
		
	$page .= '<div class="nav">';
	$page .= plaatprotect_link('pid='.PAGE_HOME, t('LINK_HOME'));
	$page .=  '</div>';
			
	return $page;
}

/*
** ---------------------
** HANDLER
** ---------------------
*/

/**
 * plaatprotect 
 * @return HTML block which page contain.
 */
function plaatprotect_notification() {

  /* input */
  global $pid;
  global $eid;
  global $id;
  global $sid;
    
   /* Event handler */
  switch ($eid) {
  
		case EVENT_UPDATE: 
			plaatprotect_set_notification_state($id, $sid);
			break;
	}

  /* Page handler */
  switch ($pid) {

     case PAGE_NOTIFICATION:
        return plaatprotect_notification_page();
        break;
  }
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>

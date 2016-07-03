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

function plaatprotect_set_zwave_state($id, $scenario) {

	$sql = 'select zid, home, sleep, away from zwave where zid='.$id;
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
			$sql = 'update zwave set home='.$value.' where zid='.$id;
			break;
					
		case SLEEP: 	
			$sql = 'update zwave set sleep='.$value.' where zid='.$id;
			break;
					
		case AWAY: 	
			$sql = 'update zwave set away='.$value.' where zid='.$id;
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
function plaatprotect_zwave_page() {

	global $pid;

   $page ="<style>input[type='checkbox']{width:24px;height:24px}</style>";
	$page .= '<h1>'.t('TITLE_ZWAVE').'</h1>';
	
	$page .= '<br>';
		
	$page .= '<table>';
	
	$page .= '<tr>';
	
	$page .= '<th width="10%">';
	$page .= 'ID';
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= 'Description';
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= 'Vendor';
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= 'Location';
	$page .= '</th>';
		
	$page .= '<th width="10%">';
	$page .= 'State';
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= 'Home';
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= 'Sleep';
	$page .= '</th>';
	
	$page .= '<th width="10%">';
	$page .= 'Away';
	$page .= '</th>';
			
	$page .= '</tr>';
		
	$sql = 'select zid, nodeid, type, vendor, description, location, home, sleep, away from zwave';
	$result = plaatprotect_db_query($sql);
	while ($row = plaatprotect_db_fetch_object($result)) {

		$page .= '<tr>';
		
		$page .= '<td>';
		$page .= $row->nodeid;
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= $row->description;
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= $row->vendor;
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= $row->location;
		$page .= '</td>';
				
		$page .= '<td>';
		if ($row->type==2) {
			$page .= "OFF";
		} else {
			$page .= "ONLINE";
		}
		$page .= '</td>';
		
		$page .= '<td>';
		if ($row->type==2) {
			$page .= '<input type="checkbox" ';
			if ($row->home==1) { $page .= "checked"; }
			$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.HOME.'&id='.$row->zid.'\');">';
		} else {
			$page .= '<input type="checkbox" disabled readonly>';			
		}
		$page .= '</td>';
		
		$page .= '<td>';
		if ($row->type==2) {
			$page .= '<input type="checkbox" ';
			if ($row->sleep==1) { $page .= "checked"; }
			$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.SLEEP.'&id='.$row->zid.'\');">';
		} else {
			$page .= '<input type="checkbox" disabled readonly>';			
		}
		$page .= '</td>';
		
			$page .= '<td>';
		if ($row->type==2) {
			$page .= '<input type="checkbox" ';
			if ($row->away==1) { $page .= "checked"; }
			$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&sid='.AWAY.'&id='.$row->zid.'\');">';
		} else {
			$page .= '<input type="checkbox" disabled readonly>';			
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
 * @return HTML block which page contain.
 */
function plaatprotect_zwave() {

  /* input */
  global $pid;
  global $eid;
  global $id;
  global $sid;
    
   /* Event handler */
  switch ($eid) {
  
		case EVENT_UPDATE: 
			plaatprotect_set_zwave_state($id, $sid);
			break;
	}
    
  /* Page handler */
  switch ($pid) {

     case PAGE_ZWAVE:
        return plaatprotect_zwave_page();
        break;
  }
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>

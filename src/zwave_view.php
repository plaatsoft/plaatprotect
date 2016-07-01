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

function plaatprotect_get_alarm_zwave_state($id) {

	$sql = 'select alarm_enabled from zwave where nodeid='.$id;
	$result = plaatprotect_db_query($sql);
	$row = plaatprotect_db_fetch_object($result);
	
	return (isset($row->alarm_enabled) && ($row->alarm_enabled==1));	
}

function plaatprotect_set_alarm_zwave_state($id) {

	if (plaatprotect_get_alarm_zwave_state($id)) {
	
		$sql = 'update zwave set alarm_enabled=0 where nodeid='.$id;
		
	} else {
	
		$sql = 'update zwave set alarm_enabled=1 where nodeid='.$id;
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
	
	$page .= '<th width="15%">';
	$page .= 'ID';
	$page .= '</th>';
	
	$page .= '<th width="15%">';
	$page .= 'Description';
	$page .= '</th>';
	
	$page .= '<th width="15%">';
	$page .= 'Vendor';
	$page .= '</th>';
	
	$page .= '<th width="15%">';
	$page .= 'Location';
	$page .= '</th>';
		
	$page .= '<th width="15%">';
	$page .= 'State';
	$page .= '</th>';
	
	$page .= '<th width="15%">';
	$page .= 'Alarm';
	$page .= '</th>';
			
	$page .= '</tr>';
		
	$sql = 'select nodeid, vendor, description, location, alarm_enabled from zwave';
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
		$page .= "ONLINE";
		$page .= '</td>';
		
		$page .= '<td>';
		if ($row->description=='Sirene') {
			$page .= '<input type="checkbox" '. (plaatprotect_get_alarm_zwave_state($row->nodeid) ? "checked" : "");			
			$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&id='.$row->nodeid.'\');">';
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
    
   /* Event handler */
  switch ($eid) {
  
		case EVENT_UPDATE: 
			plaatprotect_set_alarm_zwave_state($id);
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

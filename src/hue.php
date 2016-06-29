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

function plaatprotect_hue_alarm_enabled($id) {

	$sql = 'select hid from hue where hid='.$id;
	$result = plaatprotect_db_query($sql);
	$row = plaatprotect_db_fetch_object($result);
	
	return (isset($row->hid));	
}

function plaatprotect_hue_alarm_update($id) {

	if (plaatprotect_hue_alarm_enabled($id)) {
	
		$sql = 'delete from hue where hid='.$id;
		
	} else {
	
		$sql = 'insert into hue (hid) value ('.$id.')';
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
function plaatprotect_hue_page() {

	global $pid;

   $page ="<style>input[type='checkbox']{width:24px;height:24px}</style>";
	$page .= '<h1>'.t('TITLE_HUE').'</h1>';

	$data  = plaatprotect_get_inventory_hue();
	
	$page .= '<br>';
		
	$page .= '<table>';
	
	$page .= '<tr>';
	
	$page .= '<th width="15%">';
	$page .= 'Id';
	$page .= '</th>';
	
	$page .= '<th width="15%">';
	$page .= 'Name';
	$page .= '</th>';
	
	$page .= '<th width="15%">';
	$page .= 'Type';
	$page .= '</th>';
	
	$page .= '<th width="15%">';
	$page .= 'Manufacture';
	$page .= '</th>';
	
	$page .= '<th width="15%">';
	$page .= 'Version';
	$page .= '</th>';
	
	$page .= '<th width="15%">';
	$page .= 'State';
	$page .= '</th>';
	
	$page .= '<th width="15%">';
	$page .= 'Alarm';
	$page .= '</th>';
			
	$page .= '</tr>';
		
	foreach($data as $id => $bulb ) {	
		$page .= '<tr>';
		
		$page .= '<td>';
		$page .= $id;
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= $bulb->name;
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= $bulb->modelid;
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= $bulb->manufacturername;
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= $bulb->swversion;
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= ($bulb->state->on) ? "ON" : "OFF";
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= '<input type="checkbox" '. (plaatprotect_hue_alarm_enabled($id) ? "checked" : "");
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_HUE.'&id='.$id.'\');">';
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
 * plaatprotect about handler
 * @return HTML block which page contain.
 */
function plaatprotect_hue() {

  /* input */
  global $pid;
  global $eid;
  global $id;
    
   /* Event handler */
  switch ($eid) {
  
		case EVENT_HUE: 
			plaatprotect_hue_alarm_update($id);
			break;
	}

  /* Page handler */
  switch ($pid) {

     case PAGE_HUE:
        return plaatprotect_hue_page();
        break;
  }
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>

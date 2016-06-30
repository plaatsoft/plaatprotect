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
	
	//echo $json;
	
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

	$page .= '<table>';
	$page .= '<thead>';
	$page .= '<tr>';
	$page .= '<th width="12%">ID</th>';
	$page .= '<th width="12%">Name</th>';
	$page .= '<th width="12%">Type</th>';
	$page .= '<th width="12%">Model</th>';
	$page .= '<th width="12%">Vendor</th>';
	$page .= '<th width="12%">Version</th>';
   $page .= '<th width="12%">State</th>';
	$page .= '<th width="12%">Alarm</th>';
	$page .= '</tr>';
	$page .= '</thead>';
	$page .= '<tbody>';

	foreach($data as $id => $bulb ) {
	  $page .= '<tr>';
  	  $page .= '<td>' . $id . '</td>';
	  $page .= '<td>' . $bulb->name . '</td>';
	  $page .= '<td>' . $bulb->type . '</td>';
	  $page .= '<td>' . $bulb->modelid . '</td>';
	  $page .= '<td>' . $bulb->manufacturername . '</td>';
	  $page .= '<td>' . $bulb->swversion . '</td>';
	  $page .= ($bulb->state->reachable ? ($bulb->state->on ? '<td class="on">ON</td>' : '<td class="off">OFF</td>') : '<td class="not">OFFLINE</td>') . '</td>';

		
		$page .= '<td>';
		$page .= '<input type="checkbox" '. (plaatprotect_hue_alarm_enabled($id) ? "checked" : "");
		$page .= ' onchange="link(\'pid='.$pid.'&eid='.EVENT_UPDATE.'&id='.$id.'\');">';
		$page .= '</td>';


	  $page .= '</tr>';

		/*;


		
		*/
	}
	
	$page .= '</table>';
		
	$page .= '<div class="nav">';
	$page .= plaatprotect_link('pid='.PAGE_HOME, t('LINK_HOME'));
	$page .=  '</div>';
	
	$page .= '<script>setTimeout(link,5000,\'pid='.$pid.'\');</script>';
		
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
  
		case EVENT_UPDATE: 
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

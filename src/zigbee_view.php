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

/**
 * @file
 * @brief contain zigbee page
 */

/*
** ---------------------
** PAGE
** ---------------------
*/

/**
 * plaatprotect zigbee page
 * @return HTML block which page contain.
 */
function plaatprotect_zigbee_page() {

	global $pid;
	
	//$event = '{"zid":"all", "action":"get"}';
	//plaatprotect_db_event_insert(CATEGORY_ZIGBEE, $event);
		
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
	
	$page .= '</tr>';
	$page .= '</thead>';
	$page .= '<tbody>';
	
	$sql = 'select zid, vendor, type, version, location, state from zigbee order by zid';
	$result = plaatprotect_db_query($sql);
	while ($row = plaatprotect_db_fetch_object($result)) {
	
		$page .= '<tr>';
		$page .= '<td>' . $row->zid . '</td>';
		$page .= '<td>' . $row->location . '</td>';
		
		$page .= '<td>';
		$page .= t('SENSOR_TYPE_'.$row->type);
		$page .= '</td>';
		
		$page .= '<td>' . $row->vendor . '</td>';
		$page .= '<td>' . $row->version . '</td>';

		$page .= '</tr>';
	}
	
	$page .= '</table>';
		
	$page .= '<div class="nav">';
	$page .= plaatprotect_link('pid='.PAGE_HOME, t('LINK_HOME'));
	$page .=  '</div>';
	
	//$page .= '<script>setTimeout(link,2500,\'pid='.$pid.'\');</script>';
		
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
  global $zid;
  global $sid;
      
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
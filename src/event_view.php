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
 * @brief contain event view page
 */

/*
** ---------------------
** PAGE
** ---------------------
*/

function plaatprotect_event_view_page() {

	global $pid;
	global $id;
	global $eid;
	global $eid2;

    $page ="<style>input[type='checkbox']{width:24px;height:24px}</style>";
	$page .= '<h1>'.t('TITLE_EVENT').'</h1>';
	
	$sql  = 'select timestamp, category, action, processed from event ';
	if ($eid2 == EVENT_FILTER) {
		$sql .= 'where action like "%alarm%" ';
	}
	$sql .= 'order by timestamp desc ';
	$sql .= 'limit '.($id*17).',15 ';
	
	$result = plaatprotect_db_query($sql);

	$page .= '<table>';

	$page .= '<tr>';

	$page .= '<th>';
	$page .= t('EVENT_TIMESTAMP');
	$page .= '</th>';

	$page .= '<th>';
	$page .= t('EVENT_AGO');
	$page .= '</th>';
	
	$page .= '<th>';
	$page .= t('EVENT_CATEGORY');
	$page .= '</th>';

	$page .= '<th>';
	$page .= t('EVENT_ACTION');
	$page .= '</th>';
	
	$page .= '<th>';
	$page .= t('EVENT_PROCESSED');
	$page .= '</th>';

	$page .= '</tr>';
  
	$page .= '</th>';

	while ($row = plaatprotect_db_fetch_object($result)) {
		$page .= '<tr>';
		$page .= '<td>';
		$page .= $row->timestamp;
		$page .= '</td>';

		$page .= '<td>';
		$page .= plaatprotect_ago($row->timestamp);
		$page .= '</td>';

		$page .= '<td>';
		
		switch ($row->category) {
						
			case CATEGORY_ZWAVE: 
				$page .= 'Zwave';
				break;		
			
			case CATEGORY_ZWAVE_CONTROL: 
				$page .= '[Zwave]';
				break;	
				
			case CATEGORY_ZIGBEE: 
				$page .= 'Zigbee';
				break;	
				
			case CATEGORY_MOBILE: 
				$page .= 'Mobile';
				break;	
		
			case CATEGORY_EMAIL: 
				$page .= 'Email';
				break;	
				
			case CATEGORY_DRONE: 
				$page .= 'Drone';
				break;	
		
			default: 
				$page .= 'General';
				break;		
		}
	
		$page .= '</td>';

		$page .= '<td>';
		$page .= $row->action;
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= $row->processed;
		$page .= '</td>';
				
		$page .= '<tr>';
    }
	$page .= '</table>';

	$page .= '<div class="nav">';
	$page .= plaatprotect_link('pid='.$pid.'&eid='.EVENT_PREV.'&eid2='.$eid2.'&id='.$id, t('LINK_PREV'));
	$page .= plaatprotect_link('pid='.PAGE_HOME, t('LINK_HOME'));
	$page .= plaatprotect_link('pid='.$pid.'&eid='.EVENT_NEXT.'&eid2='.$eid2.'&id='.$id, t('LINK_NEXT'));
	
	if ($eid2 == EVENT_FILTER) {
		$page .= plaatprotect_link('pid='.$pid, t('LINK_FILTER_OFF'));
	} else {
		$page .= plaatprotect_link('pid='.$pid.'&eid2='.EVENT_FILTER, t('LINK_FILTER_ON'));
	} 
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
function plaatprotect_event_view() {

	/* input */
	global $pid;
	global $eid;
	global $id;
  
	/* Event handler */
	switch ($eid) {
      
		case EVENT_NEXT:				
			$id--;
			if ($id<0) {
				$id=0;
			}
			break;

		case EVENT_PREV;
			$id++;
			break;
   }
	
  /* Page handler */
  switch ($pid) {

     case PAGE_EVENT_VIEW:
        return plaatprotect_event_view_page();
        break;
  }
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>

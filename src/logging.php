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
** PAGE
** ---------------------
*/

/**
 * plaatprotect hue page
 * @return HTML block which page contain.
 */
function plaatprotect_logging_page() {

	global $pid;

        $page ="<style>input[type='checkbox']{width:24px;height:24px}</style>";
	$page .= '<h1>'.t('TITLE_LOGGING').'</h1>';
	$page .= '<br>';
	
	$sql  = 'select timestamp, nodeid, event, value from event order by timestamp desc limit 0,20 ';
	$result = plaatprotect_db_query($sql);

	$page .= '<table border=1 >';

	$page .= '<tr>';

	$page .= '<th>';
	$page .= 'Timestamp';
	$page .= '</th>';

	$page .= '<th>';
	$page .= 'Node';
	$page .= '</th>';

	$page .= '<th>';
	$page .= 'Event';
	$page .= '</th>';

	$page .= '<th>';
	$page .= 'Value';
	$page .= '</th>';

	$page .= '</tr>';
  
	$page .= '</th>';

	while ($row = plaatprotect_db_fetch_object($result)) {
		$page .= '<tr>';
		$page .= '<td>';
		$page .= $row->timestamp;
		$page .= '</td>';

		$page .= '<td>';
		$page .= 'Node '.$row->nodeid;
		$page .= '</td>';

		$page .= '<td>';
		$page .= $row->event;
		$page .= '</td>';

		$page .= '<td>';
		if ($row->value==0) {
			$page .= 'off';
		} else { 
			$page .= 'on';
		}
		$page .= '</td>';
		$page .= '<tr>';
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
function plaatprotect_logging() {

  /* input */
  global $pid;

  /* Page handler */
  switch ($pid) {

     case PAGE_LOGGING:
        return plaatprotect_logging_page();
        break;
  }
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>

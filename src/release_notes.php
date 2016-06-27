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
 * @brief contain release notes
 */
 
/*
** ---------------------
** NOTES
** ---------------------
*/

$note[2] = '<div class="subparagraph">Version 0.2 (27-06-2016)</div>
<div class="large_text">
<ul>
<li>General: Added vibration alarm detection.</li>
<li>General: Added zwave node status on home page.</li>
<li>General: Improve webcam error recovery.</li>
</ul>
</div>';

$note[1] = '<div class="subparagraph">Version 0.1 (21-06-2016)</div>
<div class="large_text">
<ul>
<li>General: Add Notify My Android (NMA) webservice integration for push messages to andriod phone.</li>
<li>General: Add basic support for two webcams.</li>
<li>General: Add option to make picture from webcam view.</li>
<li>General: Add motion detection with automatic recording to webcam sensor script.</li>
<li>General: Add option to navigate through webcam recordings.</li>
<li>General: Add basic support for Philips HUE lighting system.</li>
<li>General: Passwords are now encrypted stored in database.</li>
</ul>
</div>';

/*
** ---------------------
** PAGES
** ---------------------
*/

function plaatprotect_release_notes_page(){

  global $pid;
  global $id;
  global $note;
  
  $page  = '<h1>Release Notes</h1>';
  $page .= '<br/>';
  
  $page .= $note[$id];
  
  $page .= '<div class="nav">';
  $page .= plaatprotect_link('pid='.$pid.'&eid='.EVENT_PREV.'&id='.$id, t('LINK_PREV'));
  $page .= plaatprotect_link('pid='.PAGE_HOME, t('LINK_HOME'), 'home');
  $page .= plaatprotect_link('pid='.$pid.'&eid='.EVENT_NEXT.'&id='.$id, t('LINK_NEXT'));
  $page .= '</div>';

  return $page;
}

/*
** ---------------------
** HANDLER
** ---------------------
*/

/**
 * Help handler
 */
function plaatprotect_release_notes() {

	/* input */
	global $max;
	global $pid;
	global $eid;
	global $id;
	global $note;

	if($id==0) {
		$id = sizeof($note);
	}
	
	/* Event handler */
	switch ($eid) {
      
		case EVENT_NEXT:
			if ($id<sizeof($note)) {
				$id++;
			}
			break;

		case EVENT_PREV:
			if ($id>1) {
				$id--;
			}
			break;
   }

	/* Page handler */
	switch ($pid) {

		case PAGE_RELEASE_NOTES:
			return plaatprotect_release_notes_page();
			break;
	}
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>

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
 * @brief contain setting page
 */
 
/*
** ---------------------
** PARAMETERS
** ---------------------
*/

$value = plaatprotect_post("value", "");
$password = plaatprotect_post("password", "");

$sql  = 'select id from config where category='.$cat.' and readonly=0';
$result = plaatprotect_db_query($sql);
$count = plaatprotect_db_num_rows($result);
$step = 8;
$max = 0;
if ($count>$step) {
	$max = 1;
}
	
/*
** ---------------------
** EVENTS
** ---------------------
*/

function plaatprotect_setting_login_event() {

	global $pid;
	global $password;
	
	$settings_password = plaatprotect_db_config_value('settings_password',CATEGORY_SECURITY);
	
	if ((strlen($settings_password)==0) || ($settings_password==md5($password))) {
	
		// Correct password, redirect to setting page
		$pid = PAGE_SETTING_CATEGORY;
	}
}
	
function plaatprotect_setting_save_event() {

    // input
	global $id;
	global $value;

	$sql  = 'select encrypt from config where id='.$id;
	$result = plaatprotect_db_query($sql);
	$row = plaatprotect_db_fetch_object($result);
	
	if (($row->encrypt==1) && (strlen($value)>0)) {
		$value = md5($value);
	}
		
	$sql  = 'update config set value="'.$value.'", date=SYSDATE() where id='.$id;		
	plaatprotect_db_query($sql);
	
	$sql  = 'select rebuild from config where id='.$id;		
	$result = plaatprotect_db_query($sql);
	$row = plaatprotect_db_fetch_object($result);
	
	if ($row->rebuild==1) {
		plaatprotect_db_process(EVENT_PROCESS_ALL_DAYS);
	}
}

/*
** ---------------------
** PAGE
** ---------------------
*/

function plaatprotect_setting_login_page() {

   // input
   global $id;
			
   $page  = ' <h1>'.t('SETTING_TITLE').'</h1>';

   $page .= '<br/>';
   $page .= '<label>'.t('LABEL_PASSWORD').'</label>';
   $page .= '<input type="password" name="password" size="20" />';
   $page .= '<br/>';

   $page .= '<div class="nav">';
   $page .= plaatprotect_link('pid='.PAGE_HOME, t('LINK_CANCEL'));   
   $page .= '<input type="hidden" name="token" value="pid='.PAGE_SETTING_LOGIN.'&eid='.EVENT_LOGIN.'"/>';
   $page .= '<input type="submit" name="Submit" id="normal_link" value="'.t('LINK_LOGIN').'"/>';
   $page .= '</div>';
   
   /* Set focus on first input element */
	$page .= '<script type="text/javascript" language="JavaScript">';
	$page .= 'document.forms[\'plaatprotect\'].elements[\'password\'].focus();';
	$page .= '</script>';
      
   $page .= '</div>';
	
   return $page;
}

function plaatprotect_setting_edit_page() {

   // input
   global $id;
	global $cat;

	$sql  = 'select token, value, options, encrypt from config where id='.$id;
	$result = plaatprotect_db_query($sql);
	$row = plaatprotect_db_fetch_object($result);

	$page  = ' <h1>'.t('SETTING_TITLE').' - '.t('CATEGORY'.$cat).'</h1>';

	$page .= '<br/>';
	$page .= '<label>'.t($row->token).'</label>';
	$page .= '<br/>';
	
	if (strlen($row->options)>0) {	   
		$options = explode(",", $row->options);		
		$page .= '<select name="value" >';		
		foreach ($options as $option) {
			if ($row->value==$option) {
				$page .= '<option selected="selected" value="'.$option.'">'.$option.'</option>';
 			} else {
				$page .= '<option value="'.$option.'">'.$option.'</option>';
			}
		}
		$page .= '</select>';
    } else {	   
		if ($row->encrypt==1) { 
			$page .= '<input type="text" name="value" value="" size="40" />';
		} else {
		   $page .= '<input type="text" name="value" value="'.$row->value.'" size="40" />';
		}
	}
	$page .= '<br/>';
 
	$page .= '<div class="nav">';
	$page .= plaatprotect_link('pid='.PAGE_SETTING_LIST.'&cat='.$cat, t('LINK_CANCEL'));
	$page .= plaatprotect_link('pid='.PAGE_SETTING_LIST.'&eid='.EVENT_SAVE.'&id='.$id.'&cat='.$cat, t('LINK_SAVE'));
	$page .= '</div>';
	
	return $page;
}

function plaatprotect_setting_list_page() {

   // input
	global $pid;
	global $cat;
	global $limit;
	global $step;
	global $max;

	$sql  = 'select id, token, value, encrypt from config where readonly=0 and category='.$cat.' order by token limit '.($limit*$step).','.$step;
	$result = plaatprotect_db_query($sql);
	
	$page  = ' <h1>'.t('SETTING_TITLE').' - '.t('CATEGORY'.$cat).'</h1>';

	$page .= '<br/>';
	
	$page .= '<div class="setting">';
	$page .= '<table>';
	$page .= '<tr>';
	$page .= '<th width="175">'.t('LABEL_TOKEN').'</th>';
	$page .= '<th width="150">'.t('LABEL_VALUE').'</th>';
	$page .= '<th width="300">'.t('LABEL_DESCRIPTION').'</th>';
	$page .= '</tr>';
	
	while ($row = plaatprotect_db_fetch_object($result)) {
	
		$page .= '<tr>';
		$page .= '<td width="175">'.plaatprotect_link('pid='.PAGE_SETTING_EDIT.'&id='.$row->id.'&cat='.$cat, $row->token).'</td>';
		$page .= '<td width="150">';		
		if ((strlen($row->value)>0) && ($row->encrypt==1)) {
			$page .= '*************';
		} else {
			$page .= $row->value;
		}
		$page .= '</td>';
		$page .= '<td width="300">'.t($row->token).'</td>';
		$page .= '</tr>';
	}
	$page .= '</table>';
	$page .= '</div>';
	 
	$page .= '<div class="nav">';
	if ($max>0) {
		$page .= plaatprotect_link('pid='.$pid.'&eid='.EVENT_PREV.'&cat='.$cat.'&limit='.$limit, t('LINK_PREV'));
	}
	$page .= plaatprotect_link('pid='.PAGE_SETTING_CATEGORY, t('LINK_BACK'));
	if ($max>0) {
		$page .= plaatprotect_link('pid='.$pid.'&eid='.EVENT_NEXT.'&cat='.$cat.'&limit='.$limit, t('LINK_NEXT'));
	}
	$page .= '</div>';
	
	return $page;
}


function plaatprotect_setting_category_page() {

   // input
	global $pid;
	global $limit;
	global $step;

	$sql  = 'select category from config group by category order by category';
	$result = plaatprotect_db_query($sql);
	
	$page  = '<h1>'.t('SETTING_TITLE').'</h1>';
	
	$page .= '<div class="setting">';
	$page .= '<table>';
	
	$count = 0;
	while ($row = plaatprotect_db_fetch_object($result)) {
	
		if (($count%3)==0) {
			$page .= '<tr>';
		}
		$page .= '<td width="200">'.plaatprotect_link('pid='.PAGE_SETTING_LIST.'&cat='.$row->category, i('cog').t('CATEGORY'.$row->category)).'</td>';
		if (($count%3)==3) {
			$page .= '</tr>';
		}
		
		$count++;
		
	}
	$page .= '</table>';
	$page .= '</div>';

	$page .= '<div class="nav">';

	$page .= plaatprotect_link('pid='.PAGE_HOME, t('LINK_HOME'));
	$page .= '</div>';
	
	return $page;
}

/*
** ---------------------
** HANDLER
** ---------------------
*/

function plaatprotect_settings() {

	/* input */
	global $pid;
	global $eid;
	global $max;
	global $limit; 
	
  /* Event handler */
	switch ($eid) {

		case EVENT_NEXT:
			if ($limit<$max) {
				$limit++;
			}
			break;

		case EVENT_PREV:
			if ($limit>0) {
				$limit--;
			}
			break;
		
		case EVENT_SAVE:
			plaatprotect_setting_save_event();
			break;
		  
		case EVENT_LOGIN:
			plaatprotect_setting_login_event();
			break;		
   }

  /* Page handler */
  switch ($pid) {

		case PAGE_SETTING_LOGIN:
			return plaatprotect_setting_login_page();
			break;
			
		case PAGE_SETTING_CATEGORY:
			return plaatprotect_setting_category_page();
			break;
		
		case PAGE_SETTING_LIST:
			return plaatprotect_setting_list_page();
			break;
	
		case PAGE_SETTING_EDIT:
			return plaatprotect_setting_edit_page();
			break;
			
  }
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>

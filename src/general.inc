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
 * @brief contain general logic
 */

/*
** -----------
** DIRECTORY
** -----------
*/

// Installation path
define('BASE_DIR', '/var/www/html/plaatprotect');
 
/*
** -----------
** PAGES
** -----------
*/

define('PAGE_HOME_LOGIN',           10);
define('PAGE_HOME',                 11);

define('PAGE_LOGGING',              12);
define('PAGE_WEBCAM',               13);
define('PAGE_ARCHIVE',              14);
define('PAGE_IMAGE_VIEWER',         15);
define('PAGE_HUE',                  16);
define('PAGE_ZWAVE',                17);
define('PAGE_ABOUT',                18);
define('PAGE_DONATE',               19);
define('PAGE_RELEASE_NOTES',        20);

define('PAGE_SETTING_LOGIN',        30);
define('PAGE_SETTING_CATEGORY',     31);
define('PAGE_SETTING_LIST',         32);
define('PAGE_SETTING_EDIT',         33);

/*
** -----------
** EVENTS
** -----------
*/

define('EVENT_NONE',                110);
define('EVENT_PREV',                111);
define('EVENT_NEXT',                112);
define('EVENT_SAVE',                113);
define('EVENT_BACKUP',              114);
define('EVENT_EXPORT',              115);
define('EVENT_LOGIN',               116);
define('EVENT_SCHEME',              117);
define('EVENT_LANGUAGE',            118);
define('EVENT_DELETE',              119);
define('EVENT_PICTURE',             120);
define('EVENT_VIEW',                121);
define('EVENT_PLAY',                122);
define('EVENT_NEXT_FAST',           123);
define('EVENT_PREV_FAST',           124);
define('EVENT_STOP',                125);
define('EVENT_BEGIN',               126);
define('EVENT_END',                 127);
define('EVENT_UPDATE',              128);

/*
** -----------
** CATEGORY
** -----------
*/

define('ZWAVE',                     11);
define('SECURITY',                  51);
define('LOOK_AND_FEEL',             52);
define('WEBCAM_1',                  61);
define('WEBCAM_2',                  62);
define('HUE',                       71);
define('NOTIFICATION',              81);

/*
** -----------
** CONSTANTS
** -----------
*/

/*
** -----------
** NOTIFCATION
** serverity
** 2 = top
** 1 = high
** 0 = normal
** -1 = low
** -2 = none
** -----------
*/

function plaatprotect_notification($topic, $content, $severity=0) {

  $notification_present = plaatprotect_db_get_config_item('notification_present', NOTIFICATION);

  if ($notification_present=="true" ) {
  	$notification_nma_key = plaatprotect_db_get_config_item('notification_nma_key', NOTIFICATION);

  	require_once 'nmaApi.class.php';

  	$nma = new nmaApi(array('apikey' => $notification_nma_key));
		if($nma->verify()) {
         	$nma->notify('plaatprotect', $topic, $content, $severity );
		}
   }
}

/*
** -----------
** LOCK
** -----------
*/

function plaatprotect_islocked() { 
    if( file_exists( LOCK_FILE ) ) { 

        $lockingPID = trim( file_get_contents( LOCK_FILE ) ); 
        $pids = explode( "\n", trim( `ps -e | awk '{print $1}'` ) ); 
        if( in_array( $lockingPID, $pids ) )  return true; 
        unlink( LOCK_FILE ); 
    } 
    
    file_put_contents( LOCK_FILE, getmypid() . "\n" ); 
    return false; 
} 


/*
** -----------
** PAGE
** -----------
*/

/**
 * Language function 
 * @return Combine string in selected language
 */
function t() {

	global $lang;
	
   $numArgs = func_num_args();

   $temp = $lang[func_get_arg(0)];

   $pos = 0;
   $i = 1;

   while (($pos = strpos($temp, "%s", $pos)) !== false) {
      if ($i >= $numArgs) {
         throw new InvalidArgumentException("Not enough arguments passed.");
		}

      $temp = substr($temp, 0, $pos) . func_get_arg($i) . substr($temp, $pos + 2);
      $pos += strlen(func_get_arg($i));
      $i++;
   }      
	
	$temp = mb_convert_encoding($temp, "UTF-8", "HTML-ENTITIES" ); 
   return $temp; 
}

/**
 * Add title icon 
 */
function add_icons() {
	
	// Charset
	$page = '<meta charset="UTF-8">';
	
	// Normal icons
	$page .= '<link rel="shortcut icon" type="image/png" sizes="16x16" href="images/16.png">';
	$page .= '<link rel="shortcut icon" type="image/png" sizes="24x24" href="images/24.png">';
	$page .= '<link rel="shortcut icon" type="image/png" sizes="32x32" href="images/32.png">';
	$page .= '<link rel="shortcut icon" type="image/png" sizes="48x48" href="images/48.png">';
	$page .= '<link rel="shortcut icon" type="image/png" sizes="64x64" href="images/64.png">';
	$page .= '<link rel="shortcut icon" type="image/png" sizes="128x128" href="images/128.png">';
	$page .= '<link rel="shortcut icon" type="image/png" sizes="256x256" href="images/256.png">';
	$page .= '<link rel="shortcut icon" type="image/png" sizes="512x512" href="images/512.png">';
	
	// Apple icons
	$page .= '<link rel="apple-touch-icon" type="image/png" href="images/apple-60.png">';
	$page .= '<link rel="apple-touch-icon" type="image/png" sizes="76x76" href="images/apple-76.png">';
	$page .= '<link rel="apple-touch-icon" type="image/png" sizes="120x120" href="images/apple-120.png">';
	$page .= '<link rel="apple-touch-icon" type="image/png" sizes="152x152" href="images/apple-152.png">';
	
	// Web app cable (runs the website as app)
	$page .= '<meta name="apple-mobile-web-app-capable" content="yes">';
	$page .= '<meta name="mobile-web-app-capable" content="yes">';

	// Workarround to get transparant Google Charts
	//$page .= '<style>rect{fill:none;}</style>'; 
	
	// Title
	$page .= '<title>'.t('TITLE').'</title>';
	   
	return $page;
}

function loadCSS($url) {
	return '<link href="'.$url.'" rel="stylesheet" type="text/css" />';
	//return '<style>' . file_get_contents($url) . '</style>';
}


function loadJS($url) {
	return '<script language="JavaScript" src="'.$url.'" type="text/javascript"></script>';	
	//return '<script>' . file_get_contents($url) . '</script>';
}

/**
 * General header
 */
function general_header() {

  // input
  global $ip;
  global $pid;
  global $eid;
  global $sid;
  global $date;
  global $session;

  $sql  = 'select theme,language from session where ip="'.$ip.'"';
  $result = plaatprotect_db_query($sql);
  $row = plaatprotect_db_fetch_object($result);

  $lang = $row->language;
  $theme = $row->theme;
    
  $page  = '<!DOCTYPE html>';
  $page .= '<html>';
  $page .= '<head>'; 

  $page .= add_icons();

  $page .= loadJS('js/link.js');
  
   $page .= loadCSS('css/general.css');

    // Load the icons from Font Awesome not with loadCSS because this file never will change and it cant load the font
    $page .= '<link rel="stylesheet" type="text/css" href="css/font-awesome.min.css"/>';

    // Load the dark css theme if db theme var = dark
    if ($theme == "dark") {
	$page .= loadCSS('css/theme-dark.css');
    }
  
  $page .= '</head>';
  
  $page .= '<body>';
  $page .= '<form id="plaatprotect" method="POST">';  
  
  $page .= '<input type="hidden" name="session" value="'.$session.'" />';
  
	$page .= '<div class="language">';
	if ($lang=="en") {
		$page .= plaatprotect_normal_link('pid='.$pid.'&eid='.$eid.'&date='.$date.'&sid='.EVENT_LANGUAGE, t('DUTCH'));
	} else { 
		$page .= plaatprotect_normal_link('pid='.$pid.'&eid='.$eid.'&date='.$date.'&sid='.EVENT_LANGUAGE , t('ENGLISH'));
	}
	$page .= '</div>';
	
	$page .= '<div class="theme">';
	if ($theme == "light") {
		$page .= plaatprotect_normal_link('pid='.$pid.'&eid='.$eid.'&date='.$date.'&sid='.EVENT_SCHEME, t('THEME_TO_DARK'));
	} else {
		$page .= plaatprotect_normal_link('pid='.$pid.'&eid='.$eid.'&date='.$date.'&sid='.EVENT_SCHEME, t('THEME_TO_LIGHT'));		
	}
	$page .= '</div>';
   
	return $page;
}

/**
 * General footer
 */
function general_footer($time=0) {
	global $pid;
	
	$page = '';
	
	$page .= '<div class="copyright">'.t('LINK_COPYRIGHT') .' - '.t('TITLE').'<br/>';
	$page .= '['.round($time*1000).' ms - '.plaatprotect_db_count().' queries]';
	$page .= '</div>';

	$page .= '</form>';
	$page .= '</body>';
	$page .= '</html>';
  
	return $page;
}

/**
 * Set cookie
 */
function set_cookie_and_refresh ($name, $value) {
	setcookie($name, $value, time() + (86400 * 30), "/");
	header("Location: " . $_SERVER['PHP_SELF']);
}

/**
 * Set icon to link
 */
function i ($name) { 
	$icon = '<i class="fa fa-' . $name;
	if ($name == 'chevron-right') {
		$icon .= ' right';
	}
	$icon .= ' fa-fw"></i>';
	return $icon;
}

// ----------------------------
// NAVIGATION
// ----------------------------

function plaatprotect_get($label, $default) {
	
	$value = $default;
	
	if (isset($_GET[$label])) {
		$value = $_GET[$label];
		$value = stripslashes($value);
		$value = htmlspecialchars($value);
	}
	
	return $value;
}

/**
 * Process post parameters 
 */
function plaatprotect_post($label, $default) {
	
	$value = $default;
	
	if (isset($_POST[$label])) {
		$value = $_POST[$label];
		$value = stripslashes($value);
		$value = htmlspecialchars($value);
	}
	
	return $value;
}

/** 
 * Encode link data
 */
function plaatprotect_token_decode($token) {
	
	return htmlspecialchars_decode($token);
}

/** 
 * Encode link data
 */
function plaatprotect_token_encode($token) {
   
	return htmlspecialchars($token);	
}

/**
 * Create button like link 
 */
function plaatprotect_link($parameters, $label, $title="") {
   
	global $link_counter;
	
	$link_counter++;
	
	$link  = '<a href="javascript:link(\''.plaatprotect_token_encode($parameters).'\');" class="link" ';			
	
	if (strlen($title)!=0) {
		$link .= ' title="'.strtolower($title).'"';
	}
	
	$link .= '>'.$label.'</a>';	
	return $link;
}

/**
 * Create hyperlink like link 
 */
function plaatprotect_normal_link($parameters, $label, $id="", $title="") {
   
	global $link_counter;
	
	$link_counter++;
	
	$link  = '<a href="javascript:link(\''.plaatprotect_token_encode($parameters).'\');" class="normal_link" ';			
	if (strlen($id)!=0) {
		$link .= ' id="'.strtolower($id).'"';
	}
	if (strlen($title)!=0) {
		$link .= ' title="'.strtolower($title).'"';
	}
	$link .= '>'.$label.'</a>';	
	return $link;
}

function plaatprotect_create_path($path) {
    if (is_dir($path)) return true;
    $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
    $return = plaatprotect_create_path($prev_path);
    umask(0);
    return ($return && is_writable($prev_path)) ? mkdir($path, 0777) : false;
}

/** 
 * @mainpage plaatprotect Documentation
 *   Welcome to the plaatprotect documentation.
 *
 * @section Introduction
 *   plaatprotect is a burglar alarm center for raspberry Pi. Its collect Zwave sensor data
 *   and and active a configured counter measure. With a web GUI the system can be controlled.
 *
 * @section Links
 *   Website: http://www.plaatsoft.nl
 *   Code: https://github.com/wplaat/plaatprotect
 *
 * @section Credits
 *   Documentation: wplaat\n
 *
 * @section Licence
 *   <b>Copyright (c) 2008-2016 Plaatsoft</b>
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *   
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *   
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
 
// ----------------------------
// THE END
// ----------------------------




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
define('PAGE_EVENT_VIEW',           12);
define('PAGE_WEBCAM',               13);
define('PAGE_ARCHIVE',              14);
define('PAGE_IMAGE_VIEWER',         15);
define('PAGE_ZIGBEE',               16);
define('PAGE_ZWAVE',                17);
define('PAGE_ZWAVE_EDIT',           18);
define('PAGE_ABOUT',                19);
define('PAGE_DONATE',               20);
define('PAGE_RELEASE_NOTES',        21);
define('PAGE_MOTION',               22);
define('PAGE_ACTOR',                23);
define('PAGE_SETTING_LOGIN',        24);
define('PAGE_SETTING_CATEGORY',     25);
define('PAGE_SETTING_LIST',         26);
define('PAGE_SETTING_EDIT',         27);
define('PAGE_BATTERY',              28);
define('PAGE_TEMPERATURE',          29);
define('PAGE_HUMIDITY',             30);
define('PAGE_LUMINANCE',				31);

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
define('EVENT_SWITCH_SCENARIO',     129);
define('EVENT_ON',                  130);
define('EVENT_OFF',                 131);
define('EVENT_EDIT',                132);
define('EVENT_FILTER',              133);
define('EVENT_REFRESH',             134);

/*
** -----------
** SCENARIO
** -----------
*/

define('SCENARIO_HOME',   				1);
define('SCENARIO_SLEEP',  				2);
define('SCENARIO_AWAY',  				3);
define('SCENARIO_PANIC',  				4);

define('PANIC_ON',        				1);
define('PANIC_OFF',       				0);

/*
** -----------
** CATEGORY
** -----------
*/

define('CATEGORY_GENERAL',           0);
define('CATEGORY_ZWAVE',            11);
define('CATEGORY_ZWAVE_CONTROL',    12);
define('CATEGORY_EMAIL',            21);
define('CATEGORY_DRONE',            31);
define('CATEGORY_SECURITY',         51);
define('CATEGORY_LOOK_AND_FEEL',    52);
define('CATEGORY_WEBCAM_1',         61);
define('CATEGORY_WEBCAM_2',         62);
define('CATEGORY_ZIGBEE',           71);
define('CATEGORY_MOBILE',           81);

/**
 ********************************
 * LOG
 ********************************
 */
 
function plaatprotect_log($text) {

  $t = microtime(true);
  $micro = sprintf("%06d",($t - floor($t)) * 1000000);
  $d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );

  print $d->format("Y-m-d H:i:s.u");
  echo " ".$text."\r\n";
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
		
	$theme="light";
	$lang="en";
	if (isset($row->language)) {
		$lang = $row->language;
		$theme = $row->theme;
	}
    
	$page  = '<!DOCTYPE html>';	
	$page .= '<html>';
	$page .= '<head>'; 

  $page .= add_icons();

  $page .= loadJS('js/link2.js');
  
   $page .= loadCSS('css/general1.css');

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

function plaatprotect_ago($timestamp) {

	$seconds = time()-strtotime($timestamp);
	$minutes = round($seconds / 60);
	$hours = round($seconds / (60*60));
	$days = round($seconds / (60*60*24));
	
	if ($seconds<60) {
		return $seconds.' '.t('SECONDS');
	} else if (($minutes>0) && ($minutes==1)) {
		return $minutes.' '.t('MINUTE');
	} else if (($minutes>0) && ($minutes<60)) {
		return $minutes.' '.t('MINUTES');
	} else if (($hours>0) && ($hours==1)) {
		return $hours.' '.t('HOUR');
	} else if (($hours>0) && ($hours<24)) {
		return $hours.' '.t('HOURS');
	} else if (($days>0) && ($days==1)) {
		return $days.' '.t('DAY');
	} else {
		return $days.' '.t('DAYS');
	}
}

function plaatprotect_flip($value) {
	if($value==1) {
		return 0;
	} else {
		return 1;
	}
}

// ----------------------------
// NAVIGATION
// ----------------------------

function plaatprotect_dayofweek($value) {

    list($year, $month, $day) = explode("-", $value);
    return t("DAY_".jddayofweek( cal_to_jd(CAL_GREGORIAN, $month, $day, $year))); 
}

/**
 * Get previous day
 */
function plaatprotect_prev_day($date) {

  list($year, $month, $day) = explode("-", $date);

  $prev_day=$day-1;
  $prev_month=$month;
  $prev_year=$year;   

  if ($prev_day<=0) {
     $prev_month=$month-1;
     $prev_year=$year;
     $prev_day=date("t", strtotime($prev_year.'-'.$prev_month.'-1'));
  }

  if ($prev_month<=0) {
     $prev_month=12;
     $prev_year=$year-1;
     $prev_day=date("t", strtotime($prev_year.'-'.$prev_month.'-1'));
  }
  
  return $prev_year.'-'.$prev_month.'-'.$prev_day; 
}

/**
 * Get next day
 */
function plaatprotect_next_day($date) {

  list($year, $month, $day) = explode("-", $date);

  $next_day=$day+1;   
  $next_month=$month;
  $next_year=$year;   
  
  if ($next_day>date("t", strtotime($next_year.'-'.$next_month.'-1'))) {
     $next_day=1;
     $next_month=$next_month+1;
     $next_year=$year;
  }
  
  if ($next_month>12) {
     $next_day=1;
     $next_month=1;
     $next_year=$year+1;
  }
  
  return $next_year.'-'.$next_month.'-'.$next_day; 
}

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
   	
	$link  = '<a href="javascript:link(\''.plaatprotect_token_encode($parameters).'\');" class="link" ';			
	
	if (strlen($title)!=0) {
		$link .= ' title="'.strtolower($title).'"';
	}
	
	$link .= '>'.$label.'</a>';	
	return $link;
}

/**
 * Create hidden link with popup
 */ 
function plaatprotect_link_confirm($parameters, $label, $question="") {
   			
	$link  = '<a href="javascript:show_confirm(\''.$question.'\',\''.plaatprotect_token_encode($parameters).'\');" class="link" ';
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
 *   <b>Copyright (c) 1996-2019 Plaatsoft</b>
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




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
 * @brief contain database logic
 */
 
/*
** ---------------------
** SETTINGS
** ---------------------
*/

define('DEBUG', 0);
$db = "";

/*
** ---------------------
** GENERAL
** ---------------------
*/

/**
 * connect to database
 * @param $dbhost database hostname
 * @param $dbuser database username
 * @param $dbpass database password
 * @param $dbname database name
 * @return connect result (true = successfull connected | false = connection failed)
 */
function plaatprotect_db_connect($dbhost, $dbuser, $dbpass, $dbname) {

	global $db;

   $db = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);	
	if (mysqli_connect_errno()) {
		plaatprotect_db_error();
		return false;		
	}
	return true;
}

/**
 * Disconnect from database  
 * @return disconnect result
 */
function plaatprotect_db_close() {

	global $db;

	mysqli_close($db);

	return true;
}

/**
 * Show SQL error 
 * @return HTML formatted SQL error
 */
function plaatprotect_db_error() {

	if (DEBUG == 1) {
		echo mysqli_connect_error(). "<br/>\n\r";
	}
}

/**
 * Count queries 
 * @return queries count
 */
$query_count=0;
function plaatprotect_db_count() {

	global $query_count;
	return $query_count;
}

/**
 * Execute database multi query
 */
function plaatprotect_db_multi_query($queries) {

	$tokens = @preg_split("/;/", $queries);
	foreach ($tokens as $token) {
	
		$token=trim($token);
		if (strlen($token)>3) {
			plaatprotect_db_query($token);		
		}
	}
}

/**
 * Execute database query
 * @param $query SQL query with will be executed.
 * @return Database result
 */
function plaatprotect_db_query($query) {
			
	global $query_count;
	global $db;
	
	$query_count++;

	if (DEBUG == 1) {
		echo $query."<br/>\r\n";
	}

	$result = mysqli_query($db, $query);

	if (!$result) {
		plaatprotect_db_error();		
	}
	
	return $result;
}

/**
 * escap database string
 * @param $data  input.
 * @return $data escaped
 */
function plaatprotect_db_escape($data) {

	global $db;
	
	return mysqli_real_escape_string($db, $data);
}

/**
 * Fetch query result 
 * @return mysql data set if any
 */
function plaatprotect_db_fetch_object($result) {
	
	$row = $result->fetch_object();
	return $row;
}

/**
 * Return number of rows
 * @return number of row in dataset
 */
function plaatprotect_db_num_rows($result) {
	
	return mysqli_num_rows($result);
}

/*
** ---------------------
** DB UPDATE
** ---------------------
*/

function startsWith($haystack, $needle){
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

/**
 * Execute SQL script
 * @param $version Version of sql patch file
 */
function plaatprotect_db_execute_sql_file($version) {

    $filename = 'database/patch-'.$version.'.sql';
    $commands = file_get_contents($filename);
	 
    //delete comments
    $lines = explode("\n",$commands);
    $commands = '';
    foreach($lines as $line){
        $line = trim($line);
        if( $line && !startsWith($line,'--') ){
            $commands .= $line . "\n";
        }
    }

    //convert to array
    $commands = explode(";\n", $commands);

    //run commands
    $total = $success = 0;
    foreach($commands as $command){
        if(trim($command)) {
				if (DEBUG == 1) {
					echo $command."<br/>\n\r";
				}
            $success += (@plaatprotect_db_query($command)==false ? 0 : 1);
            $total += 1;
        }
    }

    //return number of successful queries and total number of queries found
    return array(
        "success" => $success,
        "total" => $total
    );
}

/**
 * Check db version and upgrade if needed!
 */
function plaatprotect_db_check_version() {

   // Execute SQL base sql script if needed!
   $sql = "select 1 FROM config limit 1" ;
   $result = plaatprotect_db_query($sql);
   if (!$result)  {
      plaatprotect_db_execute_sql_file("0.1");
   }
		
	// Execute SQL path script v0.2 if needed
	$value = plaatprotect_db_config_value('database_version', CATEGORY_GENERAL);
   if ($value=="0.1")  { 
		plaatprotect_db_execute_sql_file("0.2");
   }
	
	// Execute SQL path script v0.3 if needed
	$value = plaatprotect_db_config_value('database_version', CATEGORY_GENERAL);
   if ($value=="0.2")  { 
		plaatprotect_db_execute_sql_file("0.3");
   }
}

/*
** ---------------------
** SESSION
** ---------------------
*/

function plaatprotect_db_get_session($ip, $new=false) {

   $sql = 'select sid, timestamp, session_id, requests from session where ip="'.$ip.'"';
   $result = plaatprotect_db_query($sql);
   $data = plaatprotect_db_fetch_object($result);

   $session_id = "";
   if ( isset($data->sid) ) {   
	
		$session_id = $data->session_id;
		$requests = $data->requests;
	
		if (($new==true) || ((time()-strtotime($data->timestamp))>(60*15))) {		
			$session_id = md5(date('Y-m-d H:i:s'));
		}
			   
		$sql = 'update session set timestamp=SYSDATE(), session_id="'.$session_id.'", requests='.++$requests.' where sid="'.$data->sid.'"';
	   plaatprotect_db_query($sql);
	  
   } else {

		$sql = 'insert into session (timestamp, ip, requests, language, theme) value (SYSDATE(), "'.$ip.'", 1, "en", "light")';
		plaatprotect_db_query($sql);
	}

   return $session_id;
}

/*
** ---------------------
** CRON
** ---------------------
*/

/**
 * Update Cron
 */
function plaatprotect_db_cron_update($cid) {
		
	$query  = 'update cron set '; 
	$query .= 'last_run = "'.date("Y-m-d H:i:s").'" ';
	$query .= 'where cid='.$cid; 
	
	return plaatprotect_db_query($query);
}

/*
** ---------------------
** CONFIG
** ---------------------
*/

function plaatprotect_db_config_value($key, $category=CATEGORY_GENERAL) {

	$value="";
	
   $sql = 'select value from config where token="'.$key.'" and category='.$category;
   $result = plaatprotect_db_query($sql);
   $data = plaatprotect_db_fetch_object($result);

	if (isset($data->value)) {
		$value = $data ->value;
	}
			
   return $value;
}

function plaatprotect_db_config($key, $category=0) {

   $sql = 'select id, category, token, value from config where token="'.$key.'" and category='.$category;
   $result = plaatprotect_db_query($sql);
   $data = plaatprotect_db_fetch_object($result);

   return $data;
}

function plaatprotect_db_config_update($config) {

  $query = 'update config set value="'.$config->value.'", date=SYSDATE() where id='.$config->id;		
  
  return plaatprotect_db_query($query);
}

/*
** ---------------------
** EVENT
** ---------------------
*/

function plaatprotect_db_event($category=0) {
	
   $query  = 'select eid, timestamp, category, action, processed from event where processed=0 ';
   if ($category!=0) {
		$query .= 'and category='.$category.' ';
	}
	$query .= 'order by timestamp limit 0,1';
   $result = plaatprotect_db_query($query);
   $row = plaatprotect_db_fetch_object($result);
	$result->close();

	return $row;
}

function plaatprotect_db_event_insert($category, $action) {
 
   $timestamp = date('Y-m-d H:i:s');
	
   $query  = 'insert into event (timestamp, category, action) ';
	$query .= 'values ("'.$timestamp.'",'.$category.',"'.plaatprotect_db_escape($action).'")';
	
	return plaatprotect_db_query($query);
}

function plaatprotect_db_event_update($event) {
		
	$query  = 'update event set '; 
	$query .= 'processed='.$event->processed.' ';
	$query .= 'where eid='.$event->eid; 
	
	return plaatprotect_db_query($query);
}

/*
** ---------------------
** ZWAVE
** ---------------------
*/

function plaatprotect_db_zwave($zid) {
 	
   $query  = 'select zid, type, vendor, location, home, sleep, away, panic from zwave where zid='.$zid;
   $result = plaatprotect_db_query($query); 
	$row = plaatprotect_db_fetch_object($result);
 
	return $row;
}

function plaatprotect_db_zwave_insert($zid, $vendor, $type, $version, $location, $state) {
 
	global $db;
	
   $query  = 'insert into zwave (zid, vendor, type, version, location) ';
	$query .= 'values ('.$zid.',';
	$query .= '"'.plaatprotect_db_escape($vendor).'",';
	$query .= '"'.plaatprotect_db_escape($type).'",';
	$query .= '"'.plaatprotect_db_escape($version).'",';
	$query .= '"'.plaatprotect_db_escape($location).'")';
	
	return plaatprotect_db_query($query);
}

function plaatprotect_db_zwave_update($zwave) {
 
   $query  = 'update zwave set '; 
	$query .= 'vendor="'.plaatprotect_db_escape($zwave->vendor).'", ';
	$query .= 'type="'.plaatprotect_db_escape($zwave->type).'", ';
	$query .= 'location="'.plaatprotect_db_escape($zwave->location).'", ';
	$query .= 'home='.$zwave->home.', ';
	$query .= 'sleep='.$zwave->sleep.', ';
	$query .= 'away='.$zwave->away.', ';
	$query .= 'panic='.$zwave->panic.' ';
	$query .= 'where zid='.$zwave->zid; 
	
	return plaatprotect_db_query($query);
}

function plaatprotect_db_zwave_delete($zid) {
 
	$query = 'delete from zwave where zid='.$zid;
	 
	return plaatprotect_db_query($query);
}

function plaatprotect_db_zwave_alive($zid) {

	$timestamp = date('Y-m-d H:i:s');

	$query = 'update zwave set last_update="'.$timestamp.'" where zid='.$zid;	
	
   plaatprotect_db_query($query);
}

/*
** ---------------------
** SENSOR
** ---------------------
*/

function plaatprotect_db_sensor_last($zid, $timestamp) {
 	
   $query  = 'select sid, zid, luminance, temperature, humidity, ultraviolet, battery from sensor where zid='.$zid.' and timestamp="'.$timestamp.'"';
   $result = plaatprotect_db_query($query); 
	$row = plaatprotect_db_fetch_object($result);
 
	return $row;
}

function plaatprotect_db_sensor_insert($zid, $timestamp, $luminance, $temperature, $humidity, $ultraviolet, $battery) {
 	
	$query  = 'insert into sensor (zid, timestamp, luminance, temperature, humidity, ultraviolet, battery) ';
	$query .= 'values ('.$zid.',"'.$timestamp.'",'.$luminance.','.$temperature.','.$humidity.','.$ultraviolet.','.$battery.')';
	
	plaatprotect_db_query($query);
}

function plaatprotect_db_sensor_update($sensor) {
	
	$query  = 'update sensor set ';
	$query .= 'zid='.$sensor->zid.',';
	$query .= 'luminance='.$sensor->luminance.',';
	$query .= 'temperature='.$sensor->temperature.',';
	$query .= 'humidity='.$sensor->humidity.',';
	$query .= 'ultraviolet='.$sensor->ultraviolet.',';
	$query .= 'battery='.$sensor->battery.' ';
	$query .= 'where sid='.$sensor->sid;	
	
	plaatprotect_db_query($query);
}

/*
** ---------------------
** HUE
** ---------------------
*/

define('HUE_STATE_OFF',      0);
define('HUE_STATE_ON',       1);
define('HUE_STATE_OFFLINE',  2);

function plaatprotect_db_hue($hid) {
 	
   $query  = 'select hid, vendor, type, version, location, state, home, sleep, away, panic from hue where hid='.$hid;
   $result = plaatprotect_db_query($query); 
	$row = plaatprotect_db_fetch_object($result);
 
	return $row;
}

function plaatprotect_db_hue_update($data) {
 
   $query  = 'update hue set '; 
	$query .= 'vendor="'.plaatprotect_db_escape($data->vendor).'", ';
	$query .= 'type="'.plaatprotect_db_escape($data->type).'", ';
	$query .= 'version="'.plaatprotect_db_escape($data->version).'", ';
	$query .= 'location="'.plaatprotect_db_escape($data->location).'", ';
	$query .= 'state='.$data->state.', ';
	$query .= 'home='.$data->home.', ';
	$query .= 'sleep='.$data->sleep.', ';
	$query .= 'away='.$data->away.', ';
	$query .= 'panic='.$data->panic.' ';
	$query .= 'where hid='.$data->hid; 
	
	return plaatprotect_db_query($query);
}

function plaatprotect_db_hue_insert($hid, $vendor, $type, $version, $location, $state) {
 	
   $query  = 'insert into hue (hid, vendor, type, version, location, state) ';
	$query .= 'values ('.$hid.',';
	$query .= '"'.plaatprotect_db_escape($vendor).'",';
	$query .= '"'.plaatprotect_db_escape($type).'",';
	$query .= '"'.plaatprotect_db_escape($version).'",';
	$query .= '"'.plaatprotect_db_escape($location).'",';
	$query .= $state.')';
	
	return plaatprotect_db_query($query);
}

function plaatprotect_db_hue_delete($hid) {
 
	$query = 'delete from hue where hid='.$hid;
	 
	return plaatprotect_db_query($query);
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>

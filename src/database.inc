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

	global $output;

	$handle = mysql_connect($dbhost, $dbuser, $dbpass);
	if(!$handle) {

		plaatprotect_db_error();
		return false;		
	}
	mysql_select_db($dbname);
	return true;
}

/**
 * Disconnect from database  
 * @return disconnect result
 */
function plaatprotect_db_close() {

	$result = mysql_close();
	return $result;
}

/**
 * Show SQL error 
 * @return HTML formatted SQL error
 */
function plaatprotect_db_error() {

	$error = mysql_error();
	
	if (DEBUG == 1) {
		echo $error.'<br/>';
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
	global $output;
	
	$query_count++;

	if (DEBUG == 1) {
		echo $query.'<br/>';
	}
		
	@$result = mysql_query($query);
		
	if (!$result) {
		plaatprotect_db_error();		
	}
	
	return $result;
}

/**
 * Fetch query result 
 * @return mysql data set if any
 */
function plaatprotect_db_fetch_object($result) {
	
	$data = @mysql_fetch_object($result);	
	return $data;
}

/**
 * Escape SQL value 
 * @return escaped mysql query
 */
function plaatprotect_db_escape($value) {
	
	$value = mysql_real_escape_string($value);	
	return $value;
}

/**
 * Return number of rows
 * @return number of row in dataset
 */
function plaatprotect_db_num_rows($value) {
	
	$value = mysql_num_rows($value);
	return $value;
}

/*
** ---------------------
** SPECIFIC
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
        if(trim($command)){
	    if (DEBUG == 1) {
		echo $command.'<br/>';
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
}

/**
 * Fetch config item from database
 * @param $key key name of setting stored in database
 * @return $value of key
 */
function plaatprotect_db_get_config_item($key, $category=0) {

   $sql = 'select value from config where token="'.$key.'" and category='.$category;
   $result = plaatprotect_db_query($sql);
   $data = plaatprotect_db_fetch_object($result);

   $value = "";
   if ( isset($data->value) ) {
		$value = $data->value;
   }
   return $value;
}


function plaatprotect_db_set_config_item($key, $value, $category=0) {

  $sql  = 'update config set value="'.$value.'", date=SYSDATE() where token="'.$key.'" and category='.$category;		
  plaatprotect_db_query($sql);
}

function plaatprotect_db_get_session($ip, $new=false) {

   $sql = 'select sid, session_id, requests from session where ip="'.$ip.'"';
   $result = plaatprotect_db_query($sql);
   $data = plaatprotect_db_fetch_object($result);

   $session_id = "";
   if ( isset($data->sid) ) {   
	
		$session_id = $data->session_id;
		$requests = $data->requests;
	
		if ($new==true) {		
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
 * @unitTest
 */
function plaatprotect_db_cron_update($cid) {
		
	$query  = 'update cron set '; 
	$query .= 'last_run = "'.date("Y-m-d H:i:s").'" ';
	$query .= 'where cid='.$cid; 
	
	return plaatprotect_db_query($query);
}

/*
** ---------------------
** THE END
** ---------------------
*/

?>

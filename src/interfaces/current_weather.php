<?php

/* 
**  ===========
**  PlaatEnergy
**  ===========
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

define('LOG', 0);

include '/var/www/html/plaatprotect/config.php';
include '/var/www/html/plaatprotect/database.php';
include '/var/www/html/plaatprotect/general.php';

define( 'LOCK_FILE', "/tmp/".basename( $argv[0], ".php" ).".lock" ); 
if( plaatprotect_islocked() ) die( "Already running.\n" ); 

plaatprotect_db_connect($dbhost, $dbuser, $dbpass, $dbname);

$weather_present = plaatprotect_db_config_value('weather_present', CATEGORY_WEATHER);
$weather_city = plaatprotect_db_config_value('weather_city', CATEGORY_WEATHER);
$weather_country = plaatprotect_db_config_value('weather_country', CATEGORY_WEATHER);  
$weather_api_key = plaatprotect_db_config_value('weather_api_key', CATEGORY_WEATHER);

$SLEEP = 5000000;
$MAX_LOOP = 5;

if( $weather_present=="true" ) { 

	$stop = false;
	$count = 0;

	while ($stop==false) {
		$url = "http://api.openweathermap.org/data/2.5/weather?appid=".$weather_api_key."&q=".$weather_city.",".$weather_country."&units=metric";
		$json = file_get_contents($url);	
		if (LOG == 1) {	
			echo($json);
		}
		
		$data = json_decode($json);
		if (LOG == 1) {	
			print_r($data);
		}	
	
		$timestamp = date('Y-m-d H:i:00');

		if (isset($data->main->temp)) {	
			plaatprotect_db_sensor_insert(100, $timestamp, $data->main->temp);
			plaatprotect_db_sensor_insert(101, $timestamp, $data->main->pressure);
			plaatprotect_db_sensor_insert(102, $timestamp, $data->main->humidity);
			plaatprotect_db_sensor_insert(103, $timestamp, $data->wind->speed);
			$stop = true;			
			
		} else {
		
			if (LOG == 1) {	
				echo "sleep and retry\n\r";			
			}			
			usleep($SLEEP);
			
			if (++$count>$MAX_LOOP) {
				echo "max retry, skip it\n\r";	
				$stop = true;		
			}
		}
	}
}

unlink( LOCK_FILE ); 
exit(0); 

?>

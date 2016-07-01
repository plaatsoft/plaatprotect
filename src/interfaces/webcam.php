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
**  All copyrights reserved (c) 2008-2016 PlaatSoft
*/
 
/**
 * @file
 * @brief webcam script
 */
 
// instance=61 [Webcam 1]
// instance=62 [Webcam 2]

include '/var/www/html/plaatprotect/config.inc';
include '/var/www/html/plaatprotect/database.inc';
include '/var/www/html/plaatprotect/general.inc';

define( 'LOCK_FILE', "/tmp/".basename( $argv[0], ".php" ).".lock" ); 
if( plaatprotect_islocked() ) die( "Already running.\n" ); 

plaatprotect_db_connect($dbhost, $dbuser, $dbpass, $dbname);

$detect_level=15;
$detect_areas=15;
$im2 = '';

@$index = $argv[1];
if (!isset($index)) {
	$index=1;
}

function getColor($img, $x, $y) {
    $rgb = imagecolorat($img, $x, $y);
    $r = ($rgb >> 16) & 0xFF;
    $g = ($rgb >> 8) & 0xFF;
    $b = $rgb & 0xFF;
    return array ($x, $y, $r, $g, $b);
}

function plaatprotect_make_picture($name, $width, $height) {

   $path = BASE_DIR.'/webcam/'.date('Y-m-d');		
   plaatprotect_create_path($path);
	
	$source = BASE_DIR.'/webcam/image1.jpg';
   #$source = BASE_DIR.'/webcam/image1.png';

   $now = DateTime::createFromFormat('U.u', microtime(true));
   $now->setTimezone(new DateTimeZone('Europe/Amsterdam'));	
   $destination = $path.'/image1-'.$now->format("His.u").'.jpg';
	
   $im = imagecreatefromjpeg($source);
	#$im = imagecreatefrompng($source);
	
	$lightblue = ImageColorAllocatealpha($im, 41, 69, 249, 80);
	imagefilledrectangle($im, 0, $height-22, $width, $height, $lightblue);	
	
	$textcolor = imagecolorallocate($im, 255, 255, 255);
	imagestring($im, 5, 5, $height-20, $name, $textcolor);
	imagestring($im, 5, ($width-180), $height-20, $now->format("Y-m-d H:i:s"), $textcolor);
		
   imagejpeg($im, $destination);	
}

function plaatprotect_motion($resolution, $name, $webcam_no_motion_area) {

   global $im2;
   global $index;
   global $detect_level;
   global $detect_areas;
	
   $width=320;
   $height=240;  /* remove last 15 pixel lines because of footer */
   $segment=5;
		
   if ($resolution=="640x480") {
     $width=640;
     $height=480;  /* remove last 15 pixel lines because of footer */
     $segment=10;
   }
	
   $offset=$segment/2;

   $input = BASE_DIR.'/webcam/image'.$index.'.jpg';
	#$input = BASE_DIR.'/webcam/image'.$index.'.png';
   $output = BASE_DIR.'/webcam/image'.($index+2).'.jpg';

   $im1 = imagecreatefromjpeg($input);
	#$im1 = imagecreatefrompng($input);
   if(!$im1) {
      return;
   }
	
	$red = ImageColorAllocatealpha($im1, 255, 0, 0, 30);
	$lightblue = ImageColorAllocatealpha($im1, 41, 69, 249, 80);
  
   if(!$im2) {
     $im2 = $im1;
     return;
   }
	
   $detection=0;	
	$x3=0;
	$y3=0;
	$x4=0;
	$y4=0;
	if (strlen($webcam_no_motion_area)>0) {
		list($x3,$y3,$x4,$y4) = explode(",", $webcam_no_motion_area);
		imagerectangle($im1, $x3, $y3, $x4, $y4, $lightblue);
	}

	$width1=$width/$segment;
	$height1=($height-20)/$segment;

   for ($x=0; $x<$width1; $x++) {
     for ($y=0; $y<$height1; $y++) {
			list($x1, $y1, $r1, $g1, $b1) = getColor($im1, ($x*$segment)+$offset, ($y*$segment)+$offset);
			
			if (($x1>$x3) && ($x1<$x4) && ($y1>$y3) && ($y1<$y4)) {
				continue;
			}
			
			list($x2, $y2, $r2, $g2, $b2) = getColor($im2, ($x*$segment)+$offset, ($y*$segment)+$offset);
			if ((abs($r1-$r2)>$detect_level) || (abs($g1-$g2)>$detect_level) || (abs($b1-$b2)>$detect_level)) { 		 
				$detection++;
				imagerectangle( $im1, $x*$segment , $y*$segment , ($x+1)*$segment , ($y+1)*$segment , $red);
			}	
		}
	}
	
	$now = DateTime::createFromFormat('U.u', microtime(true));
   $now->setTimezone(new DateTimeZone('Europe/Amsterdam'));	
	
	imagefilledrectangle($im1, 0, $height-22, $width, $height, $lightblue);	
	$white = imagecolorallocate($im1, 255, 255, 255);
	imagestring($im1, 6, 5, $height-18, $name, $white);
	imagestring($im1, 6, ($width-180), $height-18, $now->format("Y-m-d H:i:s"), $white);

   $im2=$im1;
   imagejpeg($im1, $output);	

   if ($detection>$detect_areas) {
     plaatprotect_make_picture($name, $width, $height);
   }
	
   return $detection;
}

$instance = 61; 
if ($index==2) {
	$instance=62;
}
	
$name = plaatprotect_db_get_config_item('webcam_name', $instance);
$resolution = plaatprotect_db_get_config_item('webcam_resolution', $instance);
$device = plaatprotect_db_get_config_item('webcam_device', $instance);  
$webcam_fps = plaatprotect_db_get_config_item('webcam_fps', $instance);
$webcam_no_motion_area = plaatprotect_db_get_config_item('webcam_no_motion_area', $instance);
	
while (true) {

	global $name;
	global $resolution;
	global $device;
	global $webcam_fps;
	global $webcam_no_motion_area;
	
	$time_start = microtime(true);
	
	global $index;
	
	#$command = 'fswebcam -q --device '.$device.' --timestamp "%Y-%m-%d %H:%M:%S" -r '.$resolution. ' --title '.$name.' -S 1 '.BASE_DIR.'/webcam/image'.$index.'.jpg';  
	$command = 'fswebcam -q --device '.$device.' --no-banner -r '.$resolution. ' -S 1 '.BASE_DIR.'/webcam/image'.$index.'.jpg';   
	#$command = 'ffmpeg -i '.$device.' -s '.$resolution. ' -c png -frames 1 -v 0 -y '.BASE_DIR.'/webcam/image'.$index.'.png'; 
  
	exec($command);
	echo $command."\r\n";
	
	$detection_count = plaatprotect_motion($resolution, $name, $webcam_no_motion_area);

	$time_end = microtime(true);	
	$time = ($time_end - $time_start)*1000000;
	
	$sleep = 5000000;
	if ($time>200000) {
		/* no error, default delay */
		$sleep = round((1000000 / $webcam_fps) - $time);
	}
	
	echo 'Process time='.round(($time))." usec [motion_count=".$detection_count." | now sleep ".$sleep." usec]\r\n";
		
	if ($sleep>0) {
		usleep($sleep);
	}
}

unlink( LOCK_FILE ); 
exit(0); 

?>

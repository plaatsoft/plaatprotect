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
 * @brief contain zwave interface 
 */
 
include "/var/www/html/plaatprotect/general.inc";
include "/var/www/html/plaatprotect/database.inc";
include "/var/www/html/plaatprotect/config.inc";

define('EVENT_IDLE',        		10);
define('EVENT_ALARM_ON',   		11);
define('EVENT_ALARM_OFF', 	  		12);

define('STATE_IDLE',        		20);
define('STATE_ACTIVE',      		21);

$state = STATE_IDLE;
$event = EVENT_IDLE;
$event_nodeid = 0;
$event_timestamp = 0;

define( 'LOCK_FILE', "/tmp/".basename( $argv[0], ".php" ).".lock" ); 
if( plaatprotect_islocked() ) die( "Already running.\n" ); 

// Open Aeotec Zstick (Gen. 5) device 
exec('stty -F /dev/ttyACM0 9600 raw');
$fp=fopen("/dev/ttyACM0","c+");


/**
 ********************************
 * Counter measures
 ********************************
 */

function plaatprotect_hue_alarm_group($event) {

	$sql = 'select value from config where token="alarm_scenario"';
	$result = plaatprotect_db_query($sql);
	$row = plaatprotect_db_fetch_object($result);
	
	switch ($row->value) {
	
		case HOME: 
			$sql = 'select hid from hue where home=1';
			break;
			
		case SLEEP: 
			$sql = 'select hid from hue where sleep=1';
			break;		
			
		case AWAY: 
			$sql = 'select hid from hue where away=1';
			break;
	}

	$result = plaatprotect_db_query($sql);
	while ($row = plaatprotect_db_fetch_object($result)) {	
		if ($event==EVENT_ALARM_ON) {
			plaatprotect_control_hue($row->hid, "true");
		} else {
			plaatprotect_control_hue($row->hid, "false");
		}
	}
}
	
function plaatprotect_zwave_sirene_group($event) {
	
	$sql = 'select value from config where token="alarm_scenario"';
	$result = plaatprotect_db_query($sql);
	$row = plaatprotect_db_fetch_object($result);
	
	switch ($row->value) {
	
		case HOME: 
			$sql = 'select zid, nodeid from zwave where home=1';
			break;
			
		case SLEEP: 
			$sql = 'select zid, nodeid from zwave where sleep=1';
			break;		
			
		case AWAY: 
			$sql = 'select zid, nodeid from zwave where away=1';
			break;
	}
	
   $result = plaatprotect_db_query($sql);
   while ($row = plaatprotect_db_fetch_object($result)) {
	
		if ($event==EVENT_ALARM_ON) {
			SendDataActiveHorn($row->nodeid, 1, $row->nodeid);
			Receive();
			Receive();
		} else  {
			SendDataActiveHorn($row->nodeid, 0, $row->nodeid);
			Receive();
			Receive();
		}		
	}	
}
	
function plaatprotect_zwave_notification_group($event, $nodeid) {

	$sql = 'select value from config where token="alarm_scenario"';
	$result = plaatprotect_db_query($sql);
	$row = plaatprotect_db_fetch_object($result);
	
	switch ($row->value) {
	
		case HOME: 
			$sql = 'select nid from notification where home=1 and type=1';
			break;
			
		case SLEEP: 
			$sql = 'select nid from notification where sleep=1 and type=1';
			break;		
			
		case AWAY: 
			$sql = 'select nid from notification where away=1 and type=1';
			break;
	}
	
	$result = plaatprotect_db_query($sql);
   
	while ($row = plaatprotect_db_fetch_object($result)) {
	
		$sql2 = 'select nodeid, location from zwave where nodeid='.$nodeid;	
		$result2 = plaatprotect_db_query($sql2);
		$row2 = plaatprotect_db_fetch_object($result2);
	
		// Notication to mobile
		$subject =  "Alarm";
		$body  = "Location=".$row2->location." ";
		$body .= "Zone=".$row2->nodeid." ";
		$body .= "Event=";
	
		if ($event==EVENT_ALARM_ON) {
			$body .= 'on';
		} else {
			$body .= 'off';
		}
	
		LogText('Notification = '.$subject.' '.$body .' sent!');
	
		plaatprotect_mobile_notification($subject, $body );
	}
}
	
function plaatprotect_zwave_state_machine() {
	
	global $event;
	global $event_nodeid;
	global $state;
	
	switch ($event) {
	
		case EVENT_IDLE: 
				break;
				
		case EVENT_ALARM_ON: 
				if ($state==STATE_IDLE) {
		
					LogText("======================");					
					plaatprotect_hue_alarm_group($event);
					plaatprotect_zwave_sirene_group($event);
					plaatprotect_zwave_notification_group($event, $event_nodeid);
					LogText("======================");
					
					$state=STATE_ACTIVE;
					$event=EVENT_IDLE;
				}
				break;
				
		case EVENT_ALARM_OFF: 
		
				if ($state==STATE_ACTIVE) {
					LogText("======================");				
					plaatprotect_hue_alarm_group($event);
					plaatprotect_zwave_sirene_group($event);
					plaatprotect_zwave_notification_group($event, $event_nodeid);
					LogText("======================");
					$state=STATE_IDLE;
					$event=EVENT_IDLE;
				}
				break;
	}
	
	switch ($state) {
	
		case STATE_IDLE: 
		      LogText("StateMachine = Idle");
				break;
				
		case STATE_ACTIVE:
				LogText("StateMachine = Active");
				break;
	}
}
	


/**
 ********************************
 * Database
 ********************************
 */
 
plaatprotect_db_connect($dbhost, $dbuser, $dbpass, $dbname);

function plaatprotect_event_insert($nodeId, $event, $value) {
 
   $timestamp = date('Y-m-d H:i:s');
	
   $sql  = 'INSERT INTO event (timestamp, nodeid, event, value) ';
	$sql .= 'VALUES ("'.$timestamp.'",'.hexdec($nodeId).','.$event.','.$value.')';
	
	plaatprotect_db_query($sql);
}

function plaatprotect_sensor_insert($nodeId, $type, $value) {
 
   $timestamp = date('Y-m-d H:i:s');
	
	$temperature = 0;
	$luminance = 0;
	$humidity = 0;
	$ultraviolet = 0;
	$battery = 0;
	
	switch ($type) {
		case 0x00: $battery = $value;
					  break;
					  
		case 0x01: $temperature = $value;
					  break;
		
		case 0x03: $luminance = $value;
					  break;
			  
		case 0x05: $humidity = $value;
					  break;
					 
		case 0x1b: $ultraviolet = $value;
					  break;
	}
	
   $sql  = 'INSERT INTO sensor (nodeid, timestamp, temperature, luminance, humidity, ultraviolet, battery ) ';
	$sql .= 'VALUES ('.hexdec($nodeId).',"'.$timestamp.'","'.$temperature.'","'.$luminance.'","'.$humidity.'","'.$ultraviolet.'",'.$battery.')';
	
	plaatprotect_db_query($sql);
}

function plaatprotect_node_alive($nodeId) {

	$sql  = 'update zwave set last_update=SYSDATE() where nodeid='.hexdec($nodeId);	
   $result = plaatprotect_db_query($sql);
   $row = plaatprotect_db_fetch_object($result);
}

function plaatprotect_control_hue($hue_bulb_nr, $value) {
	
 	$hue_ip = plaatprotect_db_get_config_item('hue_ip_address',HUE);
 	$hue_key = plaatprotect_db_get_config_item('hue_key',HUE);
	
   $hue_url = "http://".$hue_ip."/api/".$hue_key."/lights/".$hue_bulb_nr."/state";

   $tmp = "Hue command: ";

    $tmp .= file_get_contents($hue_url, false, stream_context_create(["http" => [
      "method" => "PUT", "header" => "Content-type: application/json",
      "content" => "{\"on\":". $value."}"
    ]]));

	LogText($tmp);
}
  
  
/**
 ********************************
 * General
 ********************************
 */
 
/**
 * Zwave checksum calculation
 */
function GenerateChecksum($data, $send=true) {
    $offset = 1;
    $len = strlen($data);
    if ($len==0) {
      return 0;
    }
    if ($send==false) {
      $len--;
    }
    $offset = 1;
    $ret = $data[$offset];
    
    for ($i = $offset+1; $i<$len; $i++) {
        // Xor bytes
        $ret = $ret ^ $data[$i];
    }
    // Not result
    $ret = ~$ret;
    return $ret;
}

/**
 * Convert byte stream to nice formatted hex string
 */
function GetHexString($value) {
  
   $tmp="";
   for ($i=0; $i<strlen($value); $i++) {
      if (strlen($tmp)>0) {
         $tmp.=' ';
      }
      $tmp.='0x'.bin2hex($value[$i]);
   }  
   return $tmp;
}

/**
 * Log send byte(s)
 */
function LogTxCommand($data) {

  $t = microtime(true);
  $micro = sprintf("%06d",($t - floor($t)) * 1000000);
  $d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );

  print $d->format("Y-m-d H:i:s.u");
    
  echo ' Tx: '.GetHexString($data)."\r\n";
}

function LogText($text) {

  $t = microtime(true);
  $micro = sprintf("%06d",($t - floor($t)) * 1000000);
  $d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );

  print $d->format("Y-m-d H:i:s.u");
  echo " ".$text."\r\n";
}

/**
 * Log received byte(s)
 */
function LogRxCommand($data, $crc) {

   $t = microtime(true);
   $micro = sprintf("%06d",($t - floor($t)) * 1000000);
   $d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );

   print $d->format("Y-m-d H:i:s.u");
    
   echo ' Rx: '.GetHexString($data);
   if ($crc==true) {
	   echo " [".bin2hex(GenerateChecksum($data, false))."]";
   }
  
   echo "\r\n";
  
	if ($crc==true) {
		if ($data[strlen($data)-1]!=GenerateChecksum($data, false)) {
			return false;
		}
	}
	return true;
}

function int2hex($value) {

   return sprintf("%02x",$value);
}

/**
 ********************************
 * Sent ZWave Packet
 ********************************
 */

/* 
 ** Send Ack 
 */
function SendAck() {

	global $fp;
	
	$command = chr(0x06);
	fwrite($fp, $command, strlen($command));
	LogTxCommand($command);
}

/* 
 ** Send GetVersion 
 */
function SendGetVersion() {

  global $fp;
  /*
   * Byte 0 : Start of Frame (0x01)
   * Byte 1 : Length of frame - number of bytes to follow
   * Byte 2 : Request (0x00) 
   * Byte 3 : Message Class (0x15) SendGetVersion
   * Byte 4 : Last byte is checksum
   */
 
   $tmp = "SendGetVersion"; 
	LogText($tmp);
	 
   $command = hex2bin("01030015");
   $command .= GenerateChecksum($command);
   LogTxCommand($command);
   fwrite($fp, $command, strlen($command));
}

/* 
 ** Send GetVersion 
 */
function SendRequestNodeNeighborUpdate($node) {

  global $fp;
  /*
   * Byte 0 : Start of Frame (0x01)
   * Byte 1 : Length of frame - number of bytes to follow
   * Byte 2 : Request (0x00) 
   * Byte 3 : Message Class (0x48) RequestNodeNeighborUpdate
	* Byte 4 : NodeId
   * Byte 5 : Last byte is checksum
   */
 
   $tmp = "SendRequestNodeNeighborUpdate"; 
	LogText($tmp);
	 
   $command = hex2bin("01040048".int2hex($node));
   $command .= GenerateChecksum($command);
   LogTxCommand($command);
   fwrite($fp, $command, strlen($command));
}


function SendGetMemoryId() {

  global $fp;
  /*
   * Byte 0 : Start of Frame (0x01)
   * Byte 1 : Length of frame - number of bytes to follow
   * Byte 2 : Request (0x00) 
   * Byte 3 : Message Class (0x20) SendGetMemoryId
   * Byte 4 : Last byte is checksum
   */
 
   $tmp = "SendGetMemoryId"; 
	LogText($tmp);
	 
   $command = hex2bin("01030020");
   $command .= GenerateChecksum($command);
   LogTxCommand($command);
   fwrite($fp, $command, strlen($command));
}


function SendGetRouteInfo($node) {

  global $fp;
  /*
   * Byte 0 : Start of Frame (0x01)
   * Byte 1 : Length of frame - number of bytes to follow
   * Byte 2 : Request (0x00)
   * Byte 3 : Message Class (0x80) SendGetRouteInfo
   * Byte 4 : NodeId 
   * Byte 5 : Do not remove bad Node 0x00
   * Byte 6 : Do not remove non-repater 0x00
   * Byte 7 : Function Id 0x03
   * Byte 8 : Last byte is checksum
   */
   $tmp = "SendGetRouteInfo NodeId=".int2hex($node);
	LogText($tmp);
	
   $command = hex2bin("01070080".int2hex($node)."000003");
   $command .= GenerateChecksum($command);
   fwrite($fp, $command, strlen($command));
   LogTxCommand($command);
}

function SendGetIdentifyNode($node) {

  global $fp;
  /*
   * Byte 0 : Start of Frame (0x01)
   * Byte 1 : Length of frame - number of bytes to follow
   * Byte 2 : Request (0x00) 
   * Byte 3 : Message Class (0x41) IdentifyNode
   * Byte 4 : NodeId
   * Byte 5 : Last byte is checksum
   */
  
   $tmp= "GetIndentifyNode NodeId=[".int2hex($node)."]";
   LogText($tmp);
	
  $command = hex2bin("01040041".int2hex($node));
  $command .= GenerateChecksum($command);
  fwrite($fp, $command, strlen($command));
  LogTxCommand($command);
}

function SendGetCommandClassSupport($node ,$callbackId) {

  global $fp;
  /*
   * Byte 0 : Start of Frame (0x01)
   * Byte 1 : Length of frame - number of bytes to follow
   * Byte 2 : Request (0x00) 
   * Byte 3 : Message Class (0x41) IdentifyNode
   * Byte 4 : NodeId
   * Byte 5 : Last byte is checksum
   */
  
   $tmp= "SentGetCommandClassSupport NodeId=[".int2hex($node)."] CallbackId=[".int2hex($callbackId)."]";
   LogText($tmp);
	
   $command = hex2bin("01090013".int2hex($node)."02000025".int2hex($callbackId));
   $command .= GenerateChecksum($command);
   fwrite($fp, $command, strlen($command));
   LogTxCommand($command);
}

function SendGetProtocolStatus() {

  global $fp;
  /*
   * Byte 0 : Start of Frame (0x01)
   * Byte 1 : Length of frame - number of bytes to follow
   * Byte 2 : Request (0x00) 
   * Byte 3 : Message Class (0xbf) SendGetProtocolStatus
   * Byte 4 : Last byte is checksum
   */
 
   $tmp= 'SendGetProtocolStatus';
   LogText($tmp);
	
   $command = hex2bin("010300bf");
   $command .= GenerateChecksum($command);
   fwrite($fp, $command, strlen($command));
   LogTxCommand($command);
}

function SendGetControllerCapabilities() {

  global $fp;
	
  /*
   * Byte 0 : Start of Frame (0x01)
   * Byte 1 : Length of frame - number of bytes to follow
   * Byte 2 : Request (0x00)
   * Byte 3 : Message Class (0x05)
   * Byte 4 : Last byte is checksum
   */
 
   $tmp= 'SendGetControllerCapabilities';
   LogText($tmp);
  
   $command = hex2bin("01030005");
   $command .= GenerateChecksum($command);
   LogTxCommand($command);
   fwrite($fp, $command, strlen($command));
}

function SendGetInitData() {
	
  global $fp;

  /*
   * Byte 0 : Start of Frame (0x01)
   * Byte 1 : Length of frame - number of bytes to follow
   * Byte 2 : Request (0x00)
   * Byte 3 : SendGetInitData (0x02)
   * Byte 4 : Last byte is checksum
   */

  $tmp= 'SendGetInitData';
  LogText($tmp);

  $command = hex2bin("01030002");
  $command .= GenerateChecksum($command);
  fwrite($fp, $command, strlen($command));
  LogTxCommand($command);
}

function SendDataInitHorn($node, $sound, $volume, $callbackId) {
	
   global $fp;
	
  /*
   * Byte 0 : Start of Frame (0x01)
   * Byte 1 : Length of frame - number of bytes to follow
   * Byte 2 : Request (0x00) 
   * Byte 3 : SendData (0x13)
   * Byte 4 : NodeId
   * Byte 5 : ConfigSet (0x03)
   * Byte 6 : Command Class CONFIGURATION 0x70
   * Byte 7 : Parameter (0x25)
   * Byte 8 : Value 1 (Sound) 0-5
   * Byte 9 : Value 2 (Volume) 0-3
   * Byte 10: CallBackId (0xff) 
   * Byte 11: Last byte is checksum
   */
		
   $tmp = "SetSendDataHorn " ; 
	
	switch ($sound) {
		case 0:  $tmp .= "CurrentSound ";
		         break;
		case 1:  $tmp .= "Sound1 ";
		         break;
		case 2:  $tmp .= "Sound2 ";
		         break;					
		case 3:  $tmp .= "Sound3 ";
		         break;
		case 4:  $tmp .= "Sound4 ";
		         break;
		case 5:  $tmp .= "Sound5 ";
		         break;		
      default: $tmp .= "NotSupportSound, abort ";
	       return;
               break;	
	}
	
	switch ($volume) {
		case 0:  $tmp .= "CurrentVolume ";
		         break;
		case 1:  $tmp .= "88dB ";
		         break;
		case 2:  $tmp .= "100dB ";
		         break;					
		case 3:  $tmp .= "105dB ";
		         break;
      default: $tmp .= "NotSupportVolume, abort";
					return;
               break;	
   }

   LogText($tmp);
	
   $command = hex2bin("010a0013".int2hex($node)."0570".int2hex($sound).int2hex($volume)."25".$callbackId);
   $command .= GenerateChecksum($command);
   LogTxCommand($command);
   fwrite($fp, $command, strlen($command));
}


function SendDataActiveHorn($node,$value,$callbackId) {

  global $fp;
  
  /*
   * Byte 0 : Start of Frame (0x01)
   * Byte 1 : Length of frame - number of bytes to follow
   * Byte 2 : Request (0x00) 
   * Byte 3 : SendData (0x13)
   * Byte 4 : NodeId
   * Byte 5 : Config Set (0x03)
   * Byte 6 : Command Class BASIC 0x20 
   * Byte 7 : Parameter (0x01)
   * Byte 8 : Value (On=0xff Off=0x00) 
   * Byte 9 : CallBackId 
   * Byte 10: Last byte is checksum
   */
   
   $tmp = "SendDataActiveHorn NodeId=".int2hex($node)." value=".int2hex($value)." callbackId=".int2hex($callbackId);
	LogText($tmp);

   $command = hex2bin("01090013".int2hex($node)."032001".int2hex($value).int2hex($callbackId));
   $command .= GenerateChecksum($command);
   fwrite($fp, $command, strlen($command));
   LogTxCommand($command);
}


function GetHornState($node,$value,$callbackId) {

  global $fp;
  
  /*
   * Byte 0 : Start of Frame (0x01)
   * Byte 1 : Length of frame - number of bytes to follow
   * Byte 2 : Request (0x00) 
   * Byte 3 : SendData (0x13)
   * Byte 4 : NodeId
   * Byte 5 : Config Get (0x02)
   * Byte 6 : Command Class BASIC 0x20 
   * Byte 7 : Parameter (0x01)
   * Byte 8 : Value
   * Byte 9 : CallBackId 
   * Byte 10: Last byte is checksum
   */
   $tmp = "GetHornState NodeId=".int2hex($node)." value=".int2hex($value)." callbackId=".int2hex($callbackId);
	LogText($tmp);

   $command = hex2bin("01090013".int2hex($node)."022001".int2hex($value).int2hex($callbackId));
   $command .= GenerateChecksum($command);
   fwrite($fp, $command, strlen($command));
   LogTxCommand($command);
}

/**
 ********************************
 * Received ZWave packet
 ********************************
 */
 
function decodeAlarm($data) {

	global $event;
	global $event_nodeid;
	
	$tmp = "";
	$nodeid = bin2hex(substr($data,5,1));
	$command= ord(substr($data,8,1));
	
	switch ($command) {
		case 0x04: $tmp .= 'Get ';
					  break;
					  
	   case 0x05: $tmp .= 'Report ';
					  break;
      
		case 0x07: $tmp .= "SupportGet ";
				     break;
      
		case 0x08: $tmp .= "SupportReport ";
				     break;
	}
                 
	$type = ord(substr($data,9,1));
	switch ($type) {
		case 0x00: $tmp .= 'General ';
					  break;
					  
		case 0x01: $tmp .= 'Smoke ';
					  break;
					  
      case 0x02: $tmp .= 'Carbon Monoxide ';
					  break;
					  
      case 0x03: $tmp .= 'Carbon Dioxide ';
					  break;
					  
      case 0x04: $tmp .= 'Heat ';
					  break;
					  
      case 0x05: $tmp .= 'Flood ';
					  break;
					  
      case 0x06: $tmp .= 'Access control ';
					  break;
					  
      case 0x07: $tmp .= 'Burglar ';
					  break;
					  
      case 0x08: $tmp .= 'Power Management ';
					  break;
					  
      case 0x09: $tmp .= 'System ';
					  break;
					  
      case 0x0a: $tmp .= 'Emergency ';
					  break;
					  
      case 0x0b: $tmp .= 'Clock ';
					  break;
					  
      case 0x0c: $tmp .= 'Appliance ';
					  break;
					  
      case 0x0d: $tmp .= 'Health ';
					  break;
   }
 	
	$action = ord(substr($data,14,1));
	switch ($action) {
		case 0x00: $tmp .= 'AlarmOff';
					  $event = EVENT_ALARM_OFF;
					  plaatprotect_event_insert($nodeid, $action, 0x00);	
					  $event_nodeid = $nodeid;
					  $event_timestamp = time();
					  break;
					  
		case 0x03: $tmp .= 'AlarmVibrationDetected';
					  $event = EVENT_ALARM_ON;
					  plaatprotect_event_insert($nodeid, $action, 0xff);	
					  $event_nodeid = $nodeid;
					  $event_timestamp = time();
					  break;
					  
		case 0x08: $tmp .= 'AlarmMotionDetected';
					  $event = EVENT_ALARM_ON;
					  plaatprotect_event_insert($nodeid, $action, 0xff);	
					  $event_nodeid = $nodeid;
					  $event_timestamp = time();
					  break;
	}
		
	return $tmp;
}

function decodeSensor($data) {

	$tmp ="";
	$nodeId = bin2hex(substr($data,5,1));
	$command = ord(substr($data,8,1));
	$type = ord(substr($data,9,1));
	
	switch ($command) {
		case 0x04: $tmp .= 'Get ';
					  break;
					  
	   case 0x05: $tmp .= 'Report ';
					  break;
	}
	
	$value = 0;
	switch ($type) {
		case 0x01: $tmp .= 'Temperature ';
					  $value = (((ord(substr($data,11,1)))*100)+ord(substr($data,12,1)))/10;
					  $tmp .= 'Value='.$value;
					  plaatprotect_sensor_insert($nodeId, $type, $value);
					  break;
		
		case 0x03: $tmp .= 'Luminance ';
					  $value = (((ord(substr($data,11,1)))*100)+ord(substr($data,12,1)))/10;
					  $tmp .= 'Value='.$value;
					  plaatprotect_sensor_insert($nodeId, $type, $value);
					  break;
			  
		case 0x05: $tmp .= 'Humidity ';
					  $value = ord(substr($data,11,1));
					  $tmp .= 'Value='.$value;
					  plaatprotect_sensor_insert($nodeId, $type, $value);
					  break;
					 
		case 0x1b: $tmp .= 'Ultraviolet ';
					  $value = ord(substr($data,11,1));
					  $tmp .= 'Value='.$value;
					  plaatprotect_sensor_insert($nodeId, $type, $value);
					  break;
	}
	
	return $tmp;
}

function decodeApplicationCommandHandler($data) {

  $nodeId = bin2hex(substr($data,5,1));
  plaatprotect_node_alive($nodeId);
  
  $len = substr($data,6,1);
  $commandClass = ord(substr($data,7,1));
 
  $tmp = "ApplicationCommandHandler NodeId=[".$nodeId."] ";
  
  switch( $commandClass ) {
   
    case 0x20: $tmp .= 'Basic ';
 	       $command= ord(substr($data,8,1));
	       switch ($command) {

                   case 0x01: $tmp .= 'Set ';
                              $value= ord(substr($data,9,1));
                              $tmp .= 'value='.$value;                              
                              break;
										
                   case 0x02: $tmp .= 'Get ';
                              break;
										
                   case 0x03: $tmp .= 'Report ';
                              $value= ord(substr($data,9,1));
                              $tmp .= 'value='.$value;
                              break;
               }
               break;

    case 0x31: $tmp .= 'Sensor Multilevel ';
					$tmp .= decodeSensor($data);
               break;

    case 0x70: $tmp .= 'Configuration ';
               break;

    case 0x71: $tmp .= 'Alarm ';
				   $tmp .= decodeAlarm($data);
					break;

    case 0x80: $tmp .= 'Battery ';
					$command= ord(substr($data,8,1));
					switch ($command) {
					
                   case 0x02: $tmp .= 'Get ';
                              break;
										
                   case 0x03: $tmp .= 'Report ';
										$value= ord(substr($data,9,1));
										$tmp .= 'BatteryValue='.$value.'%';
										plaatprotect_sensor_insert($nodeId, 0x00, $value);
                              break;
               }
               break;
					
	case 0x84:  $tmp .=  'Received Wakeup Notification ';
			      break;

    default:   $tmp .= 'Unknown';
               break;
  }
  LogText($tmp);
}

function decodeRouteInfo($data) {

 $count = 0;

 $tmp = "RouteId Neighbors ";
 
 for ($i=4; $i<33; $i++ ) {
   $raw_node = ord(substr($data,$i,1));
  
   for ($j=0; $j<8; $j++) {
      if (($raw_node & (0x01 << $j)) != 0x00)
         $tmp .= $j+1+(8*$count).' ';
      }
      $count++;
   }

  LogText($tmp);
}

function decodeRequestNodeNeighborUpdate($data) {

  $nodeId = bin2hex(substr($data,5,1));
  
  $tmp = "RequestNodeNeighborUpdate NodeId=[".$nodeId."] ";
  
  return $tmp;
}

function decodeIdentifyNode($data) {

  $basicClass = ord(substr($data,7,1));
  $deviceType = ord(substr($data,8,1));
  $specifyDeviceType = ord(substr($data,9,1));
  
  $tmp = "IndentifyNode ";

  switch ($basicClass) {
 
    case 0x01: $tmp .= "Controller ";
	       break;
    case 0x02: $tmp .= "StaticController ";
	       break;
    case 0x03: $tmp .= "Slave ";
	       break;
    case 0x04: $tmp .= "Router ";
	       break;
    default:   $tmp .= "Unknown ";
	       break;
  }

  switch ($deviceType) {
    case 0x01: $tmp .= "Controller ";
	       break;

    case 0x02: $tmp .= "StaticController ";
  	       switch ($specifyDeviceType) {
    		  case 0x01: $tmp .= "PCController ";
                  break;
               }
	       break;

    case 0x08: $tmp .= "Thermostat ";
	       break;

    case 0x09: $tmp .= "Shutter ";
	       break;

    case 0x10: $tmp .= "Switch ";
  	       switch ($specifyDeviceType) {
    		  case 0x01: $tmp .= "PowerSwitch ";
                  break;
                  case 0x05: $tmp .= "Siren ";
	          break;
               }
	       break;

    case 0x11: $tmp .= "Dimmer ";
	       break;

    case 0x12: $tmp .= "Transmitter ";
	       break;

    case 0x20: $tmp .= "BinarySensor ";
  	       switch ($specifyDeviceType) {
                  case 0x01: $tmp .= "RoutingBinarySensor ";
                  break;
               }
	       break;

    default:   $tmp.= "Unknown ";
  	       switch ($specifyDeviceType) {
                  case 0x01: $tmp .= "RoutingBinarySensor ";
                  break;
               }
               break;
  }
  LogText($tmp);
}

function decodeSendGetVersion($data) {

  $zWaveLibraryType = $data[16];
  $zWaveVersion = substr($data,4,15);
 
  LogText("SendGetVersion WaveVersion=[".$zWaveVersion."] LibraryType=[0x".bin2hex($zWaveLibraryType)."]");
}

function decodeMemoryId($data) {

  $homeId = GetHexString(substr($data,4,4));
  $nodeId = GetHexString(substr($data,8,1));
 
  LogText("SendGetMemoryId HomeId=[".$homeId."] NodeId=[".$nodeId."]");
  
  plaatprotect_node_alive($nodeId);
}

function decodeSentData($data) {

  $len = strlen($data);
  $callbackId = "";

  $tmp = "SentData ";

  if ($len>7) {
     $callbackId = getHexString(substr($data,4,1));
     $tmp .= "CallbackId=[".$callbackId."] ";

  } else {
    $response = ord(substr($data,4,1));
    switch ($response) {
 
    case 0x00: $tmp .= "Transmission complete and Ack received.";
	       break;
    case 0x01: $tmp .= "Transmission complete and no Ack received.";
	       break;
    case 0x02: $tmp .= "Transmission failed.";
	       break;
    case 0x03: $tmp .= "Transmission failed, network busy.";
	       break;
    case 0x04: $tmp .= "Transmission complete, no return route.";
	       break;
    default:   $tmp .= "Unknown value [".getHexString($response)."]";
	       break;
    }
  }
  LogText($tmp);
}

function DecodeMessage($data) {

  /*
   * Byte 0 : SOF (Start of Frame) 0x01
   * Byte 1 : Length of frame - number of bytes to follow
   * Byte 2 : Response (0x01)
   * Byte 3 : Command
   */
	
   switch (ord($data[3])) {

		case 0x04: 	decodeApplicationCommandHandler($data);
						break;
						
		case 0x13:	decodeSentData($data);
						break;
						
		case 0x15:	decodeSendGetVersion($data);
						break;
						
		case 0x20:	decodeMemoryId($data);
						break;
						
		case 0x41:	decodeIdentifyNode($data);
						break;
						
		case 0x48:	decodeRequestNodeNeighborUpdate($data);
						break;
						
		case 0x80:	decodeRouteInfo($data);
						break;
						
      default:		LogText("Unknown message");
						break;
   }
}

function Receive() {

  global $fp;
  $start = 0;
  $len = 0;
  $count = 0;
  $data = "";
 
  stream_set_blocking( $fp , false );

  $timer=0;
  while (true) {
    $c=fgetc($fp);
    if($c == false){
      $timer++;
      usleep(10000);
      if ($timer>500) {
        break;
      } else {
        continue;
      }
    }  

    $timer=0;
    $data .= $c;
    $count++;
	 
	 if (($start==0) && ($c==chr(0x06))) {
	 
		 LogRxCommand($data, false);
       $start = 0;
       $data="";
		 
	 } else if (($c==chr(0x01)) && ($start==0)) {
       $start = 1;
		 
    } else if ($start == 1) {
      $len = ord($c);
      $count = 0;
      $start = 2;
		
    } else if (($start==2) && ($len==$count)) {
	 	  
      if (LogRxCommand($data, true)) {
			SendAck();	
			DecodeMessage($data);		     
		}
		echo "\r\n";
      		
		$start = 0;
      $count = 0;
      $len = 0;
      $data="";
		
      break;
   }
  }
}

/**
 ********************************
 * State Machine
 ********************************
 */

plaatprotect_mobile_notification("startup", "Zwave interface is started!" );
 
/* Init ZWave layer */
SendGetVersion();
Receive();

SendGetMemoryId();
Receive();

/* Get for all zWave node information */

$sql  = 'select nodeid from zwave';	
$result = plaatprotect_db_query($sql);
	
while ($row = plaatprotect_db_fetch_object($result)) {

  SendGetIdentifyNode($row->nodeid);
  Receive();
  
  SendGetRouteInfo($row->nodeid);
  Receive();
  
  #SendRequestNodeNeighborUpdate($row->nodeid);
  #Receive();
  
  LogText("-----------------");
}

// Switch off Hue Bulbs
LogText("Switch off Hue lights which are part of the alarm group.");
$sql = 'select hid from hue';
$result = plaatprotect_db_query($sql);
while ($row = plaatprotect_db_fetch_object($result)) {	
	plaatprotect_control_hue($row->hid, "false");
}
echo "\r\n";
	
// Switch off Sirene
LogText("Switch off Sirenes which are part of the alarm group.");
$sql  = 'select nodeid from zwave where type=2';	
$result = plaatprotect_db_query($sql);
while ($row = plaatprotect_db_fetch_object($result)) {	
	SendDataActiveHorn($row->nodeid,0,$row->nodeid);
	Receive();
	Receive();
}	
	
/* Read Zwave incoming events endless */

while (true) {
   Receive();
	
	// Process state
	plaatprotect_zwave_state_machine(); 
}

#SendDataActiveHorn(3, 1, "fe");
#Receive();

/* Init ZWave Horn (NodeId=2) (Sound=2) (Volume=1) (CallBackId="ff")*/
#SendDataInitHorn(2, 2, 1, "ff");
#Receive();
#Receive();

/* Enable ZWave Horn (NodeId=2) (On) (CallBackId="fe") */
#SendDataActiveHorn(2, 1, "fe");
#Receive();
#Receive();

/* Disable ZWave Horn (NodeId=2) (Off) (CallbackId="fd") */
#SendDataActiveHorn(2, 0, "fd" );
#Receive();
#Receive();

#SendGetControllerCapabilities();
#Receive();

#SendGetInitData();
#Receive();

#SendGetProtocolStatus();
#Receive();

unlink( LOCK_FILE ); 
exit(0); 

?>

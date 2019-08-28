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
 * @brief contain zwave interface 
 */
 
include "../general.php";
include "../database.php";
include "../config.php";

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
exec('stty -F /dev/ttyACM0 cs8 9600 ignbrk -brkint -imaxbel -opost -onlcr -isig -icanon -iexten -echo -echoe -echok -echoctl -echoke noflsh -ixon -crtscts');
//exec('stty -F /dev/ttyACM0 9600 raw');
$fp=fopen("/dev/ttyACM0","c+");
	
/**
 ********************************
 * Database
 ********************************
 */
 
plaatprotect_db_connect($dbhost, $dbuser, $dbpass, $dbname);

/**
 ********************************
 * Device support library
 ********************************
 */
 
 function decodeManufacture($nodeId, $manufactureId, $deviceType, $deviceId) {
 
	$vendor = "";
	$device = "";
	
	if ($manufactureId=="0x00 0x86") {
		$vendor = "Aeon";
	}
	
	if ($manufactureId=="0x00 0x50") {
		$vendor = "Aeotec";
	}

	if ($deviceId=="0x00 0x5a") {
		$device  = "Controller";
	}
	
	if ($deviceId=="0x00 0x64") {
		$device = "Sensor";
	}
		
	if ($deviceId=="0x00 0x50") {
		$device = "Sirene";
	}
	
	plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"vendor":"'.$vendor.'", "device":"'.$device.'"}');
	
	return ' '.$vendor.' '.$device;
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


function SendSerialApiGetCapabilities() {

  global $fp;
  /*
   * Byte 0 : Start of Frame (0x01)
   * Byte 1 : Length of frame - number of bytes to follow
   * Byte 2 : Request (0x00) 
   * Byte 3 : Message Class (0x07) SerialApiGetCapabilities
   * Byte 4 : Last byte is checksum
   */
 
   $tmp = "SendSerialApiGetCapabilities"; 
	plaatprotect_log($tmp);
	 
   $command = hex2bin("01030007");
   $command .= GenerateChecksum($command);
   LogTxCommand($command);
   fwrite($fp, $command, strlen($command));
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
	plaatprotect_log($tmp);
	 
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
	plaatprotect_log($tmp);
	 
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
	plaatprotect_log($tmp);
	 
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
	plaatprotect_log($tmp);
	
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
   plaatprotect_log($tmp);
	
  $command = hex2bin("01040041".int2hex($node));
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
   plaatprotect_log($tmp);
	
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
   plaatprotect_log($tmp);
  
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
  plaatprotect_log($tmp);

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

   plaatprotect_log($tmp);
	
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
	plaatprotect_log($tmp);

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
	plaatprotect_log($tmp);

   $command = hex2bin("01090013".int2hex($node)."022001".int2hex($value).int2hex($callbackId));
   $command .= GenerateChecksum($command);
   fwrite($fp, $command, strlen($command));
   LogTxCommand($command);
}


function GetManufacturer($node, $callbackId) {
	
   global $fp;
	
  /*
   * Byte 0 : Start of Frame (0x01)
   * Byte 1 : Length of frame - number of bytes to follow
   * Byte 2 : Request (0x00) 
   * Byte 3 : SendData (0x13)
   * Byte 4 : NodeId
   * Byte 5 : SPECIFIC_GET (0x02)
   * Byte 6 : Command Class COMMAND_CLASS_MANUFACTURER 0x72
	* Byte 7 : 04
	* Byte 8 : 25
   * Byte 9 : CallBackId 
   * Byte 10 : Last byte is checksum
   */
		
   $tmp = "GetManufacturer " ; 
	
   plaatprotect_log($tmp);
	
   $command = hex2bin("01090013".int2hex($node)."02720425".int2hex($callbackId));
   $command .= GenerateChecksum($command);
   LogTxCommand($command);
   fwrite($fp, $command, strlen($command));
}


/**
 ********************************
 * Received ZWave packet
 ********************************
 */
 
function decodeAlarm($data) {
	
	$tmp = "";
	$nodeId = bin2hex(substr($data,5,1));
	$command = ord(substr($data,8,1));
	
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
					  plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"off"}');
					  break;
					  
		case 0x03: $tmp .= 'AlarmVibrationDetected';
					  plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"vibration"}');
					  break;
					  
		case 0x08: $tmp .= 'AlarmMotionDetected';
					  plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"set", "alarm":"motion"}');
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
					  $value = (((ord(substr($data,11,1)))*256)+ord(substr($data,12,1)))/10;
					  $tmp .= 'Value='.$value;
					  plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "temperature":'.$value.'}');
					  break;
		
		case 0x03: $tmp .= 'Luminance ';
					  $value = (((ord(substr($data,11,1)))*256)+ord(substr($data,12,1)));
					  $tmp .= 'Value='.$value;
					  plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "luminance":'.$value.'}');
					  break;
			  
		case 0x05: $tmp .= 'Humidity ';
					  $value = ord(substr($data,11,1));
					  $tmp .= 'Value='.$value;
					  plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "humidity":'.$value.'}');
					  break;
					 
		case 0x1b: $tmp .= 'Ultraviolet ';
					  $value = ord(substr($data,11,1));
					  $tmp .= 'Value='.$value;
					  plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "ultraviolet":'.$value.'}');
					  break;
	}
	
	return $tmp;
}

function decodeManufacturer($nodeId, $data) {

  $manufactureId = GetHexString(substr($data,9,2));
  $deviceType = GetHexString(substr($data,11,2));
  $deviceId = GetHexString(substr($data,13,2));
 
  $tmp = "manufactureId=[".$manufactureId."] DeviceType=[".$deviceType."] DeviceId=[".$deviceId."]";
  $tmp .= decodeManufacture($nodeId, $manufactureId, $deviceType, $deviceId);
  
  return $tmp;
}

function decodeApplicationCommandHandler($data) {

  $nodeId = bin2hex(substr($data,5,1));
 
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
					
	 case 0x72: $tmp .= 'Manufacturer ';
				   $tmp .= decodeManufacturer($nodeId, $data);
					break;

    case 0x80: $tmp .= 'Battery ';
					$command= ord(substr($data,8,1));
					switch ($command) {
					
                   case 0x02: $tmp .= 'Get ';
                              break;
										
                   case 0x03: $tmp .= 'Report ';
										$value = ord(substr($data,9,1));
										$tmp .= 'BatteryValue='.$value.'%';

										plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"report", "battery":'.$value.'}');
                              break;
               }
               break;
					
	case 0x84:  $tmp .=  'Received Wakeup Notification ';
					plaatprotect_db_event_insert(CATEGORY_ZWAVE, '{"zid":'.hexdec($nodeId).',"type":"notification", "value":"wakeup"}');
			      break;

    default:   $tmp .= 'Unknown';
               break;
  }
  plaatprotect_log($tmp);
  
   if ($commandClass==0x84) {
	
		echo "\r\n";
		GetManufacturer(hexdec($nodeId), hexdec($nodeId));
   }  
}

function decodeSerialApiGetCapabilities($data) {

  $serialAPIVersion = GetHexString(substr($data,4,2));
  $manufactureId = GetHexString(substr($data,6,2));
  $deviceType = GetHexString(substr($data,8,2));
  $deviceId = GetHexString(substr($data,10,2));
 
  $tmp = "SerialApiGetCapabilities serialAPIVersion=[".$serialAPIVersion."] manufactureId=[".$manufactureId."] DeviceType=[".$deviceType."] DeviceId=[".$deviceId."]";
  $tmp .= decodeManufacture(1, $manufactureId, $deviceType, $deviceId);
  plaatprotect_log($tmp);  
}

function decodeSerialInit($data) {

 $count = 0;

 $tmp = "Available Nodes ";
 
 for ($i=7; $i<36; $i++ ) {
   $raw_node = ord(substr($data,$i,1));
  
   for ($j=0; $j<8; $j++) {
      if (($raw_node & (0x01 << $j)) != 0x00)
         $tmp .= $j+1+(8*$count).' ';
      }
      $count++;
   }

  plaatprotect_log($tmp);  
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

  plaatprotect_log($tmp);
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
  plaatprotect_log($tmp);
}

function decodeSendGetVersion($data) {

  $zWaveLibraryType = $data[16];
  $zWaveVersion = substr($data,4,15);
 
  plaatprotect_log("SendGetVersion WaveVersion=[".$zWaveVersion."] LibraryType=[0x".bin2hex($zWaveLibraryType)."]");
}

function decodeMemoryId($data) {

  $homeId = GetHexString(substr($data,4,4));
  $nodeId = GetHexString(substr($data,8,1));
 
  plaatprotect_log("SendGetMemoryId HomeId=[".$homeId."] NodeId=[".$nodeId."]");
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
 
    case 0x00: $tmp .= "Delivered to Z-Wave stack (Ack received)";
	       break;
    case 0x01: $tmp .= "Delivered to Z-Wave stack (No Ack, device may be a sleep)";
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
  plaatprotect_log($tmp);
}

function DecodeMessage($data) {

  /*
   * Byte 0 : SOF (Start of Frame) 0x01
   * Byte 1 : Length of frame - number of bytes to follow
   * Byte 2 : Response (0x01)
   * Byte 3 : Command
   */
	
   switch (ord($data[3])) {


		case 0x02: 	decodeSerialInit($data);
						break;
						
		case 0x04: 	decodeApplicationCommandHandler($data);
						break;
						
		case 0x07:	decodeSerialApiGetCapabilities($data);
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
						
      default:		plaatprotect_log("Unknown message");
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
      if ($timer>100) {
        break;
      } else {
        continue;
      }
    }  

    $timer=0;
    $data .= $c;

    echo $c;

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

function plaatprotect_zwave_state_machine() {
	
	plaatprotect_log("Idle");
	
	$row = plaatprotect_db_event(CATEGORY_ZWAVE_CONTROL);			
	if (isset($row->eid)) {
	
		plaatprotect_log("Inbound zwave event: ".$row->action);
	
		$data = json_decode($row->action);
				
		if ($data->action=="init") {

			/* Init ZWave layer */
			SendGetVersion();
			Receive();

			/* Init ZWave HomeId */
			SendGetMemoryId();
			Receive();

			/* Init information of all nodes in ZWave network */
			SendGetInitData();
			Receive();

			/* Get Manufactor controller */	
			SendSerialApiGetCapabilities();
			Receive();
		}

		if ($data->action=="reset") {

			/* Get for all zWave node information */
			$sql2  = 'select zid from zwave';	
			$result2 = plaatprotect_db_query($sql2);
	
			while ($row2 = plaatprotect_db_fetch_object($result2)) {

				SendGetIdentifyNode($row2->zid);
				Receive();
   
				//SendRequestNodeNeighborUpdate($row->nodeid);
				//Receive();
 
				SendGetRouteInfo($row2->zid);
				Receive();
				
				/* Get Manufacturer of sirene */
				GetManufacturer($row2->zid, $row2->zid);
				Receive();
				Receive();
			}
		}	
		
		if ($data->action=="sirene") {
		
			if ($data->value=="on") {

				/* Init ZWave Horn (NodeId=2) (Sound=2) (Volume=1) (CallBackId="ff")*/
				#SendDataInitHorn(2, 2, 1, "ff");
				#Receive();
				#Receive();
				
				/* Enable Sirene */
				SendDataActiveHorn($data->zid, 1, $data->zid);
				Receive();
			}
			
			if ($data->value=="off") {
			
				/* Disable Sirene */
				SendDataActiveHorn($data->zid, 0, $data->zid);
				Receive();
				SendDataActiveHorn($data->zid, 0, $data->zid);
				Receive();
				SendDataActiveHorn($data->zid, 0, $data->zid);
				Receive();
				
				/* Get Manufacturer of sirene */
				GetManufacturer($data->zid, $data->zid);
				Receive();
				Receive();
			}
		}
		
		$row->processed=1;
		plaatprotect_db_event_update($row);
	}
}
	
plaatprotect_log("ZWave Interface - starting...");
	
/* Read Zwave incoming events endless */
while (true) {	

   Receive();
	
	// Process state
	plaatprotect_zwave_state_machine(); 
}

plaatprotect_log("ZWave Interface - ending...");

unlink( LOCK_FILE ); 
exit(0); 

?>

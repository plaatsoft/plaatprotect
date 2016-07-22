<?php

function plaatprotect_hue_all_off() {

	$sql = 'select hid from hue';
	$result = plaatprotect_db_query($sql);
	while ($row = plaatprotect_db_fetch_object($result)) {	
		plaatprotect_set_hue($row->hid, "false");
	}
}

function plaatprotect_set_hue($hue_bulb_nr, $value) {
	
 	$hue_ip = plaatprotect_db_config_value('hue_ip_address',CATEGORY_ZIGBEE);
 	$hue_key = plaatprotect_db_config_value('hue_key',CATEGORY_ZIGBEE);
	
   $hue_url = "http://".$hue_ip."/api/".$hue_key."/lights/".$hue_bulb_nr."/state";
	
	//plaatprotect_log($hue_url.' '.$value);

   @file_get_contents($hue_url, false, stream_context_create(["http" => [
      "method" => "PUT", "header" => "Content-type: application/json",
      "content" => "{\"on\":". $value."}"
    ]]));
}
    
function plaatprotect_get_inventory_hue() {
		
 	$hue_ip = plaatprotect_db_config_value('hue_ip_address',CATEGORY_ZIGBEE);
 	$hue_key = plaatprotect_db_config_value('hue_key',CATEGORY_ZIGBEE);
	
   $hue_url = "http://".$hue_ip."/api/".$hue_key."/lights/";
	
   @$json = file_get_contents($hue_url);
	
	/*$json= '{ "1": { "name": "Livingroom",
				         "type": "Dimmable light",
		               "manufacturername": "Philips",
						   "swversion" : "5.38.15095",
							"state": {
											"reachable" : "1",
											"on": "0"
										}
						},
				"2": { "name": "Kitchen",
				         "type": "Color Light",
		               "manufacturername": "Philips",
						   "swversion" : "5.23.13452",
							"state": {
											"reachable" : "1",
											"on": "1"
										}
						},
				"3": { "name": "Garage",
				         "type": "Dimmable light",
		               "manufacturername": "Philips",
						   "swversion" : "5.38.15095",
							"state": {
											"reachable" : "1",
											"on": "0"
										}
						},
				"4": { "name": "Bedroom Parents",
				        "type": "Dimmable light",
		               "manufacturername": "Philips",
						   "swversion" : "5.38.15095",
							"state": {
											"reachable" : "1",
											"on": "0"
										}
						},
				"5": { "name": "Bedroom Kid 1",
				         "type": "Dimmable light",
		               "manufacturername": "Philips",
						   "swversion" : "5.38.15095",
							"state": {
											"reachable" : "1",
											"on": "0"
										}
						},
				"6": { "name": "Bedroom kid 2",
				         "type": "Color Light",
		               "manufacturername": "Philips",
						   "swversion" : "5.23.13452",
							"state": {
											"reachable" : "0",
											"on": "0"
										}
						},
				"7": { "name": "Bedroom kid 3",
				         "type": "Dimmable light",
		               "manufacturername": "Philips",
						   "swversion" : "5.38.15095",
							"state": {
											"reachable" : "0",
											"on": "0"
										}						
						}
				}';*/

	$data = json_decode($json);
	
	//var_dump($data);	
   return $data;
}


?>
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
 * @brief contain android push logic
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

function plaatprotect_mobile_notification($topic, $content, $severity=0) {

  $mobile_present = plaatprotect_db_config_value('mobile_present', CATEGORY_MOBILE);

  if ($mobile_present=="true" ) {
  
		plaatprotect_log("plaatprotect_mobile_notification: on");

		$nma_key = plaatprotect_db_config_value('mobile_nma_key', CATEGORY_MOBILE);

		require_once 'nmaApi.class.php';

		$nma = new nmaApi(array('apikey' => $nma_key));
		if($nma->verify()) {
				$nma->notify('PlaatProtect', $topic, $content, $severity );
		} else {
			plaatprotect_log("plaatprotect_mobile_notification: authenication failed!");
		}
	}	
}

?>
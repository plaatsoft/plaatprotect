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
 * @brief contain email notification logic
 */
 
function plaatprotect_email_notification($subject, $body) {

	$email_present = plaatprotect_db_config_value('email_present', CATEGORY_EMAIL);

	if ($email_present=="yes" ) {
		$email = plaatprotect_db_config_value('email_address', CATEGORY_EMAIL);

		$header  = 'From: PlaatProtect <"info@protect.plaatsoft.nl">'."\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	
		if (mail($email, $subject, $body, $header)) {
			plaatprotect_log("Email to ".$email);
		} else {
			plaatprotect_log("Email to ".$email.' failed');
		}
	}
}


?>
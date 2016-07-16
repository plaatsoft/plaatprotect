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
 * @brief contain email notification logic
 */
 
function plaatprotect_email_notification($topic, $content) {

  $email_present = plaatprotect_db_config_value('email_present', CATEGORY_EMAIL);

  if ($notification_present=="true" ) {
  	$email_address = plaatprotect_db_config_value('email_address', CATEGORY_EMAIL);

	sendmail($email_address, $topic, $content);
}


?>
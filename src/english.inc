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
 * @brief contain english translation
 */
  
/*
** ------------------
** GENERAL
** ------------------
*/

$lang['TITLE'] = 'PlaatProtect';
$lang['LINK_COPYRIGHT'] = '<a class="normal_link" href="http://www.plaatsoft.nl/">PlaatSoft</a> 2008-'.date("Y").' - All Copyright Reserved ';
$lang['THEME_TO_LIGHT'] = 'Light theme';
$lang['THEME_TO_DARK'] = 'Dark theme';
$lang['ENGLISH'] = 'English';
$lang['DUTCH'] = 'Dutch';

$lang['DAY_0']           = 'Sun';
$lang['DAY_1']           = 'Mon';
$lang['DAY_2']           = 'Tue';
$lang['DAY_3']           = 'Wed';
$lang['DAY_4']           = 'Thu';
$lang['DAY_5']           = 'Fri';
$lang['DAY_6']           = 'Sat';

/*
** ------------------
** LINKS
** ------------------
*/

$lang['LINK_HOME']           = i('home'). 'Home'; 
$lang['LINK_PREV']          = i('chevron-left') . 'Previous'; 
$lang['LINK_NEXT']          = 'Next' . i('chevron-right'); 
$lang['LINK_EDIT']          = i('edit') . 'Edit'; 
$lang['LINK_INSERT']        = i('plus') . 'Insert';  
$lang['LINK_UPDATE']        = i('edit') . 'Update';  
$lang['LINK_EXECUTE']       = i('play') . 'Execute'; 
$lang['LINK_SAVE']          = i('edit') . 'Save'; 
$lang['LINK_CANCEL']        = i('times') . 'Cancel';
$lang['LINK_LOGIN']         = 'Login';
$lang['LINK_BACK']          = i('home') . 'Back'; 
$lang['LINK_REMOVE']        = i('remove'); 
$lang['LINK_PLAY']          = i('play'); 
$lang['LINK_STOP']          = i('stop'); 
$lang['LINK_NEXT_STEP']     = i('step-forward'); 
$lang['LINK_PREV_STEP']     = i('step-backward'); 
$lang['LINK_NEXT_FAST']     = i('forward'); 
$lang['LINK_PREV_FAST']     = i('backward'); 
$lang['LINK_END']           = i('fast-forward'); 
$lang['LINK_BEGIN']         = i('fast-backward'); 

$lang['LINK_LOGGING']       = i('cog') . 'Alarm Logging';  
$lang['LINK_SETTINGS']      = i('cog') . 'Settings';  
$lang['LINK_RELEASE_NOTES'] = i('align-left') . 'Release Notes';
$lang['LINK_ABOUT']         = i('users') . 'About';
$lang['LINK_DONATE']        = i('money') . 'Donate';
$lang['LINK_DELETE']        = i('remove').'Delete'; 
$lang['LINK_WEBCAM']        = i('camera') . 'Webcams'; 
$lang['LINK_HUE']           = i('lightbulb-o') . 'HUE';
$lang['LINK_PICTURE']       = i('camera') . 'Picture'; 
$lang['LINK_ARCHIVE']       = i('folder-open') . 'Archive'; 
$lang['LINK_ZWAVE']         = i('folder-open') . 'ZWave'; 

 
/*
** ------------------
** HOME
** ------------------
*/

$lang['LABEL_PASSWORD'] = 'Password';

$lang ['CONGIG_BAD' ] = 'The following file "config.inc" is missing in installation directory.<br/><br/>
PlaatProtect can not  work without!<br/><br/>
Rename config.inc.sample to config.inc, update the database settings en press F5 in your browser!';

$lang['DATABASE_CONNECTION_FAILED' ] = 'The connection to the database failed. Please check if config.inc settings are right!';

/*
** ------------------
** ABOUT
** ------------------
*/

$lang['ABOUT_TITLE'] = 'About';
$lang['ABOUT_CONTENT'] = 'PlaatProtect is created by PlaatSoft.';

$lang['DISCLAIMER_TITLE'] = 'Disclaimer';
$lang['DISCLAIMER_CONTENT'] = 'The program is provided AS IT IS with NO WARRANTY OF ANY KIND,</br>  
INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY AND<br/> FITNESS FOR A PARTICULAR PURPOSE.<br/>';

$lang['CREDITS_TITLE'] = 'Credits';
$lang['CREDITS_CONTENT'] = 'The following people helped creating PlaatProtect:<br/><br/>
wplaat (Architect / Developer)<br/>
bplaat (Graphical Designer / Developer)<br/>
lplaat (Tester)';

/*
** ------------------
** DONATE
** ------------------
*/

$lang['DONATE_TITLE'] = 'Donate';
$lang['DONATE_CONTENT'] = 'PlaatProtect software may be used free of charge, <br/>but if you wish to express your appreciation<br/>
for the time and resources the author spent developing and supporting <br/>it over the years, we do accept and appreciate donations.
<br/><br/>
To make a donation online using your credit card, or PayPal account,<br/>click below and enter the amount 
you would like to contribute.<br/>Your credit card will be processed by PayPal, a trusted name in secure online transactions.';

/*
** ------------------
** SETTING
** ------------------
*/

$lang['SETTING_TITLE'] = 'Settings';

$lang['LABEL_TOKEN'] = 'Key'; 
$lang['LABEL_VALUE'] = 'Value'; 
$lang['LABEL_DESCRIPTION'] = 'Description'; 

$lang['database_version'] = 'Current database version';
$lang['request_counter'] = 'Page request counter';

$lang['zwave_present'] = 'Zwave present';

$lang['system_name'] = 'System name';

$lang['home_password'] = 'Protect application with a password.';
$lang['settings_password'] = 'Protect settings with a password.';

$lang['webcam_name'] = 'Webcam name';
$lang['webcam_description'] = 'Webcam description';
$lang['webcam_resolution'] = 'Webcam Resolution';
$lang['webcam_present'] = 'Webcam present';
$lang['webcam_device'] = 'Webcam device mapping';
$lang['webcam_fps'] = 'Webcam Frames Per Second';
$lang['webcam_no_motion_area'] = 'Webcam no motion detect area';

$lang['hue_description'] = 'Philips HUE description';
$lang['hue_ip_address'] = 'Philips HUE IP address of bridge';
$lang['hue_key'] = 'Philips HUE access key';
$lang['hue_present'] = 'Philips HUE present';

$lang['notification_present'] = 'Notifcation present';
$lang['notification_nma_key'] = 'Notify My Android key';

$lang['CATEGORY0']  = 'General'; 
$lang['CATEGORY11'] = 'Z-Wave'; 
$lang['CATEGORY51'] = 'Security'; 
$lang['CATEGORY52'] = 'Look and Feel'; 
$lang['CATEGORY61'] = 'Webcam 1'; 
$lang['CATEGORY62'] = 'Webcam 2'; 
$lang['CATEGORY71'] = 'Philips HUE'; 
$lang['CATEGORY81'] = 'Notification'; 

/*
** ------------------
** WEBCAM
** ------------------
*/

$lang['TITLE_WEBCAM'] ='Webcams';
$lang['TITLE_ARCHIVE' ] = 'Archive';

/*
** ------------------
** LOGGING
** ------------------
*/

$lang['TITLE_LOGGING'] ='Alarm Logging';


/*
** ------------------
** HUE
** ------------------
*/

$lang['TITLE_HUE'] ='Philips HUE System';

/*
** ------------------
** ZWAVE
** ------------------
*/

$lang['TITLE_ZWAVE'] ='ZWAVE System';


/*
** ------------------
** THE END
** ------------------
*/

?>

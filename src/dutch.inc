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
 * @brief contain dutch translation
 */

/*
** ------------------
** GENERAL
** ------------------
*/

$lang['TITLE'] = 'PlaatProtect';
$lang['LINK_COPYRIGHT'] = '<a class="normal_link" href="http://www.plaatsoft.nl/">PlaatSoft</a> 2008-'.date("Y").' - All Copyright Reserved ';
$lang['THEME_TO_LIGHT'] = 'Licht thema';
$lang['THEME_TO_DARK'] = 'Donker thema';
$lang['ENGLISH'] = 'Engels';
$lang['DUTCH'] = 'Nederlands';

$lang['DAY_0']           = 'Zo';
$lang['DAY_1']           = 'Ma';
$lang['DAY_2']           = 'Di';
$lang['DAY_3']           = 'Wo';
$lang['DAY_4']           = 'Do';
$lang['DAY_5']           = 'Vr';
$lang['DAY_6']           = 'Za';

/*
** ------------------
** LINKS
** ------------------
*/

$lang['LINK_HOME']           = i('home') . 'Hoofdmenu'; 
$lang['LINK_PREV']           = i('chevron-left') . 'Vorige'; 
$lang['LINK_NEXT']           = 'Volgende' . i('chevron-right');
$lang['LINK_PREV_YEAR']      = i('chevron-left') . 'Vorig Jaar'; 
$lang['LINK_PREV_MONTH']     = i('chevron-left') . 'Vorige Maand'; 
$lang['LINK_PREV_DAY']       = i('chevron-left') . 'Vorige Dag'; 
$lang['LINK_NEXT_YEAR']      = 'Volgend Jaar' . i('chevron-right'); 
$lang['LINK_NEXT_MONTH']     = 'Volgende Maand' . i('chevron-right'); 
$lang['LINK_NEXT_DAY']       = 'Volgende Dag' . i('chevron-right'); 
$lang['LINK_EDIT']           = i('edit') . 'Aanpassen'; 

$lang['LINK_INSERT']         = i('plus') . 'Toevoegen'; 
$lang['LINK_UPDATE']         = i('edit') . 'Bijwerken'; 
$lang['LINK_EXECUTE']        = i('play') . 'Uitvoeren'; 
$lang['LINK_SAVE']           = i('edit') . 'Opslaan'; 
$lang['LINK_CANCEL']         = i('times') . 'Annuleren'; 
$lang['LINK_SETTINGS']       = i('cog') . 'Configuratie'; 
$lang['LINK_MAX']            = i('bolt') . 'Piek';
$lang['LINK_BACKUP']         = i('archive') . 'Export naar SQL';
$lang['LINK_EXPORT']         = i('download') . 'Export naar CSV';
$lang['LINK_LOGIN']          = 'Login';
$lang['LINK_BACK']           = i('home') . 'Terug'; 
$lang['LINK_SYSTEM'] 	     = i('fort-awesome') .'Systeem Overzicht';
$lang['LINK_RELEASE_NOTES']  = i('align-left') . 'Release Notes';
$lang['LINK_ABOUT']          = i('users') . 'Over';
$lang['LINK_DONATE']         = i('money') . 'Donatie';
$lang['LINK_DELETE']         = i('remove').'Verwijderen'; 
$lang['LINK_WEBCAM']         = i('camera') . 'Webcams'; 
$lang['LINK_HUE']            = i('lightbulb-o') . 'HUE';
$lang['LINK_PICTURE']        = i('camera') . 'Foto'; 
$lang['LINK_ARCHIVE']        = i('folder-open') . 'Archief'; 
$lang['LINK_PLAY']           = i('play');
$lang['LINK_STOP']           = i('stop');
$lang['LINK_REMOVE']         = i('remove'); 
$lang['LINK_NEXT_STEP']      = i('step-forward');
$lang['LINK_PREV_STEP']      = i('step-backward');
$lang['LINK_NEXT_FAST']      = i('forward');
$lang['LINK_PREV_FAST']      = i('backward');
$lang['LINK_END']            = i('fast-forward');
$lang['LINK_BEGIN']          = i('fast-backward');
$lang['LINK_ZWAVE']         = i('folder-open') . 'ZWave'; 

/*
** ------------------
** HOME
** ------------------
*/

$lang['LABEL_PASSWORD'] = 'Wachtwoord';

$lang ['CONGIG_BAD' ] = 'Het volgende bestand "config.inc" mist in de installatie directory.<br/><br/>
PlaatProtect werkt niet zonder dit bestand!<br/><br/>
Hernoem config.inc.sample naar config.inc, zet de database instellingen goed en druk op F5 in je browser!';

$lang['DATABASE_CONNECTION_FAILED' ] = 'De verbinding naar de database is niet goed. Controleer of het config.inc bestand de goede instellingen bevat!';

/*
** ------------------
** ABOUT
** ------------------
*/

$lang['ABOUT_TITLE'] = 'Over';
$lang['ABOUT_CONTENT'] = 'PlaatProtect is gemaakt door PlaatSoft.';

$lang['DISCLAIMER_TITLE'] = 'Disclaimer';
$lang['DISCLAIMER_CONTENT'] = 'Deze tool wordt zonder enige garantie geleverd.<br/>De auteurs kunnen nergens aansprakelijk voor worden gesteld.<br/>';

$lang['CREDITS_TITLE'] = 'Dankbetuiging';
$lang['CREDITS_CONTENT'] = 'De volgende mensen hebben PlaatProtect mogelijk gemaakt:<br/><br/>
wplaat (Architect / Ontwikkelaar)</br>
bplaat (Grafisch Ontwerper / Ontwikkelaar)</br>
lplaat (Tester)<br/>';

/*
** ------------------
** DONATE
** ------------------
*/

$lang['DONATE_TITLE'] = 'Donate';
$lang['DONATE_CONTENT'] = 'PlaatProtect software kan gratis gebruikt worden.<br/>
Als u uw waardering wil uiten voor de tijd en de middelen die de <br/>
auteurs besteed hebben aan de ontwikkeling accepteren wij een donatie.<br/><br/>

U kunt een donatie online overmaken met een creditcard of PayPal-account.<br/>
Klik hiervoor op het onderstaande logo en voer het bedrag in wat u wil doneren.<br/>
Uw transactie zal verwerkt worden door PayPal, een vertrouwde naam<br/>
in de beveiligde online transacties.';

/*
** ------------------
** SETTING
** ------------------
*/

$lang['SETTING_TITLE'] = 'Configuratie';
$lang['LABEL_TOKEN'] = 'Item'; 
$lang['LABEL_VALUE'] = 'Waarde'; 
$lang['LABEL_DESCRIPTION'] = 'Omschrijving'; 

$lang['database_version'] = 'Huidige database versie';
$lang['request_counter'] = 'Pagina request counter';

$lang['zwave_present'] = 'Zwave presemt';

$lang['home_password'] = 'Bescherm toegang met een wachtwoord.';
$lang['settings_password'] = 'Bescherm configuratie met een wachtwoord.';

$lang['webcam_name'] = 'Webcam naam';
$lang['webcam_description'] = 'Webcam omschrijving';
$lang['webcam_resolution'] = 'Webcam resolutie';
$lang['webcam_present'] = 'Webcam aanwezig';
$lang['webcam_device'] = 'Webcam device mapping';
$lang['webcam_fps'] = 'Webcam frames per seconden';
$lang['webcam_no_motion_area'] = 'Webcam geen beweging detectie regio';

$lang['hue_description'] = 'Philips HUE description';
$lang['hue_ip_address'] = 'Philips HUE IP address of bridge';
$lang['hue_key'] = 'Philips HUE access key';
$lang['hue_present'] = 'Philips HUE aanwezig';

$lang['notification_present'] = 'Notification aanwezig';
$lang['notification_nma_key'] = 'Notify My Andriod Key';

$lang['CATEGORY0']  = 'Algemeen'; 
$lang['CATEGORY11'] = 'Z-Wave'; 
$lang['CATEGORY51'] = 'Beveiliging'; 
$lang['CATEGORY61'] = 'Webcam 1'; 
$lang['CATEGORY62'] = 'Webcam 2'; 
$lang['CATEGORY71'] = 'Philips HUE'; 
$lang['CATEGORY81'] = 'Android Notification'; 

/*
** ------------------
** SYSTEM
** ------------------
*/

$lang['SYSTEM_TITLE'] = 'Systeem Overzicht';

/*
** ------------------
** WEBCAM
** ------------------
*/

$lang['TITLE_WEBCAM'] ='Webcams';
$lang['TITLE_ARCHIVE' ] = 'Archief';

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

<?php
/* 
**  ===========
**  plaatprotect
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

/*
** ---------------------
** BACKUP
** ---------------------
*/

$time_start = microtime(true);

include "config.php";
include "general.php";
include "database.php";

function plaatprotect_cleanup_old_backup_files() {

	$directory = BASE_DIR.'/backup';
	$older = 30;

	if (file_exists($directory)) {
		foreach (new DirectoryIterator($directory) as $fileInfo) {
			if ($fileInfo->isDot()) {
				continue;
			}
			if ($fileInfo->isFile() && time() - $fileInfo->getCTime() >= $older*24*60*60) {
				unlink($fileInfo->getRealPath());
			}
		}
	}
}

function plaatprotect_backup_event() {

	/* input */
	global $dbuser;
	global $dbpass;
	global $dbhost;
	global $dbname;
	
	$filename = plaatprotect_db_config_value('system_name', CATEGORY_GENERAL);
	if (strlen($filename)==0) {
		$filename=t('TITLE');
	}
	$filename = strtolower($filename);
	
	/* Create new database backup file */
	$filename = BASE_DIR.'/backup/'.$filename.'-'.uniqid().'.sql';

    /* Remove old file if it exists */
    @unlink($filename.'.gz');

        /* Make mysql backup */	
	$command = 'mysqldump --user='.$dbuser.' --password='.$dbpass.' --host='.$dbhost.' '.$dbname.' > '.$filename;
	system($command);
	
    /* Zip database dump file */	
	$command = 'gzip '.$filename;
	system($command);
}

plaatprotect_db_connect($dbhost, $dbuser, $dbpass, $dbname);
plaatprotect_cleanup_old_backup_files();
plaatprotect_backup_event();
plaatprotect_db_close();

// Calculate to page render time
$time_end = microtime(true);
$time = $time_end - $time_start;

if (DEBUG==1) {
	echo "backup took ".round($time,2)." secs";
}

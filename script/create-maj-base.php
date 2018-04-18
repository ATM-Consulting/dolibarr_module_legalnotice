<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 */

if(!defined('INC_FROM_DOLIBARR')) {
	define('INC_FROM_CRON_SCRIPT', true);

	require('../config.php');

}

global $db;

// uncomment


dol_include_once('/legalnotice/class/legalnotice.class.php');

$o=new LegalNotice($db);
$o->init_db_by_vars();


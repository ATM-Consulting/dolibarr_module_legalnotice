<?php
// Dolibarr environment
if (!defined('INC_FROM_DOLIBARR')) {
	define('INC_FROM_DOLIBARR', true);
	$res = @include("../../main.inc.php");
	if (! $res) {
		$res = @include("../../../main.inc.php");
	}
}

require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";

/**
 * Update field product_type and is_assuj_tva from legalnotice table
 * @return int
 */
function runUpdateLegalNoticeTable() {

	global $db;

	$db->begin();

	$erorr = 0;
	// on passe à 2 les valeurs des ProductType produit ET service
	if (updateDatabase($db, 'legalnotice', 'product_type', -1, 2 ) == 0) {
		$erorr++;
	}

	// on passe à 3 les valeurs des ProductType produit OU service
	if (updateDatabase($db, 'legalnotice', 'product_type', -2, 3 ) == 0) {
		$erorr++;
	}

	// on passe à 2 les valeurs des ProductType dont la TVA n'est pas prise en compte
	if (updateDatabase($db, 'legalnotice', 'is_assuj_tva', -1, 2 ) == 0) {
		$erorr++;
	}

	if ($erorr > 0) {
		$db->rollback();
		dol_syslog($db->lasterror(), LOG_ERR);
		dol_print_error($db, $db->lasterror());
		setEventMessages($db->lasterror(), null, 'errors');
		return 0;
	} else {
		$db->commit();
		return 1;
	}

}

/**
 * update value in field for a specific table
 *
 * @param $db
 * @param string $table Name of the table
 * @param string $field Name of fields
 * @param int $oldValue Value to search and replace
 * @param int $newValue New value to set
 * @return int
 */
function updateDatabase($db, $table, $field, $oldValue, $newValue) {


	$sql= ' UPDATE '.MAIN_DB_PREFIX.$table;
	$sql.= ' SET ' . $field . ' = '. $newValue ;
	$sql.= ' WHERE ' . $field . ' = ' .  $oldValue ;

	return $db->query($sql);

}

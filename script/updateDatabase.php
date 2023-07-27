<?php
// Dolibarr environment
$res = @include("../../main.inc.php"); // From htdocs directory
if (! $res) {
    $res = @include("../../../main.inc.php"); // From "custom" directory
}

require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";

	global $db;

	$db->begin();

	// on passe à 2 les valeurs des ProductType produit ET service
	updateDatabase($db,'legalnotice', 'product_type', -1, 2 );

	// on passe à 3 les valeurs des ProductType produit OU service
	updateDatabase($db,'legalnotice', 'product_type', -2, 3 );

	// on passe à 2 les valeurs des ProductType dont la TVA n'est pas prise en compte
	updateDatabase($db,'legalnotice', 'is_assuj_tva', -1, 2 );

function updateDatabase($db, $table, $field, $oldValue, $newvalue, $error = 0) {
	$sql= ' UPDATE '.MAIN_DB_PREFIX.$table;
	$sql.= ' SET ' . $field . ' = '. $newvalue ;
	$sql.= ' WHERE ' . $field . ' = ' .  $oldValue ;

	$result = $db->query($sql);

	if (!$result) {
		$error++;
		dol_syslog($db->lasterror(), LOG_ERR);
		dol_print_error($db, $db->lasterror());
		setEventMessages($db->lasterror(), null, 'errors');

		$result->error[] = $db->lasterror();
	}

	if (!$error) {
		$db->commit();
	} else {
		$db->rollback();
	}

}


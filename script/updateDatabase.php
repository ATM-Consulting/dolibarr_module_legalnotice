<?php
/* Copyright (C) 2025 ATM Consulting
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */
// Dolibarr environment
$res = @include "../../main.inc.php"; // From htdocs directory
if (! $res) {
	$res = @include "../../../main.inc.php"; // From "custom" directory
}

require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";

/**
 * Update field product_type and is_assuj_tva from legalnotice table
 * @return int
 */
function runUpdateLegalNoticeTable()
{

	global $db;

	$db->begin();

	$erorr = 0;
	// on passe à 2 les valeurs des ProductType produit ET service
	if (updateDatabase($db, 'legalnotice', 'product_type', -1, 2) == 0) {
		$erorr++;
	}

	// on passe à 3 les valeurs des ProductType produit OU service
	if (updateDatabase($db, 'legalnotice', 'product_type', -2, 3) == 0) {
		$erorr++;
	}

	// on passe à 2 les valeurs des ProductType dont la TVA n'est pas prise en compte
	if (updateDatabase($db, 'legalnotice', 'is_assuj_tva', -1, 2) == 0) {
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
 * @param DoliDB $db        Database handler
 * @param string $table     Name of the table
 * @param string $field     Name of the field
 * @param int    $oldValue  Value to search and replace
 * @param int    $newValue  New value to set
 * @return int
 */
function updateDatabase($db, $table, $field, $oldValue, $newValue)
{


	$sql= ' UPDATE '.MAIN_DB_PREFIX.$table;
	$sql.= ' SET ' . $field . ' = '. $newValue ;
	$sql.= ' WHERE ' . $field . ' = ' .  $oldValue ;

	return $db->query($sql);
}

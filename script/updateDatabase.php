<?php
// Dolibarr environment
$res = @include("../../main.inc.php"); // From htdocs directory
if (! $res) {
    $res = @include("../../../main.inc.php"); // From "custom" directory
}

require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";

dol_include_once('/legalnotice/class/legalnotice.class.php');

function updateDatabase() {

    global $db;

    $sql= ' SELECT note';
    $sql.=' FROM '.MAIN_DB_PREFIX.'const';
    $sql.= ' WHERE name = "MAIN_MODULE_LEGALNOTICE"' ;
    $resql = $db->query($sql);

    $LegalNotice = new LegalNotice($db);
    $TLegalNotices = $LegalNotice->fetchAll();

    foreach ($TLegalNotices as $legalNotice) {
        if ($legalNotice->product_type == -1) {
            // on passe à 2 les valeurs
            $sql= ' UPDATE '.MAIN_DB_PREFIX.'legalnotice';
            $sql.= ' SET product_type = 2' ;
            $sql.= ' WHERE rowid = ' . $legalNotice->id ;
            $db->query($sql);
        }

        if ($legalNotice->product_type == -2) {
            // on passe à 3 les valeurs
            $sql= ' UPDATE '.MAIN_DB_PREFIX.'legalnotice';
            $sql.= ' SET product_type = 3' ;
            $sql.= ' WHERE rowid = ' . $legalNotice->id ;
            $db->query($sql);
        }

        if ($legalNotice->is_assuj_tva == -1) {
            // on passe à 2 les valeurs
            $sql= ' UPDATE '.MAIN_DB_PREFIX.'legalnotice';
            $sql.= ' SET is_assuj_tva = 2' ;
            $sql.= ' WHERE rowid = ' . $legalNotice->id ;
            $db->query($sql);
        }
    }
}

updateDatabase();


<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		admin/about.php
 * 	\ingroup	legalnotice
 * 	\brief		This file is an example about page
 * 				Put some comments here
 */
// Dolibarr environment
$res = @include("../../main.inc.php"); // From htdocs directory
if (! $res) {
    $res = @include("../../../main.inc.php"); // From "custom" directory
}

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/legalnotice.lib.php';

// Translations
$langs->load("legalnotice@legalnotice");

// Access control
if (! $user->admin) {
    accessforbidden();
}

/*
 * View
 */
$page_name = "LegalNoticeAbout";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
    . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback,	"object_module.svg@legalnotice");

// Configuration header
$head = legalnoticeAdminPrepareHead();
dol_fiche_head(
    $head,
    'about',
    $langs->trans("Module104949Name"),
    0,
    'legalnotice@legalnotice'
);


require_once __DIR__ . '/../class/techatm.class.php';
$techATM = new \legalnotice\TechATM($db);

require_once __DIR__ . '/../core/modules/modLegalNotice.class.php';
$moduleDescriptor = new modLegalNotice($db);

print $techATM->getAboutPage($moduleDescriptor);

llxFooter();

$db->close();

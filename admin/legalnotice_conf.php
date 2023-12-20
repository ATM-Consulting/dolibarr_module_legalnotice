<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2020 Maxime Kohlhaas <maxime@m-development.com>
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    legalnotice/admin/setup.php
 * \ingroup legalnotice
 * \brief   LegalNotice setup page.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";
require_once '../lib/legalnotice.lib.php';
//require_once "../class/myclass.class.php";

// Translations
$langs->loadLangs(array("admin", "legalnotice@legalnotice"));

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('legalnoticesetup', 'globalsetup'));

// Access control
if (!$user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');
$modulepart = GETPOST('modulepart', 'aZ09');	// Used by actions_setmoduleoptions.inc.php

$value = GETPOST('value', 'alpha');
$label = GETPOST('label', 'alpha');
$scandir = GETPOST('scan_dir', 'alpha');
$type = 'legalnotice';



$error = 0;
$setupnotempty = 0;

// Set this to 1 to use the factory to manage constants. Warning, the generated module will be compatible with version v15+ only
$useFormSetup = 1;

if (!class_exists('FormSetup')) {
	// For retrocompatibility Dolibarr < 16.0
	if (floatval(DOL_VERSION) < 16.0 && !class_exists('FormSetup')) {
		require_once __DIR__.'/../backport/v16/core/class/html.formsetup.class.php';
	} else {
		require_once DOL_DOCUMENT_ROOT.'/core/class/html.formsetup.class.php';
	}
}

$formSetup = new FormSetup($db);
$form = new Form($db);

/*
 * DEMO
// Hôte
$item = $formSetup->newItem('NO_PARAM_JUST_TEXT');
$item->fieldOverride = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'];
$item->cssClass = 'minwidth500';

// Setup conf MYMODULE_MYPARAM1 as a simple string input
$item = $formSetup->newItem('MYMODULE_MYPARAM1');
$item->defaultFieldValue = 'default value';

// Setup conf MYMODULE_MYPARAM1 as a simple textarea input but we replace the text of field title
$item = $formSetup->newItem('MYMODULE_MYPARAM2');
$item->nameText = $item->getNameText().' more html text ';

// Setup conf MYMODULE_MYPARAM3
$item = $formSetup->newItem('MYMODULE_MYPARAM3');
$item->setAsThirdpartyType();

// Setup conf MYMODULE_MYPARAM4 : exemple of quick define write style
$formSetup->newItem('MYMODULE_MYPARAM4')->setAsYesNo();

// Setup conf MYMODULE_MYPARAM5
$formSetup->newItem('MYMODULE_MYPARAM5')->setAsEmailTemplate('thirdparty');

// Setup conf MYMODULE_MYPARAM6
$formSetup->newItem('MYMODULE_MYPARAM6')->setAsSecureKey()->enabled = 0; // disabled

// Setup conf MYMODULE_MYPARAM7
//$formSetup->newItem('MYMODULE_MYPARAM7')->setAsProduct();

$formSetup->newItem('Title')->setAsTitle();






// Setup conf MYMODULE_MYPARAM8
$item = $formSetup->newItem('MYMODULE_MYPARAM8');
$TField = array(
	'test01' => $langs->trans('test01'),
	'test02' => $langs->trans('test02'),
	'test03' => $langs->trans('test03'),
	'test04' => $langs->trans('test04'),
	'test05' => $langs->trans('test05'),
	'test06' => $langs->trans('test06'),
);
$item->setAsMultiSelect($TField);
$item->helpText = $langs->transnoentities('MYMODULE_MYPARAM8');


// Setup conf MYMODULE_MYPARAM9
$formSetup->newItem('MYMODULE_MYPARAM9')->setAsSelect($TField);


// Setup conf MYMODULE_MYPARAM10
$item = $formSetup->newItem('MYMODULE_MYPARAM10');
$item->setAsColor();
$item->defaultFieldValue = '#FF0000';
$item->nameText = $item->getNameText().' more html text ';
$item->fieldInputOverride = '';
$item->helpText = $langs->transnoentities('AnHelpMessage');
//$item->fieldValue = '';
//$item->fieldAttr = array() ; // fields attribute only for compatible fields like input text
//$item->fieldOverride = false; // set this var to override field output will override $fieldInputOverride and $fieldOutputOverride too
//$item->fieldInputOverride = false; // set this var to override field input
//$item->fieldOutputOverride = false; // set this var to override field output
*/


function _setVisibilityExtrafields($extrafields,$elementtype, $value) {
	global $db;

	$sql = 'UPDATE '.$db->prefix().'extrafields SET list='.$value.' WHERE name="'.$extrafields.'" AND elementtype="'.$elementtype.'"';
	return $db->query($sql);
}

// Permet de sélectionner une ou plusieurs mentions sur une proposition commerciale
$item = $formSetup->newItem('LEGALNOTICE_MULTI_NOTICE_PROPAL');
$item->setAsYesNo(); // need to avoid ajax call to skip _setVisibilityExtrafields()
$TField = array(
	0 => $langs->trans('No'),
	1 => $langs->trans('Yes'),
);
$item->setAsSelect($TField);
$item->nameText = $langs->trans('ManageMultiNoticePropal');
$item->saveCallBack = function(FormSetupItem $item){
	if(!_setVisibilityExtrafields('legalnotice_selected_notice', 'propal',$item->fieldValue)){
		return -1;
	}

	$result = dolibarr_set_const($item->db, $item->confKey, $item->fieldValue, 'chaine', 0, '', $item->entity);
	if ($result < 0) {
		return -1;
	} else {
		return 1;
	}
};


// Ne pas concaténer les mentions légales.
$formSetup->newItem('LEGALNOTICE_DO_NOT_CONCAT')->setAsYesNo();






$setupnotempty =+ count($formSetup->items);


$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);


/*
 * Actions
 */

// For retrocompatibility Dolibarr < 15.0
if ( versioncompare(explode('.', DOL_VERSION), array(15)) < 0 && $action == 'update' && !empty($user->admin)) {
	$formSetup->saveConfFromPost();
}

include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';




/*
 * View
 */

$form = new Form($db);

$help_url = '';
$page_name = "LegalNoticeSetup";
llxHeader('', $langs->trans($page_name), $help_url);

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'tools');

// Configuration header
$head = legalnoticeAdminPrepareHead();
print dol_get_fiche_head($head, 'settings', 'Setup', -1, "legalnotice@legalnotice");

// Setup page goes here
echo '<span class="opacitymedium">'.$langs->trans("LegalNoticeSetupPage").'</span><br><br>';

if ($action == 'edit') {
	print $formSetup->generateOutput(true);
	print '<br>';
} elseif (!empty($formSetup->items)) {
	print $formSetup->generateOutput();
	print '<div class="tabsAction">';
	print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=edit&token='.newToken().'">'.$langs->trans("Modify").'</a>';
	print '</div>';
} else {
	print '<br>'.$langs->trans("NothingToSetup");
}


// Page end
print dol_get_fiche_end();

llxFooter();
$db->close();

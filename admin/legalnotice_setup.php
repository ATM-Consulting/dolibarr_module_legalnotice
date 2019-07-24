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
 * 	\file		admin/legalnotice.php
 * 	\ingroup	legalnotice
 * 	\brief		This file is an example module setup page
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

dol_include_once('/legalnotice/class/legalnotice.class.php');

// Translations
$langs->load('admin');
$langs->load('legalnotice@legalnotice');

// Access control
if (! $user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');
$id = GETPOST('id');

$object = new LegalNotice($db);
if (!empty($id)) $object->fetch($id);
/*
 * Actions
 */
if (preg_match('/set_(.*)/',$action,$reg))
{
	$code=$reg[1];
	if (dolibarr_set_const($db, $code, GETPOST($code), 'chaine', 0, '', $conf->entity) > 0)
	{
		header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
}

if (preg_match('/del_(.*)/',$action,$reg))
{
	$code=$reg[1];
	if (dolibarr_del_const($db, $code, 0) > 0)
	{
		Header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
}

if ($action == 'save')
{
	$error = 0;

	$fk_country = GETPOST('fk_country');
	$product_type = (int) GETPOST('product_type');
	$is_assuj_tva = (int) GETPOST('is_assuj_tva');
  $fk_typent = GETPOST('fk_typent');
	$mention = GETPOST('mention');
	$rang = (int) GETPOST('rang');

	if (is_array($fk_typent)) $fk_typent = implode(',', $fk_typent);
	if (strpos($fk_typent, '-1') !== false) $fk_typent = '-1';
  if (is_array($fk_country)) $fk_country = implode(',', $fk_country);
	if (strpos($fk_country, '-1') !== false) $fk_country = '-1'; // évite de selectionner la valeur "all" avec des pays
	if (empty($fk_typent)) { setEventMessage($langs->trans('LegalNotice_FieldTypentRequired'), 'errors'); $error++; }
	if (empty($fk_country)) { setEventMessage($langs->trans('LegalNotice_FieldCountryRequired'), 'errors'); $error++; }
	if (!in_array($product_type, array(-2, -1, 0, 1))) { setEventMessage($langs->trans('LegalNotice_FieldProductTypeRequired'), 'errors'); $error++; }
	if (!in_array($is_assuj_tva, array(-1, 0, 1))) { setEventMessage($langs->trans('LegalNotice_FieldVATUsedRequired'), 'errors'); $error++; }
	if (empty($mention)) { setEventMessage($langs->trans('LegalNotice_FieldMentionRequired'), 'errors'); $error++; }


	if (empty($error))
	{

		$object->fk_country = $fk_country;
		$object->product_type = $product_type;
    $object->fk_typent = $fk_typent;
		$object->is_assuj_tva = $is_assuj_tva;
		$object->mention = $mention;
		$object->rang = $rang;

		$object->create($user);

		header('Location: '.dol_buildpath('/legalnotice/admin/legalnotice_setup.php', 1));
		exit;
	}
}
else if ($action == 'delete')
{
	$object->delete($user);

	header('Location: '.dol_buildpath('/legalnotice/admin/legalnotice_setup.php', 1));
	exit;
}

/*
 * View
 */
$page_name = "LegalNoticeSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
    . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = legalnoticeAdminPrepareHead();
dol_fiche_head(
    $head,
    'legalnotice',
    $langs->trans("Module104949Name"),
    0,
    "legalnotice@legalnotice"
);

// Setup page goes here
$form=new Form($db);
/*
$var=false;
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameters").'</td>'."\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";
print '</tr>';

// Example with a yes / no select
$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("ParamLabel").'</td>';
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="right" width="300">';
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="set_CONSTNAME">';
print $form->selectyesno("CONSTNAME",$conf->global->CONSTNAME,1);
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
print '</form>';
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("ParamLabel").'</td>';
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="300">';
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">'; // Keep form because ajax_constantonoff return single link with <a> if the js is disabled
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="set_CONSTNAME">';
print ajax_constantonoff('CONSTNAME');
print '</form>';
print '</td></tr>';

print '</table>';
print '<br />';
*/

$TCountry = array('-1' => $langs->trans('AllCountry'));

$sql = 'SELECT rowid, code as code_iso, label';
$sql.= ' FROM '.MAIN_DB_PREFIX.'c_country';
$sql.= ' WHERE active > 0 AND rowid > 0';
$resql = $db->query($sql);

if ($resql)
{
	while ($obj = $db->fetch_object($resql))
	{
		$TCountry[$obj->rowid] = ($obj->code_iso && $langs->transnoentitiesnoconv("Country".$obj->code_iso)!="Country".$obj->code_iso?$langs->transnoentitiesnoconv("Country".$obj->code_iso):($obj->label!='-'?$obj->label:''));
	}
}
else
{
	dol_print_error($db);
}

$TTypent = array('-1' => $langs->trans('ContactsAllShort'));

$sql = 'SELECT id, libelle, code';
$sql.= ' FROM '.MAIN_DB_PREFIX.'c_typent';
$sql.= ' WHERE active > 0 AND id > 0';
$resql = $db->query($sql);

if ($resql)
{
	while ($obj = $db->fetch_object($resql))
	{
		$TTypent[$obj->id] = ($obj->code && $langs->transnoentitiesnoconv($obj->code)!=$obj->code?$langs->transnoentitiesnoconv($obj->code):($obj->libelle!='-'?$obj->libelle:''));
	}
}
else
{
	dol_print_error($db);
}

$TProductType = array(0 => $langs->trans('Product'), 1 => $langs->trans('Service'), -1 => $langs->trans('LegalNoticeProductAndService'), -2 => $langs->trans('LegalNoticeProductOrService'));
$TVATused = array(0 => $langs->trans('No'), 1 => $langs->trans('Yes'), -1 => $langs->trans('LegalNoticeWhatEver'));

print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">'; // Keep form because ajax_constantonoff return single link with <a> if the js is disabled
print '<input type="hidden" name="action" value="save" />';
if (!empty($object->id)) print '<input type="hidden" name="id" value="'.$object->id.'" />';
print '<input name="token" value="'.$_SESSION['newtoken'].'" type="hidden">';

print '<table class="noborder" width="100%">';

print '<tr class="liste_titre">';
print '<td width="20%">'.$langs->trans("legalnotice_Country").'</td>';
print '<td width="10%">'.$langs->trans("legalnotice_ProductType").'</td>';
print '<td width="10%">'.$langs->trans("legalnotice_Typent").'</td>';
print '<td width="10%">'.$langs->trans("legalnotice_VATused").'</td>';
print '<td width="40%">'.$langs->trans("legalnotice_Notice").'</td>';
print '<td width="5%">'.$langs->trans("legalnotice_Rang").'</td>';
print '<td width="5%">&nbsp;</td>';
print '</tr>';

$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$form->multiselectarray('fk_country', $TCountry, (!empty($object->fk_country) ? $object->fk_country : array(-1)), 0, 0, 'minwidth200').'</td>';
print '<td>'.$form->selectarray('product_type', $TProductType, $object->product_type).'</td>';
print '<td>'.$form->multiselectarray('fk_typent', $TTypent, (!empty($object->fk_typent) ? $object->fk_typent : array(-1)), 0, 0, 'minwidth200').'</td>';
print '<td>'.$form->selectarray('is_assuj_tva', $TVATused, $object->is_assuj_tva).'</td>';
print '<td>';
if (empty($conf->global->PDF_ALLOW_HTML_FOR_FREE_TEXT))
{
    print '<textarea name="mention" class="flat" cols="120">'.$object->mention.'</textarea>';
}
else
{
    include_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
    $doleditor=new DolEditor('mention', $object->mention,'',120,'dolibarr_notes');
    print $doleditor->Create();
}
print '</td>';
print '<td><input type="text" name="rang" size="3" value="'.(!empty($object->id) ? $object->rang : '').'" /></td>';
print '<td><input class="button" type="submit" value="'.$langs->trans('Save').'" /></td>';
print '</tr>';

print '</table>';
print '</form>';


$TLegalNotice = $object->fetchAll();
print '<table class="noborder" width="100%">';

foreach ($TLegalNotice as &$legal)
{
	$var=!$var;
	print '<tr '.$bc[$var].'>';
	$intersect = array_intersect(array_keys($TCountry), $legal->fk_country);
	print '<td width="20%">';
	foreach ($intersect as $key)
	{
		print '<span class="badge">'.$TCountry[$key].'</span>';
	}
	print '</td>';
	print '<td width="10%">'.$TProductType[$legal->product_type].'</td>';
  $intersect2 = array_intersect(array_keys($TTypent), $legal->fk_typent);
	print '<td width="10%">';
	foreach ($intersect2 as $key)
	{
		print '<span class="badge">'.$TTypent[$key].'</span>';
	}
	print '</td>';
	print '<td width="10%">'.$TVATused[$legal->is_assuj_tva].'</td>';
	print '<td width="40%">'.$legal->mention.'</td>';
	print '<td width="5%">'.$legal->rang.'</td>';
	print '<td width="5%">';
	print '<a href="'.dol_buildpath('/legalnotice/admin/legalnotice_setup.php', 1).'?id='.$legal->id.'">'.img_picto('', 'edit').'</a>';
	print '&nbsp;<a onclick=\'return confirm("'.addslashes($langs->trans('LegalNoticeDeleteConfirm')).'")\' href="'.dol_buildpath('/legalnotice/admin/legalnotice_setup.php', 1).'?id='.$legal->id.'&action=delete">'.img_picto('', 'delete').'</a>';
	print '</td>';
	print '</tr>';
}

print '</table>';

llxFooter();

$db->close();

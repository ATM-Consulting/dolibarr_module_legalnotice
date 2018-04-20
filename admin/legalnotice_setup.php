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
$langs->load("legalnotice@legalnotice");

// Access control
if (! $user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');

$object = new LegalNotice($db);
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

if ($action == 'add_mention')
{
	$error = 0;
	
	$fk_country = GETPOST('fk_country');
	$product_type = (int) GETPOST('product_type');
	$is_assuj_tva = (int) GETPOST('is_assuj_tva');
	$mention = GETPOST('mention');
	
	if (is_array($fk_country)) $fk_country = implode(',', $fk_country);
	if (strpos($fk_country, '-1') !== false) $fk_country = 'all'; // évite de selectionner la valeur "all" avec des pays
	
	if (empty($fk_country)) { setEventMessage($langs->trans('LegalNotice_FieldCountryRequired'), 'errors'); $error++; }
	if ($product_type !== -1 && $product_type !== 0 && $product_type !== 1) { setEventMessage($langs->trans('LegalNotice_FieldProductTypeRequired'), 'errors'); $error++; }
	if ($is_assuj_tva !== -1 && $is_assuj_tva !== 0 && $is_assuj_tva !== 1) { setEventMessage($langs->trans('LegalNotice_FieldVATUsedRequired'), 'errors'); $error++; }
	if (empty($mention)) { setEventMessage($langs->trans('LegalNotice_FieldMentionRequired'), 'errors'); $error++; }
	
	
	if (empty($error))
	{
		$legal_notice = new LegalNotice($db);
		$legal_notice->fk_country = $fk_country;
		$legal_notice->product_type = $product_type;
		$legal_notice->is_assuj_tva = $is_assuj_tva;
		$legal_notice->mention = $mention;
		
		$legal_notice->create($user);
		
		header('Location: '.dol_buildpath('/legalnotice/admin/legalnotice_setup.php', 1));
		exit;
	}
}
else if ($action == 'delete')
{
	$id = GETPOST('id');
	$object->fetch($id);
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
    'settings',
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
$sql.= ' WHERE active > 0';
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

$TProductType = array(0 => $langs->trans('Product'), 1 => $langs->trans('Service'), -1 => $langs->trans('Both'));
$TVATused = array(0 => $langs->trans('No'), 1 => $langs->trans('Yes'), -1 => $langs->trans('Both'));

print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">'; // Keep form because ajax_constantonoff return single link with <a> if the js is disabled
print '<input type="hidden" name="action" value="add_mention" />';
print '<input name="token" value="'.$_SESSION['newtoken'].'" type="hidden">';

print '<table class="noborder" width="100%">';

print '<tr class="liste_titre">';
print '<td width="20%">'.$langs->trans("legalnotice_Country").'</td>';
print '<td width="20%">'.$langs->trans("legalnotice_ProductType").'</td>';
print '<td width="20%">'.$langs->trans("legalnotice_VATused").'</td>';
print '<td width="35%">'.$langs->trans("legalnotice_Notice").'</td>';
print '<td width="5%">&nbsp;</td>';
print '</tr>';

$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$form->multiselectarray('fk_country', $TCountry, array(-1), 0, 0, 'minwidth200').'</td>';
print '<td>'.$form->selectarray('product_type', $TProductType).'</td>';
print '<td>'.$form->selectarray('is_assuj_tva', $TVATused).'</td>';
print '<td><textarea rows="4" cols="50" name="mention"></textarea></td>';
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
	$TKey = explode(',', $legal->fk_country);
	$intersect = array_intersect(array_keys($TCountry), $TKey);
	print '<td width="20%">';
	foreach ($intersect as $key)
	{
		print '<span class="badge">'.$TCountry[$key].'</span>';
	}
	print '</td>';
	print '<td width="20%">'.$TProductType[$legal->product_type].'</td>';
	print '<td width="20%">'.$TVATused[$legal->is_assuj_tva].'</td>';
	print '<td width="35%">'.$legal->mention.'</td>';
	print '<td width="5%"><a href="'.dol_buildpath('/legalnotice/admin/legalnotice_setup.php', 1).'?id='.$legal->id.'&action=delete">'.img_picto('', 'delete').'</a></td>';
	print '</tr>';
}

print '</table>';

llxFooter();

$db->close();
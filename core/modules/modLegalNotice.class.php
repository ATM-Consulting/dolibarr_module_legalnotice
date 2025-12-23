<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\defgroup   legalnotice     Module LegalNotice
 *  \brief      Example of a module descriptor.
 *				Such a file must be copied into htdocs/legalnotice/core/modules directory.
 *  \file       htdocs/legalnotice/core/modules/modLegalNotice.class.php
 *  \ingroup    legalnotice
 *  \brief      Description and activation file for module LegalNotice
 */
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';


/**
 *  Description and activation class for module LegalNotice
 */
class modLegalNotice extends DolibarrModules
{
	/**
	 *   Constructor. Define names, constants, directories, boxes, permissions
	 *
	 *   @param      DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		global $langs,$conf;

		$this->db = $db;

		$this->editor_name = 'ATM Consulting';
		$this->editor_url = 'https://www.atm-consulting.fr';

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 104949; // 104000 to 104999 for ATM CONSULTING
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'legalnotice';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "ATM Consulting";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "Description of module LegalNotice";
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '1.7.4';
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 0;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto='module.svg@legalnotice';

		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		// for default path (eg: /legalnotice/core/xxxxx) (0=disable, 1=enable)
		// for specific path of parts (eg: /legalnotice/core/modules/barcode)
		// for specific css file (eg: /legalnotice/css/legalnotice.css.php)
		$this->module_parts = array(
			'hooks' => array(
				'pdfgeneration',
			)
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/legalnotice/temp");
		$this->dirs = array();

		// Config pages. Put here list of php page, stored into legalnotice/admin directory, to use to setup module.
		$this->config_page_url = array("legalnotice_conf.php@legalnotice");

		// Dependencies
		$this->hidden = false;			// A condition to hide module
		$this->depends = array();		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();	// List of modules id to disable if this one is disabled
		$this->conflictwith = array();	// List of modules id this module is in conflict with
		$this->phpmin = array(7,0);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(16,0);	// Minimum version of Dolibarr required by module
		$this->langfiles = array("legalnotice@legalnotice");

		// Url to the file with your last numberversion of this module
		require_once __DIR__ . '/../../class/techatm.class.php';
		$this->url_last_version = \legalnotice\TechATM::getLastModuleVersionUrl($this);

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(0=>array('MYMODULE_MYNEWCONST1','chaine','myvalue','This is a constant to add',1),
		//                             1=>array('MYMODULE_MYNEWCONST2','chaine','myvalue','This is another constant to add',0, 'current', 1)
		// );
		$this->const = array();

		// Array to add new pages in new tabs
		// Example: $this->tabs = array('objecttype:+tabname1:Title1:legalnotice@legalnotice:$user->hasRight('legalnotice', 'read'):/legalnotice/mynewtab1.php?id=__ID__',  	// To add a new tab identified by code tabname1
		//                              'objecttype:+tabname2:Title2:legalnotice@legalnotice:$user->hasRight('othermodule', 'read'):/legalnotice/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2
		//                              'objecttype:-tabname:NU:conditiontoremove');                                                     						// To remove an existing tab identified by code tabname
		// where objecttype can be
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// 'contact'          to add a tab in contact view
		// 'contract'         to add a tab in contract view
		// 'group'            to add a tab in group view
		// 'intervention'     to add a tab in intervention view
		// 'invoice'          to add a tab in customer invoice view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'member'           to add a tab in fundation member view
		// 'opensurveypoll'	  to add a tab in opensurvey poll view
		// 'order'            to add a tab in customer order view
		// 'order_supplier'   to add a tab in supplier order view
		// 'payment'		  to add a tab in payment view
		// 'payment_supplier' to add a tab in supplier payment view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'project'          to add a tab in project view
		// 'stock'            to add a tab in stock view
		// 'thirdparty'       to add a tab in third party view
		// 'user'             to add a tab in user view
		$this->tabs = array();

		// Dictionaries
		if (!isModEnabled('legalnotice')) {
			$conf->legalnotice=new stdClass();
			$conf->legalnotice->enabled=0;
		}
		$this->dictionaries=array();

		// Boxes
		// Add here list of php file(s) stored in core/boxes that contains class to show a box.
		$this->boxes = array();			// List of boxes
		// Example:
		//$this->boxes=array(array(0=>array('file'=>'myboxa.php','note'=>'','enabledbydefaulton'=>'Home'),1=>array('file'=>'myboxb.php','note'=>''),2=>array('file'=>'myboxc.php','note'=>'')););

		// Permissions
		$this->rights = array();		// Permission array used by this module
		$r=0;

		// Add here list of permission defined by an id, a label, a boolean and two constant strings.

		// Main menu entries
		$this->menu = array();			// List of menus to add
		$r=0;

		// Add here entries to declare new menus

		// Exports
		$r=1;
	}

	/**
	 *		Function called when module is enabled.
	 *		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *		It also creates data directories
	 *
	 *      @param      string	$options    Options when enabling module ('', 'noboxes')
	 *      @return     int             	1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		global $db;
		$sql = array();

		if (!defined('INC_FROM_DOLIBARR')) define('INC_FROM_DOLIBARR', true);

		dol_include_once('/legalnotice/config.php');
		dol_include_once('/legalnotice/script/create-maj-base.php');
		if ($this->needUpdate('1.1.0')) {
			dol_include_once('/legalnotice/script/updateDatabase.php');
			$res = runUpdateLegalNoticeTable();
			if ($res <= 0) {
				return $res;
			}
		}

		$extrafields = new ExtraFields($db);
		$extrafields->addExtraField('legalnotice_selected_notice', 'Mentions complémentaires', 'chkbxlst', '100', '', 'propal', 0, 0, '', array('options'=>array('legalnotice:mention:rowid::' => null)), 1, '', 0);

		$result=$this->_load_tables('/legalnotice/sql/');

		dolibarr_set_const($this->db, 'MAIN_MODULE_LEGALNOTICE', $this->version, 'chaine', 0, '', 0);

		return $this->_init($sql, $options);
	}

	/**
	 *		Function called when module is disabled.
	 *      Remove from database constants, boxes and permissions from Dolibarr database.
	 *		Data directories are not deleted
	 *
	 *      @param      string	$options    Options when enabling module ('', 'noboxes')
	 *      @return     int             	1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql, $options);
	}

	/**
	 * Compare
	 *
	 * @param string $targetVersion numéro de version pour lequel il faut faire la comparaison
	 * @return bool
	 */
	public function needUpdate($targetVersion)
	{
		global $conf;
		if (!getDolGlobalString('MAIN_MODULE_LEGALNOTICE')) {
			return true;
		}

		if (versioncompare(explode('.', $targetVersion), explode('.', getDolGlobalString('MAIN_MODULE_LEGALNOTICE')))>0) {
			return true;
		}

		return false;
	}
}

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
 * \file    class/actions_legalnotice.class.php
 * \ingroup legalnotice
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class ActionsLegalNotice
 */
class ActionsLegalNotice
{
	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function doActions($parameters, &$object, &$action, $hookmanager)
	{
		return 0;
	}

	function beforePDFCreation($parameters, &$object, &$action, $hookmanager)
	{
		global $conf;
		$TContext = explode(':', $parameters['context']);

		if (in_array('invoicecard', $TContext))
		{
			if (!defined('INC_FROM_DOLIBARR')) define('INC_FROM_DOLIBARR', 1);
			dol_include_once('/legalnotice/config.php');
			dol_include_once('/legalnotice/class/legalnotice.class.php');

			if(empty($object->thidparty->id)) $object->fetch_thirdparty();
			if(empty($object->lines)) $object->fetch_lines();

			$TType = array();
			// On parcours toutes les lignes de la facture pour connaitre les types de produit présent
			foreach($object->lines as &$line) $TType[$line->product_type] = true;

			if (count($TType) == 2) $product_type = -1;
			else if(isset($TType[0])) $product_type = 0;
			else $product_type = 1;

			$legal = new LegalNotice($this->db);
			$TLegalNotice = $legal->fetchAll();

			foreach($TLegalNotice as &$legalNotice)
			{
				if ($object->thirdparty->tva_assuj != $legalNotice->is_assuj_tva && $legalNotice->is_assuj_tva != -1) continue;
				if (!in_array($object->thirdparty->country_id, $legalNotice->fk_country) && !in_array(-1, $legalNotice->fk_country)) continue;
				if (!in_array($object->thirdparty->typent_id, $legalNotice->fk_typent) && !in_array(-1, $legalNotice->fk_typent)) continue;
				// -2 = Produit OU Service, donc on considère que c'est OK dans tout les cas et qu'il ne faut pas faire un "continue"
				if ($product_type != $legalNotice->product_type && $legalNotice->product_type != -2) continue;

				if(! empty($conf->global->INVOICE_FREE_TEXT)) $conf->global->INVOICE_FREE_TEXT .= "\n<br />";
				$conf->global->INVOICE_FREE_TEXT .= $legalNotice->mention;
				break;	// On s'arrête à la première mention légale qui réunit toutes les conditions
			}
		}

		return 0;
	}
}

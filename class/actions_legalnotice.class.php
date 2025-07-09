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
require_once __DIR__ . '/../backport/v19/core/class/commonhookactions.class.php';

class ActionsLegalNotice extends legalnotice\RetroCompatCommonHookActions
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


		dol_include_once('/legalnotice/class/legalnotice.class.php');

		$TContext = explode(':', $parameters['context']);

		if ($object->element === 'facture')
		{
			if (!defined('INC_FROM_DOLIBARR')) define('INC_FROM_DOLIBARR', 1);
			dol_include_once('/legalnotice/config.php');
			if(empty($object->thidparty->id)) $object->fetch_thirdparty();
			if(empty($object->lines)) $object->fetch_lines();

			$TType = array();
			// On parcours toutes les lignes de la facture pour connaitre les types de produit présent
			foreach($object->lines as &$line) $TType[$line->product_type] = true;

			//  Je change la valeur de -1 à 2 car les valeurs ont été
			// modifiées dans le tableau des valeurs des types de produits
			if (count($TType) == 2) $product_type = 2;
			else if(isset($TType[0])) $product_type = 0;
			else $product_type = 1;

			$legal = new LegalNotice($this->db);
			$TLegalNotice = $legal->fetchAll();

			$TCountryUE = $this->searchCountryEu();
			$TCountryOutUE = $this->searchCountryOutEU();

			if(!isset($conf->global->INVOICE_FREE_TEXT)) {
				$conf->global->INVOICE_FREE_TEXT = '';
			}

			foreach($TLegalNotice as &$legalNotice)
			{
				if ($object->thirdparty->tva_assuj !=
                    $legalNotice->is_assuj_tva && $legalNotice->is_assuj_tva != 2) continue;

				// Si le pays n'est pas dans le tableau des pays de l'UE
				if (!in_array($object->thirdparty->country_id, $TCountryUE) &&
					// qu'il n'est pas selectionné dans la mention légale
					!in_array($object->thirdparty->country_id, $legalNotice->fk_country) &&
					// que le tableau parcouru n'est pas celui de TOUS LES PAYS
					!in_array(-1, $legalNotice->fk_country) &&
					// qu'il n'est pas le tableau des pays hors UE.
					!in_array(-3, $legalNotice->fk_country))
					// alors pas de traitement
					continue;

				// Si le pays n'est pas dans le tableau des pays hors de l'UE
				if (!in_array($object->thirdparty->country_id, $TCountryOutUE) &&
					// qu'il n'est pas selectionné dans la mention légale
					!in_array($object->thirdparty->country_id, $legalNotice->fk_country) &&
					// que le tableau parcouru n'est pas celui de TOUS LES PAYS
					!in_array(-1, $legalNotice->fk_country) &&
					// qu'il n'est pas le tableau des pays de l'UE.
					!in_array(-2, $legalNotice->fk_country))
					// alors pas de traitement
					continue;

				if (!in_array($object->thirdparty->typent_id, $legalNotice->fk_typent) && !in_array(-1, $legalNotice->fk_typent)) continue;
				// 3 = Produit OU Service, donc on considère que c'est OK
                // dans tous les cas et qu'il ne faut pas faire un "continue"
				if ($product_type != $legalNotice->product_type &&
                    $legalNotice->product_type != 3) continue;


				if (strpos(getDolGlobalString('INVOICE_FREE_TEXT'), $legalNotice->mention) === false) {
					if (getDolGlobalString('INVOICE_FREE_TEXT')) {
						$conf->global->INVOICE_FREE_TEXT .= "\n<br />";
					}
					$conf->global->INVOICE_FREE_TEXT .= $legalNotice->mention;
				}

				if(getDolGlobalInt('LEGALNOTICE_DO_NOT_CONCAT')) {
					break; // On s'arrête à la première mention légale qui réunit toutes les conditions
				}
			}
		}
		elseif($object->element === 'propal' && getDolGlobalString('LEGALNOTICE_MULTI_NOTICE_PROPAL') && !empty($object->array_options['options_legalnotice_selected_notice'])) {
		    $TLegalId = array($object->array_options['options_legalnotice_selected_notice']);
            if(strpos($object->array_options['options_legalnotice_selected_notice'],',') !== false) $TLegalId = explode(',',$object->array_options['options_legalnotice_selected_notice']);
			if (!isset($conf->global->PROPOSAL_FREE_TEXT)) {
				$conf->global->PROPOSAL_FREE_TEXT = '';
			}
            if(!empty($TLegalId)) {
                foreach($TLegalId as $fk_notice) {
                    $legal = new LegalNotice($this->db);
                    $legal->fetch($fk_notice);

					if (strpos(getDolGlobalString('PROPOSAL_FREE_TEXT'), $legal->mention) === false) {
						if (getDolGlobalString('PROPOSAL_FREE_TEXT')) {
							$conf->global->PROPOSAL_FREE_TEXT .= "\n<br />";
						}
						$conf->global->PROPOSAL_FREE_TEXT .= $legal->mention;
					}

					if(getDolGlobalInt('LEGALNOTICE_DO_NOT_CONCAT')) {
						break; // On s'arrête à la première mention légale qui réunit toutes les conditions
					}
                }
            }


        }

		return 0;
	}

	/**
	 *
	 * Give array of all country in EU
	 *
	 * @return int      -1 on error
	 * @return array    of country if ok
	 */
	public function searchCountryEu(){

		$sql = " SELECT rowid ";
		$sql .= " FROM ". MAIN_DB_PREFIX ."c_country ";
		$sql .= " WHERE eec = 1";

		$resql = $this->db->query($sql);

		if ($resql) {
			$resultArray = array();

			while ($row = $this->db->fetch_object($resql)) {
				$resultArray[] = $row->rowid;
			}
			return $resultArray;
		} else {
			return -1;
		}

	}

	/**
	 *
	 * Give array of all country out in EU
	 *
	 * @return int      -1 on error
	 * @return array    of country if ok
	 */
	public function searchCountryOutEU(){

		$sql = " SELECT rowid ";
		$sql .= " FROM ". MAIN_DB_PREFIX ."c_country ";
		$sql .= " WHERE eec = 0";

		$resql = $this->db->query($sql);

		if ($resql) {
			$resultArray = array();

			while ($row = $this->db->fetch_object($resql)) {
				$resultArray[] = $row->rowid;
			}
			return $resultArray;
		} else {
			return -1;
		}

	}
}

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

if (!class_exists('TObjetStd')) {
	/**
	 * Needed if $form->showLinkedObjectBlock() is call
	 */
	if (!defined('INC_FROM_DOLIBARR')) define('INC_FROM_DOLIBARR', true);
	require_once dirname(__FILE__).'/../config.php';
}

/**
 * Class LegalNotice
 *
 * Manage legal notices (custom module).
 */
class LegalNotice extends SeedObject
{
	public $table_element = 'legalnotice';

	public $element = 'legalnotice';
	public $mention = '';
	public $fk_country = array();
	public $product_type = 0;
	public $fk_typent;
	public $is_assuj_tva;
	public $rang;
	public $entity;
	public $errors = array();

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $conf;

		$this->db = $db;

		$this->fields=array(
				'entity'=>array('type'=>'integer','index'=>true)
				,'fk_country'=>array('type'=>'array') // peut contenir plusieurs fk ou '-1' pour tous
				,'product_type'=>array('type'=>'integer') // 0 => produit; 1
			// => service; 2 => produit ET service; 3 produit OU service
				,'fk_typent'=>array('type'=>'typent') // peut contenir plusieurs fk ou '-1' pour tous
				,'is_assuj_tva'=>array('type'=>'integer') // 0 = non, 1 =
			// oui, 2 = les 2
				,'mention'=>array('type'=>'text') // date, integer, string, float, array, text
				,'rang'=>array('type'=>'integer')
		);

		$this->init();

		$this->entity = $conf->entity;
	}

	/**
	 * Fetch a legal notice
	 *
	 * @param int         $id        Rowid of legal notice
	 * @param bool        $loadChild Load child objects
	 * @param string|null $ref       Optional reference
	 * @return int                   <0 if KO, >0 if OK, 0 if not found
	 */
	public function fetch($id, $loadChild = true, $ref = null)
	{
		$res = parent::fetch($id, $loadChild, $ref);

		if (empty($this->fk_country)) $this->fk_country = array();
		elseif (!is_array($this->fk_country)) $this->fk_country = explode(',', $this->fk_country);
		else $this->fk_country = explode(',', $this->fk_country[0]);

		if (empty($this->fk_typent)) $this->fk_typent = array();
		else $this->fk_typent = explode(',', $this->fk_typent);

		return $res;
	}

	/**
	 * Fetch all legal notices for current entity
	 *
	 * @param int   $limit     Max number of rows (0 = no limit)
	 * @param bool  $loadChild Load child objects
	 * @param array $TFilter   Optional filters (unused)
	 * @return LegalNotice[]   Array of LegalNotice objects
	 */
	public function fetchAll($limit = 0, $loadChild = true, $TFilter = array())
	{
		$TLegalNotice = array();
		$sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'legalnotice';
		$sql.= ' WHERE entity IN (0, '.getEntity('legalnotice').')';
		$sql.= ' ORDER BY rang';

		$resql = $this->db->query($sql);
		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
				$o = new LegalNotice($this->db);
				$res = $o->fetch($obj->rowid);
				if ($res > 0) $TLegalNotice[] = $o;
				else $this->errors[] = $o->db->lasterror;
			}
		} else {
			$this->errors[] = $this->db->lasterror;
		}

		return $TLegalNotice;
	}
}

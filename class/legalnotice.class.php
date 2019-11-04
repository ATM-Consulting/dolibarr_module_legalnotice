<?php

if (!class_exists('TObjetStd'))
{
	/**
	 * Needed if $form->showLinkedObjectBlock() is call
	 */
	define('INC_FROM_DOLIBARR', true);
	require_once dirname(__FILE__).'/../config.php';
}


class LegalNotice extends SeedObject
{
	public $table_element = 'legalnotice';

	public $element = 'legalnotice';

	public function __construct($db)
	{
		global $conf;

		$this->db = $db;

		$this->fields=array(
				'entity'=>array('type'=>'integer','index'=>true)
				,'fk_country'=>array('type'=>'array') // peut contenir plusieurs fk ou '-1' pour tous
				,'product_type'=>array('type'=>'integer') // 0 => produit; 1 => service; -1 => produit ET service; -2 produit OU service
				,'fk_typent'=>array('type'=>'typent') // peut contenir plusieurs fk ou '-1' pour tous
				,'is_assuj_tva'=>array('type'=>'integer') // 0 = non, 1 = oui, -1 = les 2
				,'mention'=>array('type'=>'text') // date, integer, string, float, array, text
				,'rang'=>array('type'=>'integer')
		);

		$this->init();

		$this->entity = $conf->entity;
	}

	public function fetch($id, $loadChild = true, $ref = NULL)
	{
		$res = parent::fetch($id, $loadChild, $ref);

		if (empty($this->fk_country)) $this->fk_country = array();
		elseif(!is_array($this->fk_country)) $this->fk_country = explode(',', $this->fk_country);
		else $this->fk_country = explode(',', $this->fk_country[0]);

		if (empty($this->fk_typent)) $this->fk_typent = array();
		else $this->fk_typent = explode(',', $this->fk_typent);

		return $res;
	}

	public function fetchAll()
	{
		$TLegalNotice = array();
		$sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'legalnotice';
		$sql.= ' WHERE entity IN (0, '.getEntity('legalnotice').')';
		$sql.= ' ORDER BY rang';

		$resql = $this->db->query($sql);
		if ($resql)
		{
			while ($obj = $this->db->fetch_object($resql))
			{
				$o = new LegalNotice($this->db);
				$res = $o->fetch($obj->rowid);
				if ($res > 0) $TLegalNotice[] = $o;
				else $this->errors[] = $o->db->lasterror;
			}
		}
		else
		{
			$this->errors[] = $this->db->lasterror;
		}

		return $TLegalNotice;
	}
}

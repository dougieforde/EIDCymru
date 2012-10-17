<?php
class ScotEID_Species extends ScotEID_Model
{
	const LEN_NAME = 15;
	
	const SPECIES_PIGS  = 3;
	const SPECIES_SHEEP = 4;
	
	private $id;
	private $name;
	
	public static function get_table_name() { return "Species_codes"; }
	public function get_primary_key() { 
		return array(
			'Species_code'	=> quote_int($this->id)
		);
	}
	protected static function attribute_map() {
		return array(
			'Species_code' => 'id',
			'Species_type' => 'name'
		);
	}
	
	public function get_id() { return $this->id; }
	public function get_name() { return $this->name; }
	
	public function set_id($id) { $this->id = (int) $id; }
	public function set_description($name) { $this->name = trim(substr($name, 0, self::LEN_NAME)); }
	
	private static $species_codes = array();
	
	public static function get($id) {
		$qid = quote_int($id);
		if(!isset(static::$species_code[$qid])) {
			$t = static::find(array(
				'conditions' => "#id# = $qid",
				'limit'			 => 1
			));
			if(count($t) == 1) static::$species_codes[$qid] = $t[0];
		}
		return static::$species_codes[$qid];
	}
}
?>
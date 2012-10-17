<?php
class ScotEID_MovementType extends ScotEID_Model
{
	const LEN_DESCRIPTION = 50;
	const LEN_SHORT_NAME  = 15;
	
	const MOVEMENT_TYPE_ISSUED            = 0;
	const MOVEMENT_TYPE_UNASSIGNED        = 1;
	const MOVEMENT_TYPE_ON                = 2;
	const MOVEMENT_TYPE_OFF               = 3;
	const MOVEMENT_TYPE_MART              = 4;
	const MOVEMENT_TYPE_DEATH             = 5;
	const MOVEMENT_TYPE_TAGGED            = 6;
	const MOVEMENT_TYPE_SURPLUS           = 7;
	const MOVEMENT_TYPE_ABATTOIR          = 8;
	const MOVEMENT_TYPE_DROPOUT           = 9;
	const MOVEMENT_TYPE_MANAGMEMENT       = 10;
	const MOVEMENT_TYPE_INSERT_BREAK      = 11;
	const MOVEMENT_TYPE_TESTING           = 12;
	const MOVEMENT_TYPE_CCP_MISSED        = 13;
	const MOVEMENT_TYPE_UNREADABLE        = 14;
	const MOVEMENT_TYPE_HIDDEN            = 15;
	const MOVEMENT_TYPE_INVENTORY         = 16;
	
	private $id            = null;
	private $description   = null;
	private $short_name    = null;
	
	public static function get_table_name() { return "tblmovementtypes"; }
	public function get_primary_key() { 
		return array(
			'MovementType'	=> quote_int($this->CPH)
		);
	}
	protected static function attribute_map() {
		return array(
			'MovementType' => 'id',
			'Description'  => 'description',
			'ShortName'    => 'short_name'
		);
	}
	
	public function get_id() { return $this->id; }
	public function get_short_name() { return $this->short_name; }
	public function get_description() { return $this->description; }
	
	public function set_id($id) { $this->id = (int) $id; }
	public function set_description($description) { $this->description = trim(substr($description, 0, self::LEN_DESCRIPTION)); 
   }
	public function set_short_name($short_name) { $this->short_name = trim(substr($short_name, 0, self::LEN_SHORT_NAME)); 
	}
	
	public function to_array() {
	  return array(
	    'id'          => $this->get_id(),
	    'description' => $this->get_description(),
	    'short_name'  => $this->get_short_name()
	  );
	}
	
	private static $movement_types = array();
	
	public static function get($id) {
		$qid = quote_int($id);
		if(!isset(static::$movement_types[$qid])) {
			$t = static::find(array(
				'conditions' => "#id# = $qid",
				'limit'			 => 1
			));
			if(count($t) == 1) static::$movement_types[$qid] = $t[0];
		}
		return static::$movement_types[$qid];
	}
}
?>
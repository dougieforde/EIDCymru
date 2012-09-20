<?php
class ScotEID_TagReading extends ScotEID_Model
{
  const INCOMPLETE_TABLE_NAME = "tempitemread";
  const COMPLETED_TABLE_NAME  = "tblsheepitemread";
  
  const TAGREADING_TYPE_EID       = 0;
  const TAGREADING_TYPE_FLOCK_TAG = 1;
  const TAGREADING_TYPE_MANUAL    = 2;
  
  private $lot                = null;
  private $tag_reading_type   = self::TAGREADING_TYPE_EID;
  private $tag_id             = null;
  private $timestamp          = null;
  private $animal             = null;
  private $country_code       = null;
  private $tag_code           = null;
  private $prev_id            = null;
  private $prev_country_code  = null;
  
  protected function property_map() { 
    return array(
      'Type'                => 'type_string',
      'Hex'                 => 'tag_id',
      'ISO24631'            => 'iso24631_tag_id',
      'Timestamp'           => 'timestamp',
      'Animal'              => 'animal'
    ); 
  }
  
	public static function get_lot_table_name($lot) {
	  if($lot instanceof ScotEID_CompletedLot) {
      return self::COMPLETED_TABLE_NAME;
    } else {
      return self::INCOMPLETE_TABLE_NAME;
    }	
	}

	protected function _table_name() {
		return self::get_lot_table_name($this->lot);
	}

  public static function get_table_name() {
    //return ScotEID_TagReading::_table_name($this->get_lot());
  	return null;
	}
  
  public function get_primary_key() {    
    if($this->get_lot() instanceof ScotEID_CompletedLot) {
      return array(
        'Lot_No'        => quote_str($this->get_lot()->get_lot_number_was()),
        'LotDate'       => quote_date($this->get_lot()->get_lot_date_was(), false),
        'EIDHexNumber'  => quote_str($this->get_tag_id()),
        'Species'       => quote_int($this->get_lot()->get_species_id_was()),
        'MovementType'    => quote_int($this->get_lot()->get_movement_type_id_was())
      );
    } else {
      return array(
        'Lot_No'        => quote_str($this->get_lot()->get_lot_number_was()),
        'LotDate'       => quote_date($this->get_lot()->get_lot_date_was(), false),
        'EIDHexNumber'  => quote_str($this->get_tag_id()),
        'uid'           => quote_int($this->get_lot()->get_uid_was()),
        'Filename'      => quote_str($this->get_lot()->get_filename_was())
      );
    }
  }
  
  public function quoted_attributes() {
    $k = array();
    if($this->get_lot() instanceof ScotEID_CompletedLot) {
      $k = array(
        'Lot_No'            => quote_str($this->get_lot()->get_lot_number()),
        'LotDate'           => quote_date($this->get_lot()->get_lot_date(), false),
        'EIDHexNumber'      => quote_str($this->get_tag_id()),
        'Species'           => quote_int($this->get_lot()->get_species_id()),
        'MovementType'      => quote_int($this->get_lot()->get_movement_type_id()),
        'prev_ID'           => quote_int($this->get_prev_id()),
        'prev_country_code' => quote_int($this->get_prev_country_code())        
      );
    } else {
      $k = array(
        'Lot_No'        => quote_str($this->get_lot()->get_lot_number()),
        'LotDate'       => quote_date($this->get_lot()->get_lot_date(), false),
        'EIDHexNumber'  => quote_str($this->get_tag_id()),
        'uid'           => quote_int($this->get_lot()->get_uid()),
        'Filename'      => quote_str($this->get_lot()->get_filename())
      );
    }
    
    return array_merge(
      $k,
      array(
        'ReadLocationCPH' => quote_str($this->get_lot()->get_read_location()),
        'EIDHexNumber'    => quote_str($this->get_dummy_tag_id()),
        'AnimalEID'       => $this->get_dummy_tag_id()->get_animal_id(),
        'MovementType'    => quote_int($this->get_lot()->get_movement_type_id()),
        'Timestamp'       => quote_datetime($this->get_timestamp()),
        'Country_code'    => quote_int($this->get_dummy_tag_id()->get_country_code()),
        'Tag_count'       => 1,
        'Flock_tag'       => quote_int($this->get_tag_reading_type()) // FIXME - this db column needs to be renamed
      )      
    );
  }
  
  protected function perform_validate() {
    if($this->get_lot() === null) {
      $this->add_error('lot', 'cannot be blank');
    }
    
    if($this->get_tag_reading_type() == self::TAGREADING_TYPE_EID) {
      if($this->get_tag_id() === null) {
        $this->add_error('tag_id', 'cannot be blank');
      } elseif(!$this->get_tag_id()->is_valid()) {
        $this->add_error('tag_id', 'is not valid');
      }
    }

    //if($this->get_timestamp() === null) {
    //  $this->add_error('timestamp', 'cannot be blank');
    //}
    
    // TODO: validate lot timestamp is on lot date?
  }
  
  //
  // Finders
  
  public static function find_by_lot($lot) {
    $pk  = $lot->get_primary_key();
    $t   = self::get_lot_table_name($lot);
    $p   = array();
    foreach($pk as $field => $value) {
      $p[] = "t.$field = $value";
    }
    $p[] = "Flock_tag <> 1";
    $p = implode(' AND ', $p);
    
    $sql = "SELECT 
              t.*, 
              r.defra_tag_code AS defra_tag_code,
              r.lastmovetype AS last_movement_type, 
              r.Lastmovedate AS last_movement_date, 
              r.lastmovecph AS last_destination_location, 
              l.ReadLocationCPH AS last_read_location, 
              r.uknumber AS animal_number, 
              r.Issue_Date AS issue_date" .
           " FROM $t t LEFT JOIN tblindividualsheepeidregister r " .
           " ON t.animaleid = r.animaleid " .
           " LEFT JOIN tblsslots l ON r.lastmoveref = l.sams_movement_reference WHERE $p" .
           " GROUP BY t.AnimalEID " .
					 " ORDER BY t.Timestamp ASC, t.AnimalEID ASC"; 

    return dbw_stack_objects($sql, 'ScotEID_TagReading', array('method' => 'extract_all_from'));
  }
  
  public static function find_by_animal_eid($animal_eid) {
    $t = static::COMPLETED_TABLE_NAME;
    
    $p = "t.AnimalEID = " . quote_int($animal_eid);
    
    $sql = "SELECT
              t.* " .
            " FROM $t t WHERE $p " .
            " ORDER BY t.Timestamp ASC, t.AnimalEID ASC";
    
    return dbw_stack_objects($sql, 'ScotEID_TagReading', array('method' => 'extract_all_from'));
  }
  
  public function extract_private_from($d) {
    $req = ScotEID_AbstractRequest::get_current_request();
    
    if(!$req || $req->is_schema_version_gte('1.2')) {
      $a = new ScotEID_Animal();
      $a->extract_all_from($d);
      if(!$a->is_empty()) {
        $this->set_animal($a);
      } else {
        $this->set_animal(null);
      }
    }   
    
    $this->extract_attributes(array('tag_reading_type', 'country_code', 'tag_code', 'prev_id', 'prev_country_code'), $d); 
  }
  
  public function extract_public_from($d) {
    $this->extract_attributes(array('tag_id', 'timestamp'), $d);
  }
  
  public static function attribute_map() {
    return array(
      'EIDHexNumber'      => 'tag_id',
      'Timestamp'         => 'timestamp',
      'Country_code'      => 'country_code',
      'defra_tag_code'    => 'tag_code',
      'Flock_tag'         => 'tag_reading_type',
      'prev_ID'           => 'prev_id',
      'prev_country_code' => 'prev_country_code'
    );
  }
  
  //
  // Property setters and getters
  
  public function set_lot($lot) { $this->lot = $lot; }
  public function set_tag_reading_type($t) { $this->tag_reading_type = $t ? (int) $t : 0; }
  public function set_tag_id($tag_id) { 
    if(!empty($tag_id) && is_string($tag_id)) { $tag_id = new ScotEID_TagID($tag_id); }
    $this->tag_id = empty($tag_id) ? null : $tag_id;
  }
  public function set_iso24631_tag_id($tag_id) {
    if(!empty($tag_id) && is_string($tag_id)) { $tag_id = new ScotEID_ISO24631TagID($tag_id); }
    $this->tag_id = empty($tag_id) ? null : $tag_id;
  }
  public function set_timestamp($timestamp) { 
    $this->timestamp = ScotEID_Utils::sanitize_datetime($timestamp);
  }
  public function set_animal($animal) { $this->animal = $animal; }
  public function set_country_code($country_code) { 
    $this->country_code = $country_code == null ? null : (int) $country_code; 
  }
  public function set_prev_id($prev_id) { 
    $this->prev_id = $prev_id == null ? null : (int) $prev_id; 
  }  
  public function set_prev_country_code($prev_country_code) { 
    $this->prev_country_code = $prev_country_code == null ? null : (int) $prev_country_code; 
  }
  public function set_tag_code($tag_code) {
    $this->tag_code = $tag_code;
  }
  
  public function get_lot() { return $this->lot; }
  public function get_tag_reading_type() { return $this->tag_reading_type; }
  public function get_tag_id() { 
    if($this->get_tag_reading_type() == self::TAGREADING_TYPE_EID) {    
      return $this->tag_id; 
    } else {
      return null;
    }
  }
  private function get_dummy_tag_id() {
    if($this->get_tag_reading_type() == self::TAGREADING_TYPE_EID) {    
      return $this->tag_id; 
    } else {
      return new ScotEID_ISO24631TagID(sprintf("1 0 00 00 0 999 %012d", $this->timestamp));
    }
  }
  
  public function get_iso24631_tag_id() { return null; }
  public function get_timestamp() { return $this->timestamp; }  
  public function get_animal() { return $this->animal; }
  public function get_country_code() { return $this->country_code; }
  public function get_prev_country_code() { return $this->prev_country_code; }
  public function get_prev_id() { return $this->prev_id; }    
  public function get_flock_number() {
    if($this->get_tag_id()->get_country_code() == 826) {
      return substr($this->get_tag_id()->get_animal_id(), 0, 6);
    } else {
      return null;
    }
  }
  
  public function get_type_string() {
    // only show tag type to schema versions >= 1.2.5
    $req = ScotEID_AbstractRequest::get_current_request();
    if($req && $req->is_schema_version_gte('1.2.5')) {    
      switch($this->tag_reading_type) {
        case self::TAGREADING_TYPE_MANUAL:
          return "manual";
        default:
          return "eid";
      }
    }
    return null;
  }

  public function get_tag_code() { return $this->tag_code; }
    
  public function get_tag_code_description() {
    static $tag_code_descriptions;
    $tag_code_descriptions = ScotEID_TagReading::get_tag_code_descriptions();
    return isset($tag_code_descriptions[$this->tag_code]) ? $tag_code_descriptions[$this->tag_code] : "";
  }
    
  public static function get_tag_code_descriptions() {
    $r = array();
    
    $sql = "SELECT defra_tag_code, tag_description FROM eid_tags";
    $result = dbw_query($sql);
    while(($row = dbw_row($result)) != null) {
     $r[$row['defra_tag_code']] = $row['tag_description'];
    }
    
    return $r;
  }
  
  public function set_type_string($type_string) {
    if($type_string == 'manual') {
      $this->tag_reading_type = self::TAGREADING_TYPE_MANUAL;
    } else {
      $this->tag_reading_type = self::TAGREADING_TYPE_EID;
    }
  }
  
  public function is_eid() {
    return $this->tag_reading_type == null || $this->tag_reading_type == 'eid';
  }
}
?>
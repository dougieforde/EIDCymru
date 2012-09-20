<?php
class ScotEID_FlockTag extends ScotEID_Model
{
  const FLOCK_NUMBER_REGEX = '/^[0-9]{6,6}$/';

  private $lot          = null;
  private $flock_number = null;
  private $tag_count    = null;
  private $prev_id      = null;  
  private $prev_country_code  = null;
  
  
  // Dummy hex number to satisfy primary key
  private $eid_hex_number = null;
  
  protected function property_map() { 
    return array(
      'FlockNumber'         => 'flock_number',
      'TagCount'            => 'tag_count'    
    ); 
  }

	protected function _table_name() {
		return self::get_lot_table_name($this->lot);
	}
  
  private static function get_lot_table_name($lot) {
    if($lot instanceof ScotEID_CompletedLot) {
      return 'tblsheepitemread';
    } else {
      return 'tempitemread';
    }
  }
  
  public static function get_table_name() {
		return null;
  }
  
  public static function attribute_map() {
    return array(
      'AnimalEID' => 'flock_number',
      'Tag_count' => 'tag_count',
      'prev_ID'   => 'prev_id'
    );
  }
  
  public function get_primary_key() {
    $common = array(
      'Lot_No'        => quote_str($this->get_lot()->get_lot_number_was()),
      'LotDate'       => quote_date($this->get_lot()->get_lot_date_was(), false),
      'EIDHexNumber'  => quote_str($this->eid_hex_number)
    );
    
    if($this->get_lot() instanceof ScotEID_CompletedLot) {
      return $common;
    } else {
      return array_merge($common, array(
        'uid'           => quote_int($this->get_lot()->get_uid_was()),
        'Filename'      => quote_str($this->get_lot()->get_filename_was())        
      ));
    }
  }
  
  public function quoted_attributes() {
    $k = array(
      'Lot_No'        => quote_str($this->get_lot()->get_lot_number()),
      'LotDate'       => quote_date($this->get_lot()->get_lot_date(), false),
      'EIDHexNumber'  => quote_str($this->eid_hex_number)
    );
    
    if($this->get_lot() instanceof ScotEID_CompletedLot) {
      $k = array_merge($k, array(
        'prev_ID'       => quote_int($this->get_prev_id()) 
      ));
    } else {
      $k = array_merge($k, array(
        'uid'           => quote_int($this->get_lot()->get_uid()),
        'Filename'      => quote_str($this->get_lot()->get_filename())
      ));
    }
    
    return array_merge($k,
    array(
      'ReadLocationCPH'   => quote_str($this->get_lot()->get_read_location()),
      'AnimalEID'         => quote_int($this->get_flock_number()),
      'Country_code'      => quote_int(826),
      'Tag_count'         => quote_int($this->get_tag_count()),
      'MovementType'      => quote_int($this->get_lot()->get_movement_type_id()),
      'Flock_tag'         => quote_int(1),
			'Species'    				=> quote_int($this->get_lot()->get_species_id())
    ));
  }
  
  // Finders
  public static function find_by_lot($lot) {
    $pk  = $lot->get_primary_key();
    $t   = self::get_lot_table_name($lot);
    $p   = array();
    foreach($pk as $field => $value) {
      $p[] = "t.$field = $value";
    }
    $p[] = "Flock_tag = 1";
    $p = implode(' AND ', $p);
    
    $sql = "SELECT t.* FROM $t t WHERE $p";
    return dbw_stack_objects($sql, 'ScotEID_FlockTag', array('method' => 'extract_all_from'));
  }
  
  public function extract_private_from($a) {
    $this->extract_attributes(array(), $a);
  }
  
  public function extract_public_from($a) {
    $this->extract_attributes(array('flock_number', 'tag_count', 'prev_id'), $a);
  }
  
  public function set_lot($lot) {
    $this->lot = $lot;
  }
  
  public function set_flock_number($flock_number, $retag_counter = 0) {
    if(!empty($flock_number)) {
      $flock_number = str_replace(" ", "", $flock_number);
    } else {
      $flock_number = "";
    }

    // cludge for storing the same flock number multiple times, which is needed for retagging
    $user_field = 0;
    $retag_counter = (int)$retag_counter;
    if($retag_counter >7) {    
      $user_field = $retag_counter -7;
      $retag_counter = 7;
    }
    
		$flock_number = (int)$flock_number;
		// FIXME
    //if(preg_match(self::FLOCK_NUMBER_REGEX, $flock_number)) {
      $this->flock_number = $flock_number;
      
      // HACK: make up a dummy hex number with country code 826 and animal number 0
      $temp_id = new ScotEID_ISO24631TagID(sprintf("1 %01d %02d 00 0 826 0%06d 00000", $retag_counter, $user_field, $flock_number));
      $this->eid_hex_number = $temp_id->to_hex_string();

    //} else {
    //  $this->flock_number = null;
    //}
  }
  
  public function set_tag_count($tag_count) {
    $this->tag_count = (int) $tag_count;
  }
  public function set_prev_id($prev_id) { 
    $this->prev_id = $prev_id == null ? null : (int) $prev_id; 
  }    
  
  public function get_lot() { return $this->lot; }
  public function get_flock_number() { return $this->flock_number; }
  public function get_tag_count() { return $this->tag_count; }
  public function get_prev_id() { return $this->prev_id; }
  
  protected function perform_validate() {
    if($this->get_lot() === null) {
      $this->add_error('lot', 'cannot be blank');
    }
    
    if(empty($this->flock_number)) {
      $this->add_error('flock_number', 'cannot be blank');
    }
    
    if(empty($this->tag_count)) {
      $this->add_error('tag_count', 'cannot be blank');
    } else if($this->get_tag_count() <= 0) {
      $this->add_error('tag_count', 'must be a positive integer');
    }
  }
}
?>
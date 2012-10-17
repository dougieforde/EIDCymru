<?php
class ScotEID_Animal extends ScotEID_Model
{
  private $etas_verified             = false;
  private $animal_number             = null;
  private $last_movement_type        = null;
  private $last_movement_date        = null;
  private $last_read_location        = null;
  private $last_destination_location = null;
  private $issue_date                = null;
  
  protected static function public_attributes() { return array('etas_verified', 'animal_number', 'last_destination_location', 'issue_date'); }
  
  public static function get_table_name() {
    return "tblindividualsheepeidregister";
  }

  public function get_primary_key() {
    // FIXME - not really used because we're not saving or loading these
    // objects
    return array();
  }  
  
  public static function attribute_map() {
    return array(
      'UKNumber'     => 'animal_number',
      'lastmovecph'  => 'last_destination_location',
      'lastmovetype' => 'last_movement_type',
      'Lastmovedate' => 'last_movement_date',
      'lastreadcph'  => 'last_read_location'
      
    );
  }
  
	public function quote_attribute($attribute, $value) {
		switch($attribute) {
		  case 'last_movement_type':
		    return quote_int($value);
      default:
			  return quote_str($value);
		}
		return "";
	}

  protected function property_map() { 
    return array(
      'ETASVerified'            => 'etas_verified',
      'AnimalNumber'            => 'animal_number',
      'LastMovementDate'        => 'last_movement_date',
      'LastMovementType'        => 'last_movement_type',
      'LastReadLocation'        => 'last_read_location',
      'LastDestinationLocation' => 'last_destination_location'
    ); 
  }  
  
  public function set_issue_date($issue_date) {
    if($issue_date) {
      $this->issue_date = $issue_date;
      $this->set_etas_verified(true);
    } else {
      $this->issue_date = null;
      $this->set_etas_verified(false);
    }
  }
  
  public function set_etas_verified($verified) {
    $this->etas_verified = (bool)$verified;
  }
  
  public function set_animal_number($animal_number) {
    $this->animal_number = $animal_number;
  }
  
  public function set_last_movement_type($movement_type) {
    $this->last_movement_type = empty($movement_type) ? null : (int) $movement_type;
  }
  
  public function set_last_movement_date($movement_date) {
    $this->last_movement_date = ScotEID_Utils::sanitize_date($movement_date);
  }
  
  public function set_last_destination_location($location) { 
    $this->last_destination_location = ScotEID_Utils::sanitize_location($location);
  }
  
  public function set_last_read_location($location) { 
    $this->last_read_location = ScotEID_Utils::sanitize_location($location);
  }

  public function get_etas_verified() {
    return $this->etas_verified;
  }
  
  public function get_animal_number() {
    return $this->animal_number;
  }
  
  public function get_flock_number() {    
    if(strncmp($this->animal_number, "UK", 2) == 0) {
      return substr($this->animal_number, 3, 6);
    } else {
      return null;
    }
  }
  
  public function get_last_movement_type() {
    return $this->last_movement_type;
  }
  
  public function get_last_movement_date() {
    return $this->last_movement_date;
  }
  
  public function get_last_read_location() {
    return $this->last_read_location;
  }
  
  public function get_last_destination_location() {
    return $this->last_destination_location;
  }
  
  public function is_empty() {
    return empty($this->animal_number) &&
           (empty($this->last_destination_location) || !$this->last_destination_location->valid());
  }
  
  public function extract_public_from($d) {   
$this->extract_attributes(array('animal_number','last_movement_type','last_movement_date','last_destination_location','last_read_location','issue_date'), $d);
  }
}
?>
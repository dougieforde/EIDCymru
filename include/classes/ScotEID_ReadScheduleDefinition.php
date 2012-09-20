<?php
class ScotEID_ReadScheduleDefinition extends ScotEID_Model
{
  const TABLE_NAME = "tblreadscheduledefinitions";
  
  private $id            = null;
  private $read_location = null;
  private $schedule      = null;
  private $sheep         = false;
  private $cattle        = false;
  
  public function get_id() { return $this->id; }
  public function get_read_location() { return $this->read_location; }
  public function get_schedule() { return $this->schedule; }
  public function get_cattle() { return $this->cattle; }
  public function get_sheep() { return $this->sheep; }

  public function find_all() {
    return dbw_stack_objects("SELECT * FROM " . self::TABLE_NAME, "ScotEID_ReadScheduleDefinition", array('method' => 'extract_all_from'));  
  }

  public function set_id($id) {
    $this->id = (int) $id;
  }

  public function set_read_location($read_location) {
    $this->read_location = ScotEID_Utils::sanitize_location($read_location);
  }
  
  public function set_schedule($schedule) {
    $this->schedule = $schedule;
  }
  
  public function set_schedule_serialized($serialized) {
    $this->schedule = unserialize($serialized);
  }
  
  public function set_cattle($cattle) {
    $this->cattle = (bool) $cattle;
  }
  
  public function set_sheep($sheep) {
    $this->sheep = (bool) $sheep;
  }
  
  public static function get_table_name() {
    return static::TABLE_NAME;
  }  
  
  public function get_primary_key() {
    return array(
      'id' => quote_int($this->get_id())
    );
  }
  
  public function quoted_attributes() {
    return array(
      'read_location'         => quote_str($this->read_location),
      'schedule_serialized'   => quote_str($this->schedule ? serialize($this->schedule) : null),
      'cattle'                => quote_bool($this->cattle),
      'sheep'                 => quote_bool($this->sheep)
    );
  }
  
  public function extract_public_from($d) {    
    $this->extract_attributes(array('read_location','cattle','sheep'), $d);
  }
  
  public function extract_private_from($d) {
    $this->extract_attributes(array('id','generated','schedule_serialized'), $d);
  }
  
  protected function attribute_map() {
    return array(
      'id'                  => 'id',
      'read_location'       => 'read_location',
      'schedule_serialized' => 'schedule_serialized',
      'cattle'              => 'cattle',
      'sheep'               => 'sheep'
    );
  }
  
}
?>
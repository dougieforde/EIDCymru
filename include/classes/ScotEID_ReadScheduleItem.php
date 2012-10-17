<?php
class ScotEID_ReadScheduleItem extends ScotEID_Model
{
  private $id                 = null;
  private $read_location      = null;
  private $starts_at          = null;
  private $cattle             = false;
  private $sheep              = false;
  private $comments           = null;
  private $warning_count      = 0;
  private $generated          = false;
  
  const TABLE_NAME   = "tblreadscheduleitems";
  const LEN_COMMENTS = 250;
  
  public static function _table_name() {
    return self::TABLE_NAME;
  }
  
  public static function get_table_name() {
    return self::_table_name();
  }  
  
  public function get_primary_key() {
    return array(
      'id' => quote_int($this->get_id())
    );
  }
  
  public function get_id() { return $this->id; }
  public function get_read_location() { return $this->read_location; }
  public function get_starts_at() { return $this->starts_at; }
  public function get_cattle() { return $this->cattle; }
  public function get_sheep() { return $this->sheep; }
  public function get_comments() { return $this->comments; }
  public function get_warning_count() { return $this->warning_count; }
  public function get_generated() { return $this->generated; }
  
  protected function set_id($id) { $this->id = empty($id) ? null : (int)$id; }
  public function set_read_location($r) {
    $this->read_location = ScotEID_Utils::sanitize_location($r);
  }
  
  public function set_starts_at($starts_at) {
    $this->starts_at = ScotEID_Utils::sanitize_datetime($starts_at);
  }
  
  public function set_cattle($cattle) {
    $this->cattle = (bool) $cattle;
  }
  
  public function set_sheep($sheep) {
    $this->sheep = (bool) $sheep;
  }
  
  public function set_comments($comments) {
    $this->comments = empty($comments) ? null : trim(substr($comments, 0, self::LEN_COMMENTS));     
  }
  
  public function set_warning_count($warning_count) {
    $this->warning_count = (int) $warning_count;
  }
  
  public function set_generated($generated) {
    $this->generated = (bool) $generated;
  }
  
  public static function find_sheep_scheduled_by_date($date) {
    $qdate  = quote_date($date);
    $sql = "SELECT * FROM " . ScotEID_ReadScheduleItem::_table_name() . " WHERE sheep = 1 AND starts_at >= DATE($qdate) AND starts_at < DATE($qdate + INTERVAL 1 DAY);";
    return dbw_stack_objects($sql, 'ScotEID_ReadScheduleItem', array('method' => 'extract_all_from'));
  }
  
  public function quoted_attributes() {
    $r = array(
      'cattle'          => quote_bool($this->cattle),
      'sheep'           => quote_bool($this->sheep),
      'comments'        => quote_str($this->comments),
      'warning_count'   => quote_int($this->warning_count),
      'generated'       => quote_bool($this->generated)
    );
    
    if(!empty($this->read_location))  $r['read_location_cph'] = quote_str($this->get_read_location());
    if(!empty($this->starts_at))      $r['starts_at']         = quote_datetime($this->get_starts_at());
    
    return $r;
  }
  
  public function extract_public_from($d) {    
    $this->extract_attributes(array('read_location','starts_at','cattle','sheep','comments','warning_count'), $d);
  }
  
  public function extract_private_from($d) {
    $this->extract_attributes(array('id','generated'), $d);
  }
  
  protected function attribute_map() {
    return array(
      'id'                => 'id',
      'read_location_cph' => 'read_location',
      'starts_at'         => 'starts_at',
      'cattle'            => 'cattle',
      'sheep'             => 'sheep',
      'comments'          => 'comments',
      'warning_count'     => 'warning_count',
      'generated'         => 'generated'
    );
  }
  
}
?>
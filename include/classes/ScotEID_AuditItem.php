<?php
class ScotEID_AuditItem extends ScotEID_ExtendedModel
{
    const UNCOMPLETE_LOT_TYPE = "uncomplete_lot";
		const LOT_CORRECTION_TYPE = "correction";
    
    protected $id         = null;
    protected $uid        = null;
    protected $type       = null;
    protected $data       = null;
    protected $created_at = null;
    
    private $object = null;
    
    public static function get_table_name() {
        return "tblaudititems";
    }

    public function get_primary_key() {
        return array('id' => quote_int($this->get_id()));
    }

  	protected static function get_attribute_definitions() {
  	  return array(
  	    'id'                      => array('type' => 'serial'),
  	    'uid'                     => array('type' => 'integer'),
  	    'sams_movement_reference' => array('type' => 'integer'),
  	    'type'                    => array('type' => 'string'),
  	    'data'                    => array('type' => 'string'),
  	    'created_at'              => array('type' => 'datetime')
  	  );
  	}
  	
    public function before_save_on_create() {
      parent::before_save_on_create();
      if(empty($this->created_at)) {
        $this->created_at = time();
      }
    }
    
    public function after_save_on_create() {
      parent::after_save_on_create();
      $this->id = dbw_insert_id();
    }

    public function get_object() {
        if($this->object) {
            return $this->object;
        } else if($this->get_data()) {
            return unserialize($this->get_data());
        } else {
            return null;
        }
    }

    public function set_data($data) {
      $this->_set_attribute('data', $data);
      $this->object   = unserialize($data);
    }
    
    public function set_object($object) {
        $this->object = $object;
        $this->_set_attribute('data', serialize($object));
    }
}
?>
<?php
class ScotEID_BPEXMovementType extends ScotEID_ExtendedModel
{
  public static function get_table_name() {
		return "bpex_movement_types";
	}
	
	public function get_primary_key() {
		return array(
			'id' => quote_int($this->get_id())
		);
	}

	protected static function get_attribute_definitions() {
	  return array(
	    'id'            => array('type' => 'int'),
	    'name'          => array('type' => 'string'),
	    'description'   => array('type' => 'string'),
	    'visible'       => array('type' => 'boolean')
	  );
	} 
}
?>
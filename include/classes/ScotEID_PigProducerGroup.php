<?php
class ScotEID_PigProducerGroup extends ScotEID_ExtendedModel
{
  public static function get_table_name() {
		return "pig_producer_groups";
	}
	
	public function get_primary_key() {
		return array(
			'id'  => quote_int($this->get_id())
		);
	}
	
  protected static function get_attribute_definitions() {
    return array(
      'id'    => array('type' => 'serial'),
      'name'  => array('type' => 'string')
    );
  }
}
?>
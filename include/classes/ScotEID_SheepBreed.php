<?php
class ScotEID_SheepBreed extends ScotEID_ExtendedModel
{
  public static function get_table_name() {
		return "tblsheepbreeds";
	}
	
	public function get_primary_key() {
		return array(
			'S_Breed_Code' => quote_str($this->get_breed_code())
		);
	}

	protected static function get_attribute_definitions() {
	  return array(
      'breed_code' => array('type' => 'string', 'field' => 'S_Breed_Code'),
      'breed'      => array('type' => 'string', 'field' => 'S_Breed'),
      'list_order' => array('type' => 'int')     
	  );
	}
}
?>
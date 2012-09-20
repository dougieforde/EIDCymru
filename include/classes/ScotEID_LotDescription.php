<?php
class ScotEID_LotDescription extends ScotEID_ExtendedModel
{
  private $description_id;
  private $description;
  private $criteria;
  
  const DESC_UNSPECIFIED = 0;
  const DESC_PORKERS     = 1;
  const DESC_CUTTERS     = 2;
  const DESC_BACONERS    = 3;
  const DESC_BACKFATTERS = 4;
  const DESC_BREEDING_PIGS   = 5;
  const DESC_SOWS            = 6;
  const DESC_BOARS           = 7;
  const DESC_SOWS_AND_BOARS  = 8;
  const DESC_WEANERS         = 9;
  const DESC_BABIES          = 10;
  const DESC_NURSERY_STORES  = 11;
  const DESC_FINISHER_STORES = 12;
  const DESC_FAT             = 13;
  const DESC_FOSTER_HUTS     = 14;
  const DESC_MIXED           = 15;
  const DESC_GILTS           = 16;
  const DESC_WILD_BOAR       = 17;
  const DESC_CULLS           = 18;
  const DESC_BREEDING_STORES = 19;
  const DESC_IN_PIG_SOWS     = 20;
  
  public static function get_table_name() {
		return "lot_descriptions";
	}
	
	public function get_primary_key() {
		return array(
			'description_id' => quote_int($this->get_lot_description())
		);
	}
	
  protected static function get_attribute_definitions() {
    return array(
      'description_id' => array('type' => 'serial'),
      'description'    => array('type' => 'string'),
      'criteria'       => array('type' => 'string')
    );
  }
  
  public static function get($id) {
    $t = static::find(array(
			'conditions' => array('description_id' => $id),
			'limit'			 => 1
		));
		if(count($t) == 1) return $t[0];
		return null;
	}
}
?>
<?php
class ScotEID_TransportDetails extends ScotEID_ExtendedModel
{
	const LEN_VEHICLE_ID  	= 14;
	const LEN_PERMIT_NO   	= 10;
	const LEN_ASSURANCE_NO  = 20;
	const LEN_NAD						= 60;
	const LEN_POSTCODE			= 9;
	const LEN_TEL						= 16;
	const LEN_EMAIL         = 100;
	const LEN_SLAP          = 8;
	
	const LEN_INDIVIDUAL_IDS = 1000;
	
	const ID_TYPE_TEMP      = 'temp';
	const ID_TYPE_BATCH     = 'batch';
	const ID_TYPE_INDIVIDUAL_IDS = 'ids';
	
	private $lot              = null;
	
	private static $validate = true;
	
	public static function set_validate($value) {
	  static::$validate = $value;
	}
	
	public function get_lot()     { return $this->lot; }
	public function set_lot($lot) { $this->lot = $lot; }
	
	protected function property_map() {
	  return array(
	    'HaulierName'        => 'haulier_business',
	    'DriverName'         => 'haulier_name',
	    'PermitNumber'       => 'haulier_permit_number',
	    'RegistrationNumber' => 'vehicle_id', 
	    'ExpectedDuration'   => 'expected_duration',
	    'LoadingDateTime'    => 'loading_datetime',
	    'UnloadingDateTime'  => 'unloading_datetime',
	    'DepartureTime'      => 'departure_time'    
	  );
	}
	
	public function update($other) {
	  foreach($this->public_attributes() as $k) {
	    $v = $other->get_attribute($k);
	    // argh, some questionable logic here.. don't update the item if it's null
	    // this is so that if an element isn't specified in the XML request it 
	    // doesn't get changed. not sure if that's correct, but it's how the existing
	    // webservices have worked historically
	    if($v !== null)
	      $this->set_attribute($k, $other->get_attribute($k));
	  }
	}
	
  public static function get_table_name() { 
   return "transport_details";
  }
	
	public function get_primary_key() {
		return array(
			'transport_details_id' => quote_int($this->get_id())
		);
	}

	protected function after_save_on_create() {
    $this->set_id(dbw_insert_id());
	}
	  
  protected static function get_attribute_definitions() {
    return array(
      'id'                            => array('type' => 'serial',   'field' => 'transport_details_id'),
      'vehicle_id'                    => array('type' => 'string',   'public' => true),
      'departure_assurance_number'    => array('type' => 'string',   'public' => true, 'field' => 'dep_assurance_no'),
      'departure_name'                => array('type' => 'string',   'public' => true, 'field' => 'dep_name'), 
      'departure_business'            => array('type' => 'string',   'public' => true, 'field' => 'dep_business'),
      'departure_address_2'           => array('type' => 'string',   'public' => true, 'field' => 'dep_add2'), 
      'departure_address_3'           => array('type' => 'string',   'public' => true, 'field' => 'dep_add3'),
      'departure_address_4'           => array('type' => 'string',   'public' => true, 'field' => 'dep_add4'), 
      'departure_postcode'            => array('type' => 'string',   'public' => true, 'field' => 'dep_postcode'), 
      'departure_tel'                 => array('type' => 'string',   'public' => true, 'field' => 'dep_tel'),
      'departure_email'               => array('type' => 'string',   'public' => true, 'field' => 'dep_email'),
      'departure_slap_mark'           => array('type' => 'string',   'public' => true, 'field' => 'dep_slap'),
      'destination_assurance_number'  => array('type' => 'string',   'public' => true, 'field' => 'dest_assurance_no'), 
      'destination_name'              => array('type' => 'string',   'public' => true, 'field' => 'dest_name'), 
      'destination_business'          => array('type' => 'string',   'public' => true, 'field' => 'dest_business'),
      'destination_address_2'         => array('type' => 'string',   'public' => true, 'field' => 'dest_add2'),
      'destination_address_3'         => array('type' => 'string',   'public' => true, 'field' => 'dest_add3'), 
      'destination_address_4'         => array('type' => 'string',   'public' => true, 'field' => 'dest_add4'),
      'destination_postcode'          => array('type' => 'string',   'public' => true, 'field' => 'dest_postcode'),
      'destination_tel'               => array('type' => 'string',   'public' => true, 'field' => 'dest_tel'),
      'destination_email'             => array('type' => 'string',   'public' => true, 'field' => 'dest_email'),
      'destination_slap_mark'         => array('type' => 'string',   'public' => true, 'field' => 'dest_slap'),
      'haulier_permit_number'         => array('type' => 'int',      'public' => true) ,
      'haulier_name'                  => array('type' => 'string',   'public' => true) ,
      'haulier_business'              => array('type' => 'string',   'public' => true),
      'expected_duration'             => array('type' => 'int',      'public' => true),
      'loading_datetime'              => array('type' => 'datetime', 'public' => true, 'field' => 'time_of_loading'),
      'unloading_datetime'            => array('type' => 'datetime', 'public' => true, 'field' => 'time_of_unloading'),
      'departure_time'                => array('type' => 'time',     'public' => true, 'field' => 'time_of_departure'),
      'individual_ids'                => array('type' => 'string',   'public' => true),
     'birth_cph'                      => array('type' => 'string',   'public' => true),
     'id_type'                        => array('type' => 'string',   'public' => true),  
     'driver_instructions'            => array('type' => 'string',   'public' => true)     
    );
  }
  
  protected function get_validations() {
    if(static::$validate) {
      return array(
        new ScotEID_PresenceValidation("departure_name"),
        new ScotEID_PresenceValidation("departure_postcode"),
        new ScotEID_PresenceValidation("destination_postcode"),
        new ScotEID_LengthOfValidation("individual_ids",      array('error' => "too many specified", 'max' => self::LEN_INDIVIDUAL_IDS)),
        new ScotEID_LengthOfValidation("vehicle_id",          array('max' => self::LEN_VEHICLE_ID)),

				new ScotEID_LengthOfValidation("departure_assurance_number", array('max' => self::LEN_ASSURANCE_NO)),
        new ScotEID_LengthOfValidation("departure_name",      array('max' => self::LEN_NAD)),
        new ScotEID_LengthOfValidation("departure_business",  array('max' => self::LEN_NAD)),
        new ScotEID_LengthOfValidation("departure_address_2", array('max' => self::LEN_NAD)),
        new ScotEID_LengthOfValidation("departure_address_3", array('max' => self::LEN_NAD)),
        new ScotEID_LengthOfValidation("departure_address_4", array('max' => self::LEN_NAD)),
        new ScotEID_LengthOfValidation("departure_tel", 			array('max' => self::LEN_TEL)),
        new ScotEID_LengthOfValidation("departure_postcode",  array('max' => self::LEN_POSTCODE)),
				new ScotEID_LengthOfValidation("departure_slap_mark", array('max' => self::LEN_SLAP)),
				new ScotEID_LengthOfValidation("departure_email",			array('max' => self::LEN_EMAIL)),
				
				new ScotEID_LengthOfValidation("destination_assurance_number", array('max' => self::LEN_ASSURANCE_NO)),
				new ScotEID_LengthOfValidation("destination_name",      array('max' => self::LEN_NAD)),
        new ScotEID_LengthOfValidation("destination_business",  array('max' => self::LEN_NAD)),
        new ScotEID_LengthOfValidation("destination_address_2", array('max' => self::LEN_NAD)),
        new ScotEID_LengthOfValidation("destination_address_3", array('max' => self::LEN_NAD)),
        new ScotEID_LengthOfValidation("destination_address_4", array('max' => self::LEN_NAD)),
        new ScotEID_LengthOfValidation("destination_tel", 			array('max' => self::LEN_TEL)),
        new ScotEID_LengthOfValidation("destination_postcode",  array('max' => self::LEN_POSTCODE)),
				new ScotEID_LengthOfValidation("destination_slap_mark", array('max' => self::LEN_SLAP)),
				new ScotEID_LengthOfValidation("destination_email",			array('max' => self::LEN_EMAIL)),
				
				new ScotEID_LengthOfValidation("haulier_name",					array('max' => self::LEN_NAD)),
				new ScotEID_LengthOfValidation("haulier_business",			array('max' => self::LEN_NAD)),
				new ScotEID_LengthOfValidation("haulier_permit_number", array('max' => self::LEN_PERMIT_NO)),
				new ScotEID_LengthOfValidation("driver_instructions", array('max' => self::LEN_NAD)),
        new ScotEID_CustomValidation("validate_address")        
      );
    } else {
      return array();
    }
  }
  
  const REQUIRED_ADDRESS_FIELDS = 2;
	
	public function validate_address() {
    foreach(array("departure","destination") as $value)
    {
      $get_business = "get_" . $value . "_business";
      $get_add2 = "get_" . $value . "_address_2";
      $get_add3 = "get_" . $value . "_address_3";
      $get_add4 = "get_" . $value . "_address_4";
      $a = array(
        $value . '_business' => $this->$get_business(),
        $value . '_address_2' => $this->$get_add2(),
        $value . '_address_3' => $this->$get_add3(),
        $value . '_address_4' => $this->$get_add4()
      );
      
      $c = 0;
      foreach($a as $k => $f) {
        if($f && strlen(trim($f)) > 0) {
          $c += 1;
        }
      }
      
      if($c < static::REQUIRED_ADDRESS_FIELDS) {
        $this->add_error($value . "_business", "^$value keeper address must contain at least 2 lines");
      }
    }
  }
    
  public function get_loading_date() {
    return $this->get_loading_datetime();
  }
  
  public function get_loading_time() {
    return $this->get_loading_datetime();
  }
	
	public static function get($id) {
		$qid = quote_int($id);
		$t = static::find(array(
			'conditions' => "#id# = $qid",
			'limit'			 => 1
		));		
		return (count($t) == 1) ? $t[0] : null;
	}

  private $keeper_map = array(
    'name'         => 'name',
    'address_1'    => 'business',
    'address_2'    => 'address_2',
    'address_3'    => 'address_3',
    'address_4'    => 'address_4',
    'postcode'     => 'postcode',
    'email'        => 'email',
    'landline_tel' => 'tel',
    'slap_mark'    => 'slap_mark'
  );
  
	public function set_departure_keeper($keeper) {
	  if($keeper) {
  	  foreach($this->keeper_map as $k => $v) {
  	    $this->set_attribute("departure_" . $v, $keeper ? $keeper->get_attribute($k) : null);
  	  }
  	  $this->set_departure_assurance_number($keeper->get_pig_assurance_number());
	  }
	}
	
	public function set_destination_keeper($keeper) {
	  if($keeper) {
  	  foreach($this->keeper_map as $k => $v) {
  	    $this->set_attribute("destination_" . $v, $keeper ? $keeper->get_attribute($k) : null);
  	  }
  	  $this->set_destination_assurance_number($keeper->get_pig_assurance_number());
	  }
	}
	
	public function get_departure_keeper() {
	  $keeper = new ScotEID_Keeper();
	  	  
	  $r = false;
	  
	  foreach($this->keeper_map as $k => $v) {
	    $v = $this->get_attribute("departure_" . $v);
	    if($v !== null) {
	      $r = true;
	    }
	    $keeper->set_attribute($k, $v);
	  }
	  
	  $keeper->set_pig_assurance_number($this->get_departure_assurance_number());
	  	  	  
	  return $r ? $keeper : null;
	}
	
	public function get_destination_keeper() {
	  $keeper = new ScotEID_Keeper();
	  	  
	  $r = false;
	  
	  foreach($this->keeper_map as $k => $v) {
	    $v = $this->get_attribute("destination_" . $v);
	    if($v !== null) {
	      $r = true;
	    }
	    $keeper->set_attribute($k, $v);
	  }
	  
	  $keeper->set_pig_assurance_number($this->set_destination_assurance_number());
	  	  	  
	  return $r ? $keeper : null;
	}
	
	public function get_individual_ids_array() {
	  $ids = $this->get_individual_ids();
	  
	  // try do some sanitization - we seem to have all sorts in this column at the moment
    $ids = str_replace("\n", ",", $ids);
  
    if(!empty($ids)) {
      $split = explode(",", $ids); 
      $split = array_map(function($i) { return trim($i); }, $split);
      $split = array_filter($split, function($i) { return !empty($i); }); 
         
      if(count($split) > 0) {
        return $split;
      }
    }
    return array();
	}
}
?>

<?php
class ScotEID_Keeper extends ScotEID_ExtendedModel
{
  const LEN_NAME     = 125;
  const LEN_ADDRESS  = 100;
  const LEN_POSTCODE = 20;
  const LEN_TEL      = 20;
  
  private $keeper_holdings = null;
    
  protected function property_map() {
    return array(
      'Name'        => 'name',
      'Address1'    => 'address_1',
      'Address2'    => 'address_2',
      'Address3'    => 'address_3',
      'Address4'    => 'address_4',
      'Postcode'    => 'postcode',
      'Tel'         => 'landline_tel',
      'Fax'         => 'fax',
      'Email'       => 'email',
      'FlockNumber' => 'flock_number',
      'SlapMark'        => 'slap_mark',
      'AssuranceNumber' => 'pig_assurance_number' // bit of a kludge here.. 
    );
  }
  
  public static function get_table_name() {
		return "keepers";
	}
	
	public function get_primary_key() {
		return array(
			'uid' => quote_int($this->get_uid())
		);
	}
	
	protected static function get_attribute_definitions() {
	  return array(
	    'uid'                    => array('type' => 'int'),
	    'name'                   => array('type' => 'string',     'public' => true),
	    'address_1'              => array('type' => 'string',     'public' => true),
	    'address_2'              => array('type' => 'string',     'public' => true),
	    'address_3'              => array('type' => 'string',     'public' => true),
	    'address_4'              => array('type' => 'string',     'public' => true),
	    'postcode'               => array('type' => 'string',     'public' => true),
	    'landline_tel'           => array('type' => 'string',     'public' => true),
	    'mobile_tel'             => array('type' => 'string',     'public' => true),
	    'fax'                    => array('type' => 'string',     'public' => true),
	    'flock_assurance_number' => array('type' => 'int',        'public' => true),
	    'pig_assurance_number'   => array('type' => 'int',        'public' => true),
	    'pig_producer_group_id'  => array('type' => 'int',        'public' => true),
	    'sheep_breeds'           => array('type' => 'serialized', 'public' => true)
	  );
	}
	
	protected function get_validations() {
	  return array(
	    new ScotEID_PresenceValidation('uid'),
	    new ScotEID_PresenceValidation('name'),
	    new ScotEID_PresenceValidation('postcode'),
	    new ScotEID_PresenceValidation('landline_tel'),
	    new ScotEID_AssociationValidation('keeper_holdings'),
	    new ScotEID_LengthOfValidation('name',          array('max' => self::LEN_NAME)),
	    new ScotEID_LengthOfValidation('address_1',     array('max' => self::LEN_ADDRESS)),
	    new ScotEID_LengthOfValidation('address_2',     array('max' => self::LEN_ADDRESS)),
	    new ScotEID_LengthOfValidation('address_3',     array('max' => self::LEN_ADDRESS)),
	    new ScotEID_LengthOfValidation('address_4',     array('max' => self::LEN_ADDRESS)),
	    new ScotEID_LengthOfValidation('postcode',      array('max' => self::LEN_POSTCODE)),
	    new ScotEID_LengthOfValidation('landline_tel',  array('max' => self::LEN_TEL)),
	    new ScotEID_LengthOfValidation('mobile_tel',    array('max' => self::LEN_TEL)),
	    new ScotEID_LengthOfValidation('fax',    array('max' => self::LEN_TEL)),
      new ScotEID_CustomValidation("validate_keeper_holdings"),
      new ScotEID_PatternValidation('postcode', '/^(KW|IV|AB|PH|DD|PA|G|FK|KY|HS|EH|ML|KA|DG|TD|ZE)[0-9]{1,2} [0-9][ABD-HJLNP-UW-Z]{2}$/i', array('error' => 'is not a valid Scottish postcode')),
      new ScotEID_CustomValidation("validate_address")
	  );
	}
	
	const REQUIRED_ADDRESS_FIELDS = 2;
	
	public function validate_address() {
	  $a = array(
	   'address_1' => $this->get_address_1(),
	   'address_2' => $this->get_address_2(),
	   'address_3' => $this->get_address_3(),
	   'address_4' => $this->get_address_4()
	  );
	  
	  $c = 0;
	  foreach($a as $k => $f) {
	    if($f && strlen(trim($f)) > 0) {
	      $c += 1;
	    }
	  }
	  
	  if($c < static::REQUIRED_ADDRESS_FIELDS) {
      $this->add_error("address_1", "^Address must contain at least 2 lines");
	  }
	}
	
  public function validate_keeper_holdings() {
    if(count($this->get_keeper_holdings()) > 25) {
      $this->add_error("keeper_holdings", "cannot exceed 25");
    }
  }
	
	protected static function nested_assignment_associations() {
	  return array('keeper_holdings');
	}
 
  public static function is_registered_as_keeper($uid) {
    return static::count(array('conditions' => array('uid' => $uid))) == 1;
  }
  
  //
  // Keeper holding association logic
  
  public function load_keeper_holdings() {
    if($this->is_saved()) {
      $this->keeper_holdings = ScotEID_KeeperHolding::find(array('conditions' => array('uid' => $this->get_uid())));
    } else {
      $this->keeper_holdings = array();
    }
  }
  
  public function get_keeper_holdings() {
    if($this->keeper_holdings == null) {
      $this->load_keeper_holdings();
    }
    return $this->keeper_holdings;
  }
  
  public function set_keeper_holdings($holdings) {    
    if(is_array($holdings) && count($holdings) > 0) {
      $set = array();
      if($holdings[0] instanceof ScotEID_KeeperHolding) {
        $set = $holdings;
      } else {
        foreach($holdings as $holding_data) {
          if(isset($holding_data['cph'])) {
            $h = new ScotEID_KeeperHolding();
            $h->extract_public_from($holding_data);
            $set[] = $h;
          }
        }
      }
      $this->keeper_holdings = $set;
    } else {
      $this->keeper_holdings = array();
    }
  }
  
  protected function after_save() {
    $sheep_keeper  = false;
    $pig_keeper    = false;
        
    // Iterate through keeper holdings and if the keeper is resident on one of them
    // use the keeper's name and address to update that CPH
    foreach($this->get_keeper_holdings() as $holding) {
      if($holding->get_resident()) {
        $h = ScotEID_Holding::first(array('conditions' => array('cph' => $holding->get_cph()->get_cph())));
        if(!$h) {
          // create a new holding
          $h = new ScotEID_Holding();
          $h->set_cph($holding->get_cph()->get_cph());
        }
        $h->set_name($this->get_name());
        $h->set_business($this->get_address_1());
        $h->set_address_2($this->get_address_2());
        $h->set_address_3($this->get_address_3());
        $h->set_address_4($this->get_address_4());
        $h->set_postcode($this->get_postcode());
        $h->set_telephone($this->get_landline_tel());
        $h->save();
      }
      
      $flock_number    = $holding->get_sheep_flock_number();
      $pig_herd_number = $holding->get_pig_herd_number();
      
      $sheep_keeper = $sheep_keeper || !empty($flock_number);
      $pig_keeper   = $pig_keeper   || !empty($pig_herd_number);
    }
    
    //
    // Update the users roles based on their holdings
        
    $role = ScotEID_User::ROLE_PARTNER_FARM;
    if($sheep_keeper) {
      $this->insert_role($role, $this->get_uid());
    } else {
      $this->delete_role($role, $this->get_uid());
    }
    
    $role = ScotEID_User::ROLE_PIG_FARM;
    if($pig_keeper) {
      $this->insert_role($role, $this->get_uid());
    } else {
      $this->delete_role($role, $this->get_uid());
    }
    
    $quid = quote_int($this->get_uid());
    dbw_query("DELETE FROM keeper_holdings WHERE uid = $quid");
                
    foreach($this->get_keeper_holdings() as $holding) {
      $holding->set_saved(false); // HACK
      $holding->set_uid($this->get_uid());
      $holding->save();     
    }
  } 
  
  private function insert_role($role, $uid) {
    ScotEID_User::add_role($uid, $role);
  }
  
  private function delete_role($role, $uid) {
    ScotEID_User::remove_role($uid, $role);
  }
   
  protected function after_save_on_create() {

  }
  
  /** NON PERSISTANT FIELDS */
  
  private $email;        // not saved to db
  private $flock_number; // not saved to db
  private $slap_mark;    // not saved to db
  
  public function set_email($email) {
    $this->email = $email;
  }
  
  public function set_flock_number($fn) {
    $this->flock_number = $fn;
  }
  
  public function set_slap_mark($sl) {
    $this->slap_mark = $sl;
  }
  
  public function get_email() {
    return $this->email;
  }
  
  public function get_flock_number() {
    return $this->flock_number;
  }
  
  public function get_slap_mark() {
    return $this->slap_mark;
  }
}
?>
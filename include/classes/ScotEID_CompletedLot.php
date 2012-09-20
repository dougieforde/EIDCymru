<?php
class ScotEID_CompletedLot extends ScotEID_Lot
{	  
  public function __construct($lot = null) {
    if($lot) {
      $this->update($lot);
    }
  }
  
  public static function get_table_name() {
    return "tblsslots";
  }
  
  public static function get_dynamic_search_properties() {
    return array_merge(
      parent::get_dynamic_search_properties(),
      array(
        "lot_problems_count"
      )
    );
  }
  
  public static function sql_for_dynamic_search_property($property) {
    if($property == "lot_problems_count") {
      return "(SELECT COUNT(*) FROM lot_problems WHERE sams_movement_reference = tblsslots.SAMS_Movement_Reference)";
    } else {
      return parent::sql_for_dynamic_search_property($property);
    }
  }

  //
  // see ScotEID_lot for documentation of this function
  
  public function can_update_property($attribute) {
  
    // legacy support for anywhere that editing a lot is done without
    // supplying an editing uid
    
    $uid  = $this->get_uid();
    $euid = $this->get_editing_uid();
    
    if($euid == null)
      return true;
      
    // if it's not a pig lot, all attributes are basically allowed to be
    // edited
    
    if($this->get_species_id() != ScotEID_Species::SPECIES_PIGS)
      return true;
      
    $bpex  = ScotEID_User::user_has_role($uid, ScotEID_User::ROLE_BPEX);
    $ebpex = ScotEID_User::user_has_role($euid, ScotEID_User::ROLE_BPEX);
    
    // if not created by bpex and being edited by bpex, restrict fields which
    // can be changed
    
    if(!$bpex && $ebpex) {
      return in_array($attribute, array(
        'arrival_date',
        'head_count',
        'doa_count',
        'destination_location',
	'destination_keeper'
      ));
    } 
    
    return true;
  }

  // TODO: replace this with newer property based finder from staging site
  public static function find_by_read_location_and_lot_number_and_date($read_location, $lot_number, $date) {
		$qdate = quote_date($date);
		$qlot  = quote_str('^0*' . $lot_number . '$');
		$qloc  = quote_str($read_location->getCPH());
		
		$sql = "SELECT * FROM " . self::_table_name() .
					 " WHERE LotDate = $qdate AND ReadLocationCPH = $qloc AND Lot_No REGEXP $qlot";					
		return dbw_stack_objects($sql, 'ScotEID_CompletedLot', array('method' => 'extract_all_from'));
  }

	// TODO: replace this with newer property based finder from staging site
	// return lots moved on a specific date, or within 3 days of the date, from a departure to a destination
	public static function find_by_movement($lot_date, $departure_location, $destination_location) {
		$qdate = quote_date($lot_date);
		$qdest = quote_str($destination_location->getCPH());
		$qdep  = quote_str($departure_location->getCPH());
		
		$sql = "SELECT * FROM " . self::_table_name() . 
					 " WHERE LotDate BETWEEN DATE_SUB($qdate, INTERVAL 3 DAY) AND DATE_ADD($qdate, INTERVAL 3 DAY) ". 
					 " AND DestinationCPH = $qdest AND DepartureCPH = $qdep";
					
		return dbw_stack_objects($sql, 'ScotEID_CompletedLot', array('method' => 'extract_all_from'));
	}

  protected function after_save() {
  	parent::after_save();
    ScotEID_TagRegister::lot_updated($this->get_sams_movement_reference());
	}
	
	protected function after_delete() {
	  parent::after_delete();
	  ScotEID_TagRegister::lot_deleted($this->get_sams_movement_reference());
	}
	
	protected function after_save_on_update() {
	  parent::after_save_on_update();
	  //
  	// delete any lot problems on the pretty weak assumption that they've been fixed
  	// problems will be regenerated daily if they still exist
  	// ideally we'd just regenerate the problems for this lot right now, but the code needs
  	// a bit of work to allow that
  	foreach($this->get_lot_problems() as $problem) {
  	  $problem->delete();
  	}
	}
  
  protected function after_save_on_create() {
    parent::after_save_on_create();
  }
  
  protected function before_save_on_create() {
    parent::before_save_on_create();
  }

  public function perform_custom_validate() { 
    $m = $this->get_movement_type_id();
    if(empty($m)) {
      $this->add_error('movement_type', 'cannot be blank');
    }
           
    $validate_locations = array('read_location', 'departure_location', 'destination_location');
           
    foreach($validate_locations as $field) {
      $method = "get_$field";
      $val = $this->$method();
      if(empty($val)) {
        $this->add_error($field, 'cannot be blank');
      }
    }
    
    if($this->get_head_count() === null) {
      $this->add_error("head_count", "must be specified");
    }
    
    /* pig validations */
    if($this->get_species_id() == ScotEID_Species::SPECIES_PIGS) {
      if($this->get_arrival_date() != null) {
        $doa = $this->get_doa_count();
        if($doa === null) {
          $this->add_error("doa_count", "must be specified");
        }
      }
      
      // jb 18/2/2012 check to ensure that temporarily identified animals are not 
      // recorded moving to a show/mart/abattoir/export
      
      $tr = $this->get_transport_details();
      if($tr->get_id_type() == ScotEID_TransportDetails::ID_TYPE_TEMP)
      {
        $holding = ScotEID_Holding::first(array('conditions' => array('cph' => $this->get_destination_location())));
        if($holding)
        {
          $illegal_locations = array(ScotEID_Holding::LOCATION_TYPE_MART, 
                                     ScotEID_Holding::LOCATION_TYPE_ABATTOIR,
                                     ScotEID_Holding::LOCATION_TYPE_SHOW,
                                     ScotEID_Holding::LOCATION_TYPE_EU_IMPORT_EXPORT);
          if( in_array(strtolower($holding->get_location_type()), $illegal_locations))
          {
            $this->add_error("destination_location", 
                             ": Temporarily identified animals cannot be moved to show/mart/abattoir/export" );
          }
        }
      }
      
      //
      // check for moves in or out of scotland and make sure they have a bpex movement type
      // set - unless uploaded by bpex
      
      $bpex = ScotEID_User::user_has_role($this->get_uid(), ScotEID_User::ROLE_BPEX);
      
      if(!$bpex) {
      
        if($this->is_cross_border() && $this->get_bpex_movement_type_id() == null) {
          $this->add_error("bpex_movement_type_id", "must be specified");
        }

      }
    }
    
    parent::perform_custom_validate();
  }

  protected function get_validations() {
    $v = parent::get_validations();
    return array_merge($v, array(
      new ScotEID_PresenceValidation("lot_date")
    ));
  }
  
  public function get_primary_key() {
    return array(
      'Lot_No'          => quote_str($this->get_lot_number_was()),
      'ReadLocationCPH' => quote_str($this->get_read_location_was()),
      'LotDate'         => quote_date($this->get_lot_date_was()),
      'MovementType'    => quote_int($this->get_movement_type_id_was()),
			'Species'					=> quote_int($this->get_species_id_was())
    );
  }

  protected static function get_attribute_definitions() {
    $a = parent::get_attribute_definitions();
    return array_diff_key($a, array('filename' => false));
  }

  public function get_filename() {
    return null;
  }
  
  public function set_filename($f) {
  }

  public function uncomplete($uid = null) {
    $incomplete_lot = new ScotEID_Lot();
    $incomplete_lot->update($this);
    
    if(!$uid) {
        $uid = $this->get_uid();
    }
    
    // FIXME: rework this so that you don't need to explicitly load everything
    $this->load_tag_readings();
    $this->load_flock_tags();
    
    if($this->get_tag_readings()) {
        $incomplete_lot->set_tag_readings($this->get_tag_readings());
    }
    if($this->get_flock_tags()) {
        $incomplete_lot->set_flock_tags($this->get_flock_tags());
    }
   
    $incomplete_lot->set_lot_number($this->get_reader_lot_number());
 
    $audit_item = new ScotEID_AuditItem();
    $audit_item->set_uid($uid);
    $audit_item->set_type(ScotEID_AuditItem::UNCOMPLETE_LOT_TYPE);
    $audit_item->set_object($this);
    
    try {
      dbw_begin();
      if($audit_item->save() && $incomplete_lot->save()) {
        $this->delete();
        dbw_commit();
      } else {
        dbw_rollback();
        return false;
      }
    } catch(Exception $ex) {
      dbw_rollback();
      return false;
    }
    return true;
  }
  
  public function cancel() {
    dbw_begin();
 
    try {
      // roll back the tag register
      ScotEID_TagRegister::lot_deleted($this->get_sams_movement_reference());
    
      // assign a unique, unused lot number
      $this->set_lot_number(ScotEID_Utils::get_deleted_lot_number($this->get_lot_date(), $this->get_read_location()));
    
      // set movement type to hidden and save
      $this->set_movement_type_id(ScotEID_MovementType::MOVEMENT_TYPE_HIDDEN);
      
      if($this->save()) {
        dbw_commit();
      } else {
        dbw_rollback();
        throw new Exception("unable to cancel lot!");
      }
      
    } catch(Exception $ex) {
      dbw_rollback();
      throw $ex;
    }
  }

  const EDITABLE_FOR = "6 months";

	public function is_editable_by($uid) {
    if(ScotEID_User::user_has_role($uid, array("Account admin", "Fieldsman", "SuperUser"))) {
      return true;
    }
    
    if($this->get_species_id() == ScotEID_Species::SPECIES_PIGS) {
            
      // user is bpex - okay if lot destination is cross-border
      if(ScotEID_User::user_has_role($uid, ScotEID_User::ROLE_BPEX)) {
        $d = $this->get_destination_location();
        if($d && !($d->is_scottish())) {
          return true;
        }
      }
      
      // once signed off - can't be edited (unless being edited by bpex(?) - this logic needs looked at
			// again)
      if($this->is_signed_off() && !ScotEID_User::user_has_role($uid, ScotEID_User::ROLE_BPEX))
        return false;
      
      // this user created the lot
      if($this->get_uid() == $uid) {
        return true;
    
      // b. if the read_location or destination_location is one of the registered holdings of the current
      //    user...
      } else if(ScotEID_User::is_registered_holding($uid, $this->get_read_location()) ||
                ScotEID_User::is_registered_holding($uid, $this->get_destination_location())) {
        return true;
      }
      
    } else if($this->get_species_id() == 4) {
  		if($uid == $this->get_uid()) {
  			if($this->get_lot_date() > strtotime("-" . self::EDITABLE_FOR)) {
  				return true;
  			}
  		}
  		
		}
		return false;
	}
	
	/**
	 * Check if a user can download this lot as CSV/PDF - basically this is
	 * if the lot belongs to them or if the departure, read, or destination
	 * locations are one of this users registered holdings
	 * @param $uid id of user to check
	 * @return boolean
	 */
	public function is_downloadable_by($uid) {
		if($uid == $this->get_uid() || ScotEID_User::user_has_role($uid, array('SuperUser', 'Fieldsman', 'RPID_user', 'Local_Auth'))) { 
			return true;
		}
		$holdings = ScotEID_User::get_registered_holdings($uid);
		foreach($holdings as $holding) {
			if($holding->getCPH() == $this->get_departure_location()->getCPH() ||
				 $holding->getCPH() == $this->get_read_location()->getCPH() ||
				 $holding->getCPH() == $this->get_destination_location()->getCPH())  {
					return true;
				}
		}
		return false;
	}
	
  public function is_departure_location_required() {
    $m = $this->get_movement_type_id();
    // ON, OFF, MART, ABATTOIR
    if($m == 2 || $m == 3 || $m == 4 || $m == 8 || $m == 13) {
      return true;
    } else {
      return false;
    }
  }
  
  public function is_destination_location_required() {
    $m = $this->get_movement_type_id();
    // OFF, MART?
    if($m == 3 || $m == 4 || $m == 13) {
      return true;
    } else {
      return false;
    }
  }

  public function before_validation() {
    parent::before_validation();
    
    /* Clear departure and destination locations where not required, populating
       them with the read location? */
       
    if(!$this->is_departure_location_required() && $this->get_departure_location() === null) {
      $this->set_departure_location($this->get_read_location());
    }
    
    if(!$this->is_destination_location_required() && $this->get_destination_location() === null) {
      $this->set_destination_location($this->get_read_location());
    }
    
    if($this->get_species_id() == ScotEID_Species::SPECIES_PIGS) {
      
      // read location always the same as departure location for pigs
      $this->set_read_location($this->get_departure_location());
      
      // if the consignment is being confirmed as arrived, make sure to set the
      // receiving keeper id
      if($this->get_arrival_date() != null && $this->get_receiving_keeper_id() == null) {
        $this->set_receiving_keeper_id($this->get_editing_uid());
      }
      
      // for bpex - mark any historic data as hidden
      $uid = $this->get_uid();
      if($uid && ScotEID_User::user_has_role($uid, array(ScotEID_User::ROLE_BPEX))) {
        if($this->get_lot_date() < strtotime("2012-03-30")) {
          $this->set_movement_type_id(ScotEID_MovementType::MOVEMENT_TYPE_HIDDEN);
        }
      }
    }
  }
  
  public function is_signed_off() {
    return $this->get_receiving_keeper_id() !== null;
  }

  protected function before_save() {
    parent::before_save();
  }

 protected function before_save_on_update() {
   if($this->is_dirty()) {
     $changes = $this->get_changed_attributes();
     $changes['sams_movement_reference'] = $this->get_sams_movement_reference();
     
     $audit_item = new ScotEID_AuditItem();
     $audit_item->set_sams_movement_reference($this->get_sams_movement_reference());
     $audit_item->set_uid($this->get_editing_uid());
     $audit_item->set_type(ScotEID_AuditItem::LOT_CORRECTION_TYPE);
     $audit_item->set_object($changes);
     $audit_item->save();
   }
   parent::before_save_on_update();
 }
 
 public $__lot_problems = null;
 
 public function count_lot_problems() {
   return count($this->get_lot_problems());
 }
 
 public function get_lot_problems() {
   if($this->__lot_problems === null) {
     $this->__lot_problems = ScotEID_LotProblem::find(
       array(
         'conditions' => array(
           'sams_movement_reference' => $this->get_sams_movement_reference()
          )
        )
      );
   }
   return $this->__lot_problems;
 }
 
 public static function load_lot_problems($lots = array()) {
   $refs     = array_map(function($l) { return $l->get_sams_movement_reference(); }, $lots);
   $problems = ScotEID_LotProblem::find(array('conditions' => array(
    'sams_movement_reference.in' => $refs
   )));
   $mapped = array();
   foreach($problems as $problem) {
     $s = $problem->get_sams_movement_reference();
     if(!isset($mapped[$s])) {
       $mapped[$s] = array();
     }
     $mapped[$s][] = $problem;
   }
   foreach($lots as $lot) {
     $s = $lot->get_sams_movement_reference();
     if(isset($mapped[$s])) {
       $lot->__lot_problems = $mapped[$s];
     } else {
       $lot->__lot_problems = array();
     }
   } 
 }
 
}
?>

<?php
class LotNotFoundException extends Exception {}

class ScotEID_Lot extends ScotEID_ExtendedModel {    
  const LEN_LOT_NUMBER = 20;
  const LEN_SLAP       = 20;
  const LEN_FOREIGN_REFERENCE = 34;
  
	const BPEX_UNSENT 					          = 0x0000000;
	const BPEX_SENT   					          = 0x0000001;
	const BPEX_ERROR  					          = 0x0000002;
	
	const BPEX_ERROR_SOURCE_SITE          = 0x0000004;
	const BPEX_ERROR_DEST_SITE            = 0x0000008;
	
	const BPEX_ERROR_MULTIPLE_SOURCE_SITE = 0x0000010;
	const BPEX_ERROR_MULTIPLE_DEST_SITE   = 0x0000020;
	
	const BPEX_HOLD                       = 0x0000040; // don't send - data needs reviewed first

  // TODO: might be better to query these from db
  private $VALID_MOVEMENT_TYPES 			= array(
    0,
    1,
    2,
    3,
    ScotEID_MovementType::MOVEMENT_TYPE_MART,
    5,
    6,
    7,
    8,
    9,
    10,
    11,
    12,
    13,
    14,
    ScotEID_MovementType::MOVEMENT_TYPE_HIDDEN,
    16,
    21,
    22,
    23,
    24,
    31,
    32
  );
  
	private $VALID_SPECIES_IDS    			= array(1,2,3,4,5,6);
	private $VALID_LOT_DESCRIPTION_IDS	= array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22);
  
  // Public properties for assignment to objects
  // that are created by unmarshalling from XML requests
  
  protected function property_map() { 
    return array(
      'LotReference'                  => 'sams_movement_reference',
      'ExternalReference'             => 'foreign_reference',
      'LotNumber'                     => 'lot_number',
      'LotDate'                       => 'lot_date_string',
      'DepartureDate'                 => 'departure_date',
      'ArrivalDate'                   => 'arrival_date',
      'MovementType'                  => 'movement_type_id',
			'Species'												=> 'species_id',
			'BatchMark'                     => 'batch_mark',
			'IDType'                        => 'id_type',
			'LotDescription'                => 'description_id',
      'HeadCount'                     => 'head_count',
      'ReadCount'                     => 'read_count',
      'DOACount'                      => 'doa_count',
      'AllEID'                        => 'all_eid',
      'VisuallyRead'                  => 'visually_read',
      'ReadLocation'                  => 'read_location',
      'DepartureLocation'             => 'departure_location',
      'DestinationLocation'           => 'destination_location',
      'TagReadings'                   => 'tag_readings',
      'FlockTags'                     => 'flock_tags',
      'PigIdentities'                 => 'pig_identities',
      'TransportInformation'          => 'transport_details',
      'DepartureKeeperFlockNumber'    => 'departure_keeper_flock_number',
      'DestinationKeeperFlockNumber'  => 'destination_keeper_flock_number',
      'DepartureKeeper'               => 'departure_keeper',
      'DestinationKeeper'             => 'destination_keeper',
      'FCI'                           => 'fci',
      'BuyerInvoiceNumber'            => 'buyer_invoice_number',
      'SellerPaymentNumber'           => 'seller_payment_number'
    ); 
  }
  
  public function set_batch_mark($m) {
    $m = trim($m);
    if(!empty($m)) {
      $this->_set_attribute("batch_mark", strtoupper($m));
    } else {
      $this->_set_attribute("batch_mark", null);
    }
  }
  
  public function get_id_type() {
    if($this->get_species_id() == ScotEID_Species::SPECIES_PIGS) {
      $t = $this->get_transport_details();
      if($t) {
        return $t->get_id_type();
      } else {
        return null;
      }
    }
  }
  
  public function set_lot_date_string($date) {
      $this->set_lot_date($date);
  }
  
  public function get_lot_date_string() {
      $d = $this->get_lot_date();
      if($d) {
          return strftime("%Y-%m-%d", $this->get_lot_date());
      } else {
          return null;
      }
  }
  
  private $movement_type      = null;
	private $species					  = null;
  private $transport_details  = null;
  private $tag_readings       = null;
  private $flock_tags         = null;
  private $description        = null;
  
  public static function get_table_name() {
    return "templots";
  }
  
  public function get_primary_key() {
    return array(
      "Lot_No"   => quote_str($this->get_lot_number_was()), 
      "LotDate"  => quote_date($this->get_lot_date_was(), false), 
      "uid"      => quote_int($this->get_uid_was()),
      "Filename" => quote_str($this->get_filename_was())
    );
  }

  public static function get_dynamic_search_properties() {
    return array("read_percent", "status");
  }
  
  public static function sql_for_dynamic_search_property($property) {
    switch($property) {
      case "read_percent":
        return "((Qty_Reads / Qty_Sheep) * 100)";
      case "status":
        return "IF(receiving_keeper_id IS NOT NULL, 'received', IF(Qty_Sheep = 0, 'cancelled', IF(LotDate > NOW(), 'pre-notified', 'consigned')))";
    }
    return null;
  }
  
	protected static function get_expression_for_order_by_column($column) {
	  if($column == 'lot_number') {
	    $table_name = static::get_table_name();
	    return "CAST($table_name.Lot_No AS UNSIGNED)";
	  } else {
	    return parent::get_expression_for_order_by_column($column);
	  }
	}
  
  public function perform_custom_validate() {

    if($this->get_movement_type_id() == null || !in_array($this->get_movement_type_id(), $this->VALID_MOVEMENT_TYPES)) {
      $this->add_error('movement_type', "is unknown");
    }

		if($this->get_species_id() == null || !in_array($this->get_species_id(), $this->VALID_SPECIES_IDS)) {
			$this->add_error('species', 'is unknown');
		}
		
		if($this->get_description_id() != null && !in_array($this->get_description_id(), $this->VALID_LOT_DESCRIPTION_IDS)) {
			$this->add_error('description_id', 'is unknown');
		}
    
    foreach(array('read_location', 'departure_location', 'destination_location') as $field) {
      $method = "get_$field";
      $val = $this->$method();
      if(!empty($val) && !$val->valid()) {
        $this->add_error($field, "is not valid");
      }
    }
    
    if($this->get_batch_mark() && strlen($this->get_batch_mark()) > self::LEN_SLAP) {
      $this->add_error("batch_mark", "must not exceed " . self::LEN_SLAP . " characters");
    }
    
    $tr = $this->get_transport_details();
    
    if($tr) {
      if(!$tr->validate()) {
        foreach($this->get_transport_details()->get_errors() as $field => $errors) {
          foreach($errors as $error) {
            $this->add_error($field, $error);
          }
        }
        $this->add_error("transport_details", "are invalid");
      }
    }
    
    if($this->get_species_id() == 3) {
      $b = $this->get_batch_mark();
      if(empty($b) && count($this->get_pig_identities()) == 0) {
        $this->add_error("batch_mark", "must be supplied unless specifying individual identities");
      }
    }
    
    // Validate presence of tag readings on creation
    if(!$this->is_saved()) {
      // Validate each tag
      foreach($this->get_tag_readings() as $tag_reading) {
        if(!$tag_reading->validate()) {
          $this->add_error('tag_readings', 'are invalid');
          break;
        }
      }
      foreach($this->get_flock_tags() as $flock_tag) {
        if(!$flock_tag->validate()) {
          $this->add_error('flock_tags', 'are invalid');
          break;
        }
      }
    }
  }
  
  protected function get_validations() {
    return array(
      new ScotEID_PresenceValidation("lot_number"),
      new ScotEID_PresenceValidation("uid"),
      new ScotEID_CustomValidation("perform_custom_validate")
    );
  }
  
  protected function before_validation() {
    $f = $this->get_filename();
    if(empty($f)) {
      $this->set_filename(time() .".txt");
    }
    if($this->get_reader_lot_number() == null) {
      $this->set_reader_lot_number($this->get_lot_number());
    }
    if($this->get_movement_type_id() == 8) {
      $this->set_destination_location($this->get_read_location());
    }
		// FIXME: backwards compatability - should possibly do this at the request point
		// with older schema versions
		if($this->get_species_id() == null) {
			$this->set_species_id(4);
		}
  }
  
	protected function after_save_on_create() {
		$this->set_sams_movement_reference(dbw_insert_id());
		parent::after_save_on_create();
	}
    
  protected function before_save() {
    if((!$this->is_saved() || $this->is_dirty()) && !$this->sent_to_bpex_changed()) {
      
      if($this->get_species_id() == ScotEID_Species::SPECIES_PIGS) {
        $editing_uid = $this->get_editing_uid();
        $uid         = $this->get_uid();

        $editing_bpex = $editing_uid && ScotEID_User::user_has_role($editing_uid, ScotEID_User::ROLE_BPEX);
        $bpex         = ScotEID_User::user_has_role($uid, ScotEID_User::ROLE_BPEX);

        $individual_ids = $this->get_transport_details() && 
    	    $this->get_transport_details()->get_id_type() == ScotEID_TransportDetails::ID_TYPE_INDIVIDUAL_IDS;

        $fieldsman = $editing_uid && ScotEID_User::user_has_role($editing_uid, array(ScotEID_User::ROLE_FIELDSMAN));
      
        if($bpex && !$this->is_saved()) {
        
          // if bpex is creating the lot, set sent to bpex = true
          $this->set_sent_to_bpex(ScotEID_Lot::BPEX_SENT);
        
        } else if($editing_bpex) {
        
          // if bpex is updating a lot, do nothing (if anybody else has 
          // changed it and it's UNSENT, it'll still need to 
          // be resent. if nobody else has changed it, it'll still be SENT)
        
        } else if($individual_ids && !$fieldsman && !$bpex) {
          
          // if the lot contains individual ids and the user isnt a fieldsman or BPEX set to HOLD
          $this->set_sent_to_bpex(ScotEID_Lot::BPEX_HOLD);
         
         } else {
           
           // else reset it to BPEX_UNSENT
          $this->set_sent_to_bpex(ScotEID_Lot::BPEX_UNSENT);
        }
      }
    }
    if($this->get_transport_details()) {
      if(!$this->get_transport_details()->save()) {
        throw new Exception("Couldn't save transport details");
      } else {
        $this->set_transport_details_id($this->get_transport_details()->get_id());
      }
    }
    parent::before_save();
  }

  protected function after_save() {
    parent::after_save();
    $this->create_tag_readings();
    // update batch_mark iterate over tag readings
    if($this->get_species_id ==4) {
      //$this->get_lot_flock_mark();
      
    }
    $flock_marks = $this->get_lot_flock_marks();
    if(count($flock_marks) == 1) {
      $this->set_batch_mark($flock_marks[0]);
    } else if (count($flock_marks) > 1) {
      $this->set_batch_mark("mixed");
    } 
  }
  
  /* Record who is editing a lot if not the user who it belongs to - for creating
	   audit trail */
	
	private $editing_uid = null;
	
	public function set_editing_uid($uid) {
		$this->editing_uid = $uid;
	}
	
	public function get_editing_uid() {
		return $this->editing_uid ? $this->editing_uid : $this->get_uid();
	}
	
  public function is_cross_border() {
    return ($this->get_departure_location() && !$this->get_departure_location()->is_scottish()) ||    
           ($this->get_destination_location() && !$this->get_destination_location()->is_scottish()); 
  }
  
  protected function create_tag_readings() {      
    $t = ScotEID_TagReading::get_lot_table_name($this);
    $this->_load_tag_readings();
    
    dbw_delete($t, $this->get_primary_key());

    if(count($this->get_tag_readings()) > 0) {
      $tag_readings = $this->get_tag_readings();
      $first = $tag_readings[0];
      $fields = implode(',', array_keys($first->quoted_attributes()));

      // now insert
      $sql = "INSERT IGNORE {$t} ($fields) VALUES ";
      foreach($tag_readings as $tag_reading) {
        $values = implode(',', array_values($tag_reading->quoted_attributes()));
        $sql .= " ($values),";
      }
      $sql = rtrim($sql, ",");
      dbw_query($sql);
    }

    if(count($this->get_flock_tags()) > 0) {
      $flock_tags = $this->get_flock_tags();
      $first = $flock_tags[0];
      $fields = implode(',', array_keys($first->quoted_attributes()));

      // insert
      $sql = "INSERT IGNORE {$t} ($fields) VALUES ";
      foreach($flock_tags as $flock_tag) {
        $values = implode(',', array_values($flock_tag->quoted_attributes()));
        $sql .= " ($values),";
      }
      $sql = rtrim($sql, ",");
      dbw_query($sql);
    }
  }
  
  public function get_lot_flock_marks() {  
    $flock_marks = array();
    if(count($this->get_tag_readings()) > 0) {
      $tag_readings = $this->get_tag_readings();
      foreach($tag_readings as $tag_reading) {
        $flock_marks[] = $tag_reading->get_flock_number();
      }
    }
    if(count($this->get_flock_tags()) > 0) {
      $flock_tags = $this->get_flock_tags();
      foreach($flock_tags as $flock_tag) {
        $flock_marks[] = $flock_tag->get_flock_number();
      }
    }
    return array_unique($flock_marks);
  }
  
  public static function get($sams_movement_reference) {
    return static::first(array('conditions' => array(
      'sams_movement_reference' => $sams_movement_reference
    )));
  }
  
  public static function find_dates($uid) {
    $quid = quote_int($uid);
    $sql  = "SELECT DISTINCT LotDate AS `date` FROM " . static::get_table_name() . " WHERE uid = $quid";
    
    $dates = array();
    $result = dbw_query($sql);
    while(($row = dbw_row($result)) != null) {
      $dates[] = strtotime($row['date']);
    }

    return $dates;
  }
  
	// FIXME: full primary key needed here?
	public static function find_specific_lot($lot_number, $lot_date, $uid) {
	  return static::first(array('conditions' => array(
	   'lot_number' => $lot_number,
	   'lot_date'   => $lot_date,
	   'uid'        => $uid
	  )));
	}

  public function load_tag_readings() {
    // deprecated
    trigger_error("load_tag_readings is deprecated", E_USER_WARNING);
  }
  
  public function load_flock_tags() {
    // deprecated
    trigger_error("load_flock_tags is deprecated", E_USER_WARNING);
  }
  
  public static function find_by_uid_and_date($uid, $lot_date) {
    return static::find(array('conditions' => array(
      'uid'       => $uid,
      'lot_date'  => $lot_date
    )));
  }
  
	public static function find_by_holding($holdings, $options = array()) {
	  $h = array_map($holdings, function($holding) { return $holding->getCPH(); });
	  
	  return static::find(
	    array('conditions' => array(
	     array('departure_location.in'   => $h),
	     array('destination_location.in' => $h)
	    ))
	  );
	}

  public static function find_by_sams_movement_reference($sams_movement_reference) {
    return static::first(array(
      'conditions' => array(
        'sams_movement_reference' => $sams_movement_reference
      )
    ));
  }
    
  protected static function nested_assignment_associations() {
	  return array('transport_details');
	}  

  protected static function get_attribute_definitions() {
    return array(
      'sams_movement_reference'             => array('type' => 'serial',  'field' => 'SAMS_Movement_Reference', 'readonly' => true),
      'foreign_reference'                   => array('type' => 'string',  'public' => true),
      'lot_number'                          => array('type' => 'string',  'field' => 'Lot_No',        'public' => true),
      'lot_date'                            => array('type' => 'date',    'field' => 'LotDate',       'public' => true, 'allow_null' => false),
      'departure_date'                      => array('type' => 'date',    'field' => 'DepartureDate', 'public' => true, 'allow_null' => true),
      'arrival_date'                        => array('type' => 'date',    'field' => 'ArrivalDate',   'public' => true, 'allow_null' => true),
      'movement_type_id'                    => array('type' => 'integer', 'field' => 'MovementType',  'public' => true),
      'species_id'                          => array('type' => 'integer', 'field' => 'Species',       'public' => true),
      'filename'                            => array('type' => 'string',  'field' => 'Filename'),
      'read_location'                       => array('type' => 'holding', 'field' => 'ReadLocationCPH', 'public' => true),
      'departure_location'                  => array('type' => 'holding', 'field' => 'DepartureCPH',    'public' => true),
      'destination_location'                => array('type' => 'holding', 'field' => 'DestinationCPH',  'public' => true),
      'head_count'                          => array('type' => 'integer', 'field' => 'Qty_Sheep',       'public' => true),
      'doa_count'                           => array('type' => 'integer', 'field' => 'Qty_DOA',         'public' => true),
      'read_count'                          => array('type' => 'integer', 'field' => 'Qty_Reads', 'readonly' => true),
      'all_eid'                             => array('type' => 'boolean', 'field' => 'AllEID',          'public' => true),
      'visually_read'                       => array('type' => 'boolean', 'field' => 'Visually_read',   'public' => true),
      'uid'                                 => array('type' => 'integer', 'field' => 'UID'),
      'departure_keeper_flock_number'       => array('type' => 'integer', 'field' => 'Dep_Keeper_Flock_No', 'public' => true),
      'destination_keeper_flock_number'     => array('type' => 'integer', 'field' => 'Dest_Keeper_Flock_No', 'public' => true),
      'reader_lot_number'                   => array('type' => 'string',  'field' => 'Reader_Lot_No'),
      'buyer_invoice_number'                => array('type' => 'integer', 'field' => 'Buyer_Inv_No',        'public' => true),
      'seller_payment_number'               => array('type' => 'integer', 'field' => 'Seller_Pay_No',       'public' => true),
      'seller_reference'                    => array('type' => 'string',  'field' => 'Seller_Acc_No',       'public' => true),
      'buyer_reference'                     => array('type' => 'string',  'field' => 'Buyer_Acc_No',        'public' => true),
      'transport_details_id'                => array('type' => 'integer'),
      'description_id'                      => array('type' => 'integer', 'public' => true),
      'batch_mark'                          => array('type' => 'string',  'public' => true),
      'fci_declaration'                     => array('type' => 'integer', 'public' => true),
      'receiving_keeper_id'                 => array('type' => 'integer'),
      'bpex_movement_type_id'               => array('type' => 'integer', 'public' => true),
      'sent_to_bpex'                        => array('type' => 'integer', 'default' => ScotEID_Lot::BPEX_UNSENT)
    );
  }
  
  public function update($lot, $ignore = array()) {	
    foreach(array_values($this->property_map()) as $property) {
      $getter = "get_$property";
      $setter = "set_$property";
      
      if(in_array($property, $ignore)) {
        continue;
      }
    
      $v = $lot->$getter();

      if($v !== null) {
        if($this->can_update_property($property)) {
          $this->$setter($v);
        } else {
          // TODO:
          // in a future schema version we may want to throw a security fault
          // if the user can't update this attribute
        }
      }
    }

    $n = $lot->get_reader_lot_number();
    if(!empty($n)) {
    	$this->set_reader_lot_number($n);
    }

    $uid = $lot->get_uid();
    if($uid !== null) {
      $this->set_uid($uid);
    }
  }
  
  // used to check whether the update() function above is allowed to change a particular
  // attribute on a lot - not used anywhere else at present
  //
  // it's worth noting that at this point, in principal, the editing user is allowed
  // to make modifications to the lot - so none of those type of checks need to be
  // made here
  
  public function can_update_property($attribute) {
    return true;
  }
  
  public function complete() {
    dbw_begin();
    
    try {
            
      $completed_lot = new ScotEID_CompletedLot($this);

      if(!$completed_lot->save()) {
        dbw_rollback();
        return $completed_lot;
      }

      if($this->is_saved()) {
        dbw_delete(static::get_table_name(), array(
          'SAMS_Movement_Reference' => quote_int($this->get_sams_movement_reference()))
        );
      }
      
      dbw_commit();    
    } catch(Exception $ex) {
      dbw_rollback();
      throw $ex;
    }

    return $completed_lot;
  }
  
  public function merge($lot) {
    dbw_begin();
    try {
      // TODO: make this more efficient

      //
      // Tag readings
      foreach($lot->get_tag_readings() as $r) {
        $f = false;        
        foreach($this->get_tag_readings() as $r2) { 
          if($r->is_eid() && $r2->is_eid()) {
            if($r->get_tag_id()->equals($r2->get_tag_id())) {            
              $f = true;
              break;
            }
          }
        }
        if(!$f) {
          $this->add_tag_reading($r);
        }
      }

      // 
      // Flock tags
      foreach($lot->get_flock_tags() as $t) {
        $f = false;
        foreach($this->get_flock_tags() as $t2) {
          if($t->get_flock_number() == $t2->get_flock_number()) {
            $f = true;
            $t2->set_tag_count($t->get_tag_count() + $t2->get_tag_count());
            break;
          }
        }
        if(!$f) {
          $this->add_flock_tag($t);
        }
      }

      $this->save();
      $lot->delete();
      dbw_commit();
      return true;
    } catch(Exception $ex) {      
      dbw_rollback();
      throw $ex;
    }
  }
  
  public function split_at($split_at) {
		// FIXME: add species_id to get_incomplete_sub_lot_number
    dbw_begin();
    try {      
      $new_lot = new ScotEID_Lot();
      $new_lot->update($this, array('flock_tags', 'tag_readings'));
      $new_lot->set_lot_number(ScotEID_Utils::get_incomplete_sub_lot_number(
        $this->get_lot_date(), 
        $this->get_lot_number(),
        $this->get_uid()));

      foreach($this->get_tag_readings() as $tag_reading) {
        if($tag_reading->get_timestamp() && $tag_reading->get_timestamp() >= $split_at) {
          $this->remove_tag_reading($tag_reading);
          $new_lot->add_tag_reading($tag_reading);
        }
      }

      if(!$this->save()) {
        throw new Exception("Unknown error");
      }
      if(!$new_lot->save()) {
        throw new Exception("Unknown error");
      }
      
      dbw_commit();
      
      return $new_lot;
    } catch(Exception $ex) {
      dbw_rollback();
      throw $ex;
    }
  }
  
  public function split_tag_readings($tag_readings) {
		// FIXME: add species_id to get_incomplete_sub_lot_number
    dbw_begin();
    try {      
      $new_lot = new ScotEID_Lot();
      $new_lot->update($this, array('tag_readings', 'flock_tags'));
      $new_lot->set_lot_number(ScotEID_Utils::get_incomplete_sub_lot_number(
        $this->get_lot_date(), 
        $this->get_lot_number(),
        $this->get_uid()));
      
      foreach($tag_readings as $tag_reading) {
        $this->remove_tag_reading($tag_reading);
        $new_lot->add_tag_reading($tag_reading);
      }
      
      if(!$this->save()) {
        throw new Exception("Unknown error");
      }
      if(!$new_lot->save()) {
        throw new Exception("Unknown error");
      }
      
      dbw_commit();
      
      return $new_lot;
    } catch(Exception $ex) {
      dbw_rollback();
      throw $ex;
    }
  }
  
  //
  // Property setters and getters

	public function set_transport_details($tr) {
	  if($tr != null || $this->get_transport_details()) {
	    if(is_array($tr)) {
	      $this->get_or_create_transport_details()->extract_public_from($tr);
      } else {
	      $this->get_or_create_transport_details()->update($tr);
      }
      $this->get_transport_details()->set_lot($this);
	  }
	}

  public function set_departure_keeper($keeper) {
    $this->get_or_create_transport_details()->set_departure_keeper($keeper);
    if($keeper && $keeper->get_flock_number()) {
      $this->set_departure_keeper_flock_number($keeper->get_flock_number());
    } else {
      $this->set_departure_keeper_flock_number(null);
    }
  }
  
  public function set_destination_keeper($keeper) {
    $this->get_or_create_transport_details()->set_destination_keeper($keeper);
    if($keeper && $keeper->get_flock_number()) {
      $this->set_destination_keeper_flock_number($keeper->get_flock_number());
    } else {
      $this->set_destination_keeper_flock_number(null);
    }
  }

  public function set_tag_readings($tag_readings) { 
    // HACK - something weird happens when these objects come from SOAP, not sure
    // why they are instances of stdClass instead of arrays but this is how we deal
    // with it for now
    if($tag_readings) {
        if($tag_readings instanceof stdClass) {
          $tag_readings = $tag_readings->TagReading;
        }
        foreach($tag_readings as $tag_reading) {
          $tag_reading->set_lot($this);
        }
    }
    $this->tag_readings = $tag_readings; 
  }
  
  public function set_flock_tags($flock_tags) {
    if($flock_tags) {
        if($flock_tags instanceof stdClass) {
          $flock_tags = $flock_tags->FlockTag;
        }
        foreach($flock_tags as $flock_tag) {
          $flock_tag->set_lot($this);
        }
    }
    $this->flock_tags = $flock_tags;
  }
  
  public function set_pig_identities($pig_identities) {
    $d = null;
    if(is_array($pig_identities)) {
      $d = $pig_identities;
    } else if($pig_identities) {
      if($pig_identities) {
        $d = array_map(function($v) {
              return $v->Tag;
             }, $pig_identities->PigIdentity);
      }
    }
       
    if(count($d) > 0) {
      $this->get_or_create_transport_details()->set_individual_ids(implode(",", $d));
    }
  }
  
  public function add_tag_reading($tag_reading) {
    $this->_load_tag_readings();
    $tag_reading->set_lot($this);
    $this->tag_readings[] = $tag_reading;
  }
  
  function clear_tag_readings() {
    $this->tag_readings = array();
  }
  
  function clear_flock_tags() {
    $this->flock_tags = array();
  }
  
  public function add_flock_tag($flock_tag) {
    $this->_load_tag_readings();
    $flock_tag->set_lot($this);
    $this->flock_tags[] = $flock_tag;
  }
  
  public function remove_tag_reading($tag_reading) {
    $this->_load_tag_readings();
    if(in_array($tag_reading, $this->get_tag_readings())) {
      $tag_reading->set_lot(null);
      foreach($this->tag_readings as $key => $value) {
        if($value == $tag_reading) unset($this->tag_readings[$key]);
      }
      $this->tag_readings = array_values($this->tag_readings);
      if(!$this->tag_readings) {
        $this->tag_readings = array();
      }
    }
  }
  
  public function set_fci($fci) {
    if($fci) {
      $this->set_fci_declaration($fci->get_withdrawal_periods_met());
    } else {
      $this->set_fci_declaration(null);
    }
  }
  
  public function get_movement_type() { 
		if($this->get_movement_type_id() && $this->movement_type == null) {
			$this->movement_type = ScotEID_MovementType::get($this->get_movement_type_id());
		}
		return $this->movement_type;
	}
	
	public function get_bpex_movement_type() {
	  if($this->get_bpex_movement_type_id()) {
	    return ScotEID_BPEXMovementType::first(array('conditions' => array('id' => $this->get_bpex_movement_type_id())));
	  } else {
	    return null;
	  }
	}
		
	public function get_species() {
		if($this->get_species_id() && $this->species == null) {
			$this->species = ScotEID_Species::get($this->get_species_id());
		}
		return $this->species;
	}
	
	// FIXME: need eager loading :(
	public function get_or_create_transport_details() {
    if(!$this->transport_details) {
      if($this->get_transport_details_id()) { 
        $this->transport_details = ScotEID_TransportDetails::get($this->get_transport_details_id());
      } else {
        $this->transport_details = new ScotEID_TransportDetails();
      }
      $this->get_transport_details()->set_lot($this);
    }
    return $this->transport_details;
	}
	public function get_transport_details() {
	  if(!$this->transport_details && $this->get_transport_details_id()) {
      $this->transport_details = ScotEID_TransportDetails::get($this->get_transport_details_id());
      $this->get_transport_details()->set_lot($this);
    }
    return $this->transport_details;
	}


  public function get_read_count() {
    $r = $this->_get_attribute("read_count");
    if($r === null) {
      return count($this->get_tag_readings());
    } else {
      return $r;
    }
  }

  public function get_read_percent() { 
    if(min($this->get_read_count(), $this->get_head_count()) == 0) {
      return 0;
    } else {
      return round(($this->get_read_count() / $this->get_head_count()) * 100); 
    }
  }
    
  private function _load_tag_readings() {
    if($this->is_saved()) {
      if($this->tag_readings === null)
        $this->set_tag_readings(ScotEID_TagReading::find_by_lot($this));
      if($this->flock_tags === null)
        $this->set_flock_tags(ScotEID_FlockTag::find_by_lot($this));
    } else {
      if($this->tag_readings === null)  
        $this->set_tag_readings(array());
      if($this->flock_tags === null)
        $this->set_flock_tags(array());
    }
  }  
    
  public function get_tag_readings() { 
    $this->_load_tag_readings();
    return $this->tag_readings; 
  }
  
  public function get_flock_tags() { 
    $this->_load_tag_readings();
    return $this->flock_tags; 
  }
  
  public function get_pig_identities() {
    if(!$this->get_transport_details()) {
      return null;
    }
    
    $ids = $this->get_transport_details()->get_individual_ids();
    
    if(empty($ids)) {
      return null;
    }
    
    $split = explode(",", $this->get_transport_details()->get_individual_ids());    
    if(count($split) > 0) {
      $r = array();
      foreach($split as $s) {
        if(!empty($s))
          $r[] = array('Tag' => $s);
      }
      return $r;
    }
    return null;
  }
  
  public function get_description() { 
		if($this->get_description_id() && $this->description == null) {
			$this->description = ScotEID_LotDescription::get($this->get_description_id());
		}
		return $this->description;
	}
  
  public function get_fci() {
    $fci = new ScotEID_FCI();
    if($this->get_fci_declaration() !== null) {
      $fci->set_withdrawal_periods_met($this->get_fci_declaration());
      return $fci;
    } else {
      return null;
    }
  }

  public function get_departure_keeper() {
    if(!$this->get_transport_details())
      return null;
    
    $k = $this->get_transport_details()->get_departure_keeper();
    if($k != null) {
      if($this->get_departure_keeper_flock_number()) {
        $k->set_flock_number($this->get_departure_keeper_flock_number());
      }
    }
    return $k;
  }
  
  public function get_destination_keeper() {
    if(!$this->get_transport_details())
      return null;
      
    $k = $this->get_transport_details()->get_destination_keeper();
    if($k != null) {
      if($this->get_destination_keeper_flock_number()) {
        $k->set_flock_number($this->get_destination_keeper_flock_number());
      }
    }
    return $k;
  }
  
 	protected function after_save_on_update() {
	  parent::after_save_on_update();
	}
	
	/* HACK - eagerly load holdings for a collection of lots */
	
	public static function load_holdings($lots, $tag_readings = true) {
		$cph = array();
		foreach($lots as $lot) {
		  if($lot->get_departure_location())
  			$cph[] = $lot->get_departure_location()->getCPH();
			if($lot->get_read_location())
  			$cph[] = $lot->get_read_location()->getCPH();
  		if($lot->get_destination_location())
  			$cph[] = $lot->get_destination_location()->getCPH();
  		if($tag_readings) {
  		  foreach($lot->get_tag_readings() as $tag_reading) {
  		    $animal = $tag_reading->get_animal();
  		    if($animal && $animal->get_last_read_location())
  		      $cph[] = $animal->get_last_read_location()->getCPH();
  		    if($animal && $animal->get_last_destination_location())  
  		      $cph[] = $animal->get_last_destination_location()->getCPH();
  		  }
  		}
		}
		$cph = array_unique($cph);
		if(count($cph) > 0) {
  		$holdings = ScotEID_Holding::find(array(
  			'conditions' => array(
  				'cph.in' => $cph
  			)
  		));
  		$map = array();
  		foreach($holdings as $holding) {
  			$map[$holding->getCPH()] = $holding;
  		}
  		foreach($lots as $lot) {
  			if($lot->get_departure_location() && $map[$lot->get_departure_location()->getCPH()])
  				$lot->set_departure_location($map[$lot->get_departure_location()->getCPH()]);
  			if($lot->get_read_location() && $map[$lot->get_read_location()->getCPH()])
  				$lot->set_read_location($map[$lot->get_read_location()->getCPH()]);
  			if($lot->get_destination_location() && $map[$lot->get_destination_location()->getCPH()])
  				$lot->set_destination_location($map[$lot->get_destination_location()->getCPH()]);
  		  if($tag_readings) {
  		    foreach($lot->get_tag_readings() as $tag_reading) {
  		      $animal = $tag_reading->get_animal();
  		      if($animal && $animal->get_last_read_location() && $map[$animal->get_last_read_location()->getCPH()])
  		        $animal->set_last_read_location($map[$animal->get_last_read_location()->getCPH()]);
  		      if($animal && $animal->get_last_destination_location() && $map[$animal->get_last_destination_location()->getCPH()])
  		        $animal->set_last_destination_location($map[$animal->get_last_destination_location()->getCPH()]);  
  		    }
  		  }
  		}
		}
	}
	
	public function is_editable_by($uid) {
	  return ($uid == $this->get_uid() || ScotEID_User::user_has_role($uid, array('SuperUser', 'Fieldsman')));
	}	
	
	public function is_downloadable_by($uid) {
	  return $uid == $this->get_uid() || ScotEID_User::user_has_role($uid, array('SuperUser', 'Fieldsman', 'RPID_user', 'Local_Auth'));
	}
	
	public function is_moveable_by($uid) {
	  return (
	    ($uid == $this->get_uid() || ScotEID_User::user_has_role($uid, array('SuperUser', 'Fieldsman'))) && 
	    $this->get_movement_type_id() != 5 && 
	    $this->get_movement_type_id() != 8);
	}
	
	public function get_last_destination_summary($exclude = array()) {
	  $r = array();
	  foreach($this->get_tag_readings() as $tag_reading) {
      $loc = $tag_reading->get_animal() ? $tag_reading->get_animal()->get_last_destination_location() : null;
      if($loc && in_array($loc, $exclude)){
        $loc = $tag_reading->get_animal()->get_last_read_location();
      }
      if($loc) {
        $cph = $loc->getCPH();
        if(isset($r[$cph])) {
          $r[$cph] += 1;
        } else {
          $r[$cph] = 1;
        }
      }
	  }
	  return $r;
	}
	
	public function get_flock_number_summary() {
	  $r = array();
	  foreach($this->get_tag_readings() as $tag_reading) {
      $fn = $tag_reading->get_flock_number();
      if(isset($r[$fn])) {
        $r[$fn] += 1;
      } else {
        $r[$fn] = 1;
      }
	  }
	  foreach($this->get_flock_tags() as $flock_tag) {
	    $fn = $flock_tag->get_flock_number();
      if($fn) {
        if(isset($r[$fn])) {
          $r[$fn] += $fn->get_tag_count();
        } else {
          $r[$fn] = $fn->get_tag_count();
        }
      }
	  }
	  return $r;
  }

	public function get_flock_tag_summary() {	  
		$qloc    = quote_str($this->get_read_location()->getCPH());
		$qdate   = quote_date($this->get_lot_date());
		$qlot    = quote_str($this->get_lot_number());
		$qhidden = quote_int(ScotEID_MovementType::MOVEMENT_TYPE_HIDDEN);

		$sql = "SELECT
			l.LotDate AS lot_date,
			LEFT(r.AnimalEID, 6) AS flock_number,
			SUM(Tag_count) AS tag_count
		 FROM
		 	tblsslots l
			INNER JOIN tblsheepitemread r ON
    		l.LotDate = r.LotDate AND
    		l.Lot_No  = r.Lot_No AND
    		l.ReadLocationCPH = r.ReadLocationCPH AND
    		l.MovementTYpe = r.MovementType
		WHERE
			r.Country_code = 826 AND
			l.ReadLocationCPH = $qloc AND
			l.LotDate = $qdate AND
			l.Lot_No = $qlot AND
      l.MovementType <> $qhidden
		GROUP BY
			flock_number, r.`LotDate`";

		$summary = array();	
			
		$res = dbw_query($sql);
		while($row = dbw_row($res)) {
			$flock_tag = new ScotEID_FlockTag();
			$flock_tag->set_tag_count($row['tag_count']);
			$flock_tag->set_flock_number($row['flock_number']);
			$summary[] = $flock_tag;
		}
		
		return $summary;
	}
	
	public static function get_movement_summary($lot_date, $departure_location, $destination_location, $within_days) {
		$qdep  = quote_str($departure_location->getCPH());
		$qdest = quote_str($destination_location->getCPH());
		$qdate = quote_date($lot_date);
		$qfuzz = quote_int($within_days);
		
		$sql = "SELECT
			l.LotDate AS lot_date,
			LEFT(r.AnimalEID, 6) AS flock_number,
			SUM(Tag_count) AS tag_count
		 FROM
		 	tblsslots l
			LEFT JOIN tblsheepitemread r ON
    		l.LotDate = r.LotDate AND
    		l.Lot_No  = r.Lot_No AND
    		l.ReadLocationCPH = r.ReadLocationCPH AND
    		l.MovementTYpe = r.MovementType
		WHERE
			(r.Country_code IS NULL OR r.Country_code = 826) AND
			l.DepartureCPH = $qdep AND
			l.DestinationCPH = $qdest AND
			l.LotDate BETWEEN DATE_SUB($qdate, INTERVAL $qfuzz DAY) AND DATE_ADD($qdate, INTERVAL $qfuzz DAY) AND
			l.MovementType = 8
		GROUP BY
			flock_number, r.`LotDate`";

		$summary = array();	
			
		$res = dbw_query($sql);
		while($row = dbw_row($res)) {
			if(array_key_exists($row['lot_date'], $summary)) {
				$s = $summary[$row['lot_date']];
			} else {
				$s = array();
			}			
			$flock_tag = new ScotEID_FlockTag();
			$flock_tag->set_tag_count($row['tag_count']);
			$flock_tag->set_flock_number($row['flock_number']);
			$s[] = $flock_tag;
			$summary[$row['lot_date']] = $s;
		}
				
		return $summary;
	}
}
?>

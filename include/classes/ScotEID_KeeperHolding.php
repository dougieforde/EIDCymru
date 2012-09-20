<?php
class ScotEID_KeeperHolding extends ScotEID_ExtendedModel
{
  private static $validate_ccp_cph = true;
  
  public static function set_validate_ccp_cph($b) {
    static::$validate_ccp_cph = $b;
  }
  
  public static function get_validate_ccp_cph() {
    return static::$validate_ccp_cph;
  }
  
  public static function get_table_name() {
		return "keeper_holdings";
	}
	
	public function get_primary_key() {
		return array(
			'uid' => quote_int($this->get_uid()),
			'cph' => quote_str($this->get_cph())
		);
	}
	
	protected static function get_attribute_definitions() {
	  return array(
	    'uid'                 => array('type' => 'int'),
	    'cph'                 => array('type' => 'holding', 'public' => true),
	    'resident'            => array('type' => 'boolean', 'public' => true, 'default' => false),
	    'pigs'                => array('type' => 'int',     'public' => true),
	    'pig_herd_number'     => array('type' => 'string',  'public' => true),
	    'pig_slap_mark'       => array('type' => 'string',  'public' => true),
	    'pig_slap_mark_2'     => array('type' => 'string',  'public' => true),
	    'sheep'               => array('type' => 'int',     'public' => true),
	    'sheep_flock_number'  => array('type' => 'int',     'public' => true),
	    'cattle'              => array('type' => 'int',     'public' => true),
	    'cattle_herd_number'  => array('type' => 'string',  'public' => true)
	  );
	}

  protected function get_validations() {
    return array(
      new ScotEID_PatternValidation('cph', '/\d{2}\/\d{3}\/\d{4}/', array('error' => '^CPH should be of the form CC/PPP/HHHH')),
      new ScotEID_PresenceValidation('pig_herd_number', array(
        'if'    => function($o) { return $o->get_pigs() > 0; },
        'error' => 'must be specified if pigs > 0'
      )),
      new ScotEID_NumericValidation('sheep_flock_number', array(
        'if'    => function($o) { return $o->get_sheep() > 0; },
        'error' => 'cannot be blank if sheep > 0 and must be a Scottish flock number',
        'min'   => 500000,
		'max'   => 600000
      )),
      new ScotEID_PatternValidation('cph', '/\d{2}\/\d{3}\/(?!(7|8))/', array('error' => '^CPH cannot end with 7000 or 8000', 'if' => function($instance) {
        return ScotEID_KeeperHolding::get_validate_ccp_cph();
      })),
      new ScotEID_CustomValidation('validate_ccppp', array('if' => function($i) {
        return ScotEID_KeeperHolding::get_validate_ccp_cph();
      })),
      new ScotEID_CustomValidation('validate_herd_number_present'),
      new ScotEID_PatternValidation('pig_herd_number', '/^[A-Z]{1,2}[0-9]{4,4}$/', array(
        'if' => function($i) {
          $h = $i->get_pig_herd_number();
          return !empty($h);
        },
        'error' => 'should be of the form AB1234'
      ))
    );
  }
  
  public function validate_ccppp() {
    if($this->get_cph()) {
      if(!$this->get_cph()->is_county_parish_valid()) {
        $this->add_error("cph", "^CPH has an invalid county/parish combination - please contact <a href='mailto:help@scoteid.com'>help@scoteid.com</a> if you believe the CPH you have entered is correct");
      }
      if(!$this->get_cph()->is_scottish()) {
        $this->add_error("cph", "^CPH is not Scottish");
      }
    }
  }
  
  public function validate_herd_number_present() {
    $flock_number = $this->get_sheep_flock_number();
    $herd_number  = $this->get_pig_herd_number();
    
    if(!empty($flock_number)) $flock_number = trim($flock_number);
    if(!empty($herd_number)) $herd_number   = trim($herd_number);
    
    if(empty($flock_number) && empty($herd_number)) {
      $this->add_error("flock_number", "^Either a flock number, herdmark or both must be specified for each holding");
    }
  }
}
?>
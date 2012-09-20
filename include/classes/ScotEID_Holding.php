<?php
class ScotEID_Holding extends ScotEID_ExtendedModel
{   	
	private static $ccppp = array();
	
	const LOCATION_TYPE_ABATTOIR            = 'abattoir';
	const LOCATION_TYPE_COLLECTION_CENTRE   = 'collection centre';
	const LOCATION_TYPE_EU_IMPORT_EXPORT    = 'eu import/export';
	const LOCATION_TYPE_FARM                = 'farm';
	const LOCATION_TYPE_FERRY               = 'ferry';
	const LOCATION_TYPE_KNACKERY            = 'knackery';
	const LOCATION_TYPE_LAIRAGE             = 'lairage';
	const LOCATION_TYPE_MART                = 'mart';
	const LOCATION_TYPE_SHOW                = 'show';
	const LOCATION_TYPE_VI_UNIT             = 'vi unit'; 
  
  public function __construct($cph = '') {
    $this->set_cph(trim($cph));
  }
  
  public function to_property_array($skip = array()) {
    return $this->getCPH();
  }
  
	public static function get_table_name() {
		return "tblsamholdings";
	}
	
	public function get_primary_key() {
		return array(
			'CPH' => quote_str($this->getCPH())
		);
	}
	
	protected static function get_attribute_definitions() {
	  return array(
	    'cph'           => array('type' => 'string',    'field' => 'CPH'),
	    'name'          => array('type' => 'string',    'field' => 'Name'),
	    'business'      => array('type' => 'string',    'field' => 'Business'),
	    'address_2'     => array('type' => 'string',    'field' => 'Address2'),
	    'address_3'     => array('type' => 'string',    'field' => 'Address3'),
	    'address_4'     => array('type' => 'string',    'field' => 'Address4'),
	    'postcode'      => array('type' => 'string',    'field' => 'Postcode'),
	    'telephone'      => array('type' => 'string',    'field' => 'Business_Telephone'),
	    'website'       => array('type' => 'string',    'field' => 'Business_Website'),
	    'location_type' => array('type' => 'string',    'field' => 'LocationType'),
	    'scottish'      => array('type' => 'boolean',   'field' => 'Scottish'),
	    'cattle'        => array('type' => 'boolean',   'field' => 'cattle'),
	    'sheep'         => array('type' => 'boolean',   'field' => 'sheep'),
	    'pigs'          => array('type' => 'integer',   'field' => 'pigs'),
	    'abattoir_number' => array('type' => 'integer', 'field' => 'abattoirno')
	  );
	}
	
  public function getCPH() {
  	return $this->get_cph();
	}
  
  public function setCPH($cph) {
		return $this->set_cph($cph);
  }
  
  public function set_cph($cph) {
    if(strlen($cph) == 11 && substr($cph, -4, 2) <= 65) {
      $this->set_scottish(true);
    } else {
      $this->set_scottish(false);
    }
    $this->_set_attribute("cph", $cph);
  }

	public function is_mart() {
	  return  (strcasecmp($this->get_location_type(), 'mart')==0);
	}
	
	public function is_abattoir() {
    return (strcasecmp($this->get_location_type(), 'abattoir')==0);
	}
	
  public function __toString() {
    return $this->getCPH();
  }
  
  public function valid() {
    return !! preg_match('/(^[0-9]{2}\/[0-9]{3}\/[0-9]{4}$)/',$this->getCPH());
  }

	/* DEPRECATED */
  public function get_name_and_address() {
    $cph = $this->get_cph();
    if(empty($cph)) {
      return "";
    } else {
      $qcph = quote_str($cph);
      $res = dbw_query("SELECT Name,Business FROM " . self::get_table_name() . " WHERE CPH = $qcph");
      $r = "";
      if($row = dbw_row($res)) {
        $name     = $row['Name'];
        $business = $row['Business'];
              
        if(!empty($name)) {
          $r .= $name;
        }
        if(!empty($name) && !empty($business)) {
          $r .= ", ";
        }
        if(!empty($business)) {
          $r .= $business;
        }
      }
      return $r;
    }
  }

  public static function try_parse($holding) {
    if(preg_match('/99\/000\/([0-9]{4,4})/', $holding, $matches) != 0) {
      if($h = self::find_cph_by_mhs_number($matches[1])) {
        return new ScotEID_Holding($h);
      } else {
        return new ScotEID_Holding($holding);
      }
    } else if(preg_match('/^[0-9]{4,4}$/', $holding) != 0) {
      if($h = self::find_cph_by_mhs_number($holding)) {
        return new ScotEID_Holding($h);
      } else {
        return new ScotEID_Holding("99/000/$holding");
      }
    } else if(preg_match('/^[0-9]{2,2}\/[0-9]{3,3}\/[0-9]{4,4}$/', $holding) != 0) {
      return new ScotEID_Holding($holding);
    } else {
      return new ScotEID_Holding('');
    }
  }
  
  private static function find_cph_by_mhs_number($mhs) {
    $mhs_quoted = quote_int($mhs);
    try {
      $result = dbw_query("SELECT CPH FROM " . self::get_table_name() . " WHERE AbattoirNo = $mhs_quoted");
      if($row = dbw_row($result)) {
        return $row["CPH"];
      }
      return null;
    } catch(Exception $ex) {
      return null;
    }
  }

	public function to_array() {
		return array(
			'cph' 									 => $this->get_cph(),
      // 'name'                    => $this->get_name(),
			'business'							 => $this->get_business(),
			'address_2'							 => $this->get_address_2(),
			'address_3'							 => $this->get_address_3(),
			'address_4'							 => $this->get_address_4(),
      'postcode'              => $this->get_postcode(),
      // 'telephone'              => $this->get_telephone(),      
			'location_type'          => $this->get_location_type(),
			'is_county_parish_valid' => $this->is_county_parish_valid(),
			'is_scottish'            => $this->is_scottish(),
			'is_landless_keeper'     => $this->is_landless_keeper()
		);
	}
	
	/**
	 * County/Parish data queries start here 
	 */
	
	public function is_county_parish_valid() {
		if($this->valid()) {
			$ccppp = static::get_ccppp();
			$parts = explode('/', $this->getCPH());
			if(isset($ccppp[$parts[0]]) && in_array($parts[1], $ccppp[$parts[0]])) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	public function is_landless_keeper() {
	  if($this->valid()) {
      if(preg_match('/[0-9]{2,2}\/[0-9]{3,3}\/7[0-9]{3,3}/', $this->getCPH(), $matches) != 0) {
        return true;
      }
	  }
	  return false;
	}
	
	public function is_scottish() {
	  if($this->valid()) {
	    $cc = substr($this->getCPH(), 0, 2);
	    if(((int)$cc) > 65 && ((int)$cc) <99) {
	      return true;
	    }
	  }
	  return false;
	}
	
	public static function get_ccppp() {
		if(self::$ccppp == null) {
			$r   = array();
			$res = dbw_query("SELECT CC, PPP FROM ccppp_names");
			while($row = dbw_row($res)) {
				if(!isset($r[$row['CC']])) {
					$r[$row['CC']] = array();
				}
				$r[$row['CC']][] = $row['PPP'];
			}
			self::$ccppp = $r;
		}
		return self::$ccppp;
	}
	
	public static function get_county_names($ccs) {
		$qcc = quote_str($ccs . "%");
		$sql = "SELECT DISTINCT CC, County FROM ccppp_names WHERE CC LIKE $qcc";
		$r = array();
		$res = dbw_query($sql);
		while($row = dbw_row($res)) {
			$r[] = array(
				'cc'  	 => $row['CC'],
				'county' => $row['County']
			);
		}
		return $r;
	}
	
	public static function get_parish_names($county, $parish) {
		$qcc  = quote_int($county);
		$qppp = quote_str($parish . "%");
		$sql  = "SELECT CC, PPP, County, Parish FROM ccppp_names WHERE CC = $qcc AND PPP LIKE $qppp";
		$res = dbw_query($sql);
		while($row = dbw_row($res)) {
			$r[] = array(
				'cc'     => $row['CC'],
				'ppp'    => $row['PPP'],
				'county' => $row['County'],
				'parish' => $row['Parish']
			);
		}
		return $r;
	}
	
  public static function get_parish_map_url($county, $parish) {
    $qcc  = quote_int($county);
    $qppp = quote_int($parish);
    $sql  = "SELECT CC, PPP, County, Parish,map_url FROM ccppp_names WHERE CC = $qcc AND PPP = $qppp";
    $res = dbw_query($sql);
    while($row = dbw_row($res)) {
      $r[] = array(
        'cc'     => $row['CC'],
        'ppp'    => $row['PPP'],
        'county' => $row['County'],
        'parish' => $row['Parish'],
        'map_url' => $row['map_url']
      );
    }
    return $r;
  }	
}
?>
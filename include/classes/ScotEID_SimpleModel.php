<?php
class ScotEID_SimpleModel
{
    private $metadata = array();
  
    //
    // Magic property setters and getters for unmarshalling
    
    /* Return an assoc array specifying the internal properties to
     * map an assignment to, e.g.
     * 'LotNumber' => 'lot_number' would cause $lot->LotNumber = '1' to
     * assign $lot->lot_number. */
    protected function property_map() { return array(); }

    public function __set($name, $value) {
      $map = $this->property_map();      
      if(array_key_exists($name, $map)) {        
        $method = "set_" . $map[$name];
        $this->$method($value);        
      }
    }

    public function __get($name) {
      $map = $this->property_map();
      if(array_key_exists($name, $map)) {
        $method = "get_" . $map[$name];
        return $this->$method();
      }
      return null;
    }

    public function __call($name, $arguments) {
      if(preg_match("/^get_(.*)$/", $name, $matches)) {
        if(array_key_exists($matches[1], $this->metadata)) {
          return $this->metadata[$matches[1]];
        }
      }
      if(SCOTEID_WEBSERVICES_ENV != 'production') {
    		trigger_error("Call to undefined method $name()", E_USER_ERROR);
      } else {
        return false;
      }
    }

    public function to_property_array($skip = array(), $only = null) {
      $r = array();
      foreach($this->property_map() as $property => $attribute) {
        if(in_array($attribute, $skip)) {
          continue;
        }
        if($only && !in_array($attribute, $only)) {
          continue;
        }
        $v = $this->get_attribute($attribute);
        if(is_object($v)) {
          $v = $v->to_property_array();
          if(count($v) == 0) {
            $v = null;
          }
        }
        if($v === false || $v === 0 || !empty($v)) {
          $r[$property] = $v;
        }
      }
      return $r;
    }
    
    public function extract_properties($map) {
      foreach($this->property_map() as $property => $attribute) {
        if(isset($map->$property)) {
          $this->set_attribute($attribute, $map->$property);
        } else if(is_array($map) && isset($map[$property])) {
          $this->set_attribute($attribute, $map[$property]);
        }
      }
    }
    
    public function extract_all_from($a) {
        $this->extract_public_from($a);
        $this->extract_private_from($a);
        $this->extract_metadata_from($a);
    }

    public function extract_public_from($d) {
			$this->extract_attributes(array_merge($this->nested_assignment_associations(), $this->public_attributes()), $d);
		}
		
    public function extract_private_from($d) {
			$all = array_values($this->attribute_map());
			$this->extract_attributes(array_diff($all, $this->public_attributes()), $d);
		}
		
		public function extract_metadata_from($d) {
		  $all = array_merge(array_values($this->attribute_map()), array_keys($this->attribute_map()));
		  foreach($d as $k => $v) {
		    if(!in_array($k, $all)) {
		      $this->metadata[$k] = $v;
		    }
		  }
		}

    protected static function attribute_map() { return array(); }

    protected static function public_attributes() { return array(); }
    protected static function nested_assignment_associations() { return array(); }

    protected function extract_attributes($as, $d) {
        $map = array_flip(static::attribute_map());
        $d   = array_change_key_case($d, CASE_LOWER);

        foreach ($as as $a) {
            $t = $a;

            if(array_key_exists($a, $map)) { $t = $map[$a]; }

            // HACK: all of these attribute maps should just be changed to work in
            // lower case!
            $t = strtolower($t);

						// check if an attribute alias exists and use that to set
            if (array_key_exists($t, $d)) {
                $method = "set_{$a}";
                $this->$method($d[$t]);
            }
						// now check for the untranslated name
						else if (array_key_exists($a, $d)) 
						{
								$method = "set_{$a}";
								$this->$method($d[$a]);
						}
        }
    }
}
?>
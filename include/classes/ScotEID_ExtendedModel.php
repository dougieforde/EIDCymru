<?php
abstract class ScotEID_ExtendedModel extends ScotEID_Model
{ 
  /** Class level variables to get around inheritance problem with static variables */
  
  protected static $_class_vars = array();
  
  protected static function get_class_var($var) {
    $class_name = get_called_class();
    if(isset(static::$_class_vars[$class_name]) && isset(static::$_class_vars[$class_name][$var])) {
      return static::$_class_vars[$class_name][$var];
    } else {
      return null;
    }
  }
  
  protected static function set_class_var($var, $value) {
    $class_name = get_called_class();
    if(!isset(static::$_class_vars[$class_name])) {
      static::$_class_vars[$class_name] = array($var => $value);
    } else {
      static::$_class_vars[$class_name][$var] = $value;
    }
  }
  
  /* Storage for attributes */
  
  private $_original_attribute_data = array();
  private $_attribute_data          = array();
  
  public function __call($name, $arguments) {
    if(preg_match('/^(get|set)_(.*)/', $name, $matches)) {
      $op         = $matches[1];
      $attribute  = $matches[2];

      if($this->is_attribute($attribute)) {
        switch($op) {
          case "set":
            if(count($arguments) == 1) {
              $this->set_attribute($attribute, $arguments[0]);
              return;
            }
          case "get":
            return $this->get_attribute($attribute);
        }
      }
    }
          
    if(preg_match('/^get_(.*)_was/', $name, $matches)) {                
      $attribute = $matches[1];        
      if($this->is_attribute($attribute)) {
        return $this->get_attribute_was($attribute);
      }
    }

    if(preg_match('/^(.*)_changed/', $name, $matches)) {
      $attribute = $matches[1];
      if($this->is_attribute($attribute)) {
        return $this->attribute_changed($attribute);
      }
    }
    
    return parent::__call($name, $arguments);
  }
  
  protected function get_validations() {
    return array();
  }
  
  protected function perform_validate() {
    foreach($this->get_validations() as $validation) {
      $validation->validate($this);
    }
  }
  
  protected function is_attribute($attribute) {
    return in_array($attribute, $this->get_attributes());
  }
  
  protected static function get_attribute_definition($attribute) {
    $defs = static::get_attribute_definitions();
    if(isset($defs[$attribute])) {
      return $defs[$attribute];
    } else {
      return null;
    }
  }
  
  public function get_attribute_was($attribute) {
    if(!$this->attribute_changed($attribute)) {
      return $this->get_attribute($attribute);
    } else {
      return $this->_original_attribute_data[$attribute];
    }
  }
  
  public function attribute_changed($attribute) {
    if($this->is_saved()) {

      $def = static::get_attribute_definition($attribute);
      if($def) {
        $v  = isset($this->_attribute_data[$attribute]) ? $this->_attribute_data[$attribute] : null;
        $ov = isset($this->_original_attribute_data[$attribute]) ? $this->_original_attribute_data[$attribute] : null;
        switch($def['type']) {
          case 'holding':
            $v  = (string) $v;
            $ov = (string) $ov;
          default:
            if($v === null && $ov === null) {
              return false;
            } else if(($v === null && $ov !== null) || ($v !== null && $ov === null)) {
              return true;
            } else {
              return $v !== $ov;
            }
        }
      }
    }
    return false;
  }
  
  public function is_dirty() {
    foreach($this->get_attributes() as $attribute) {
      if($this->attribute_changed($attribute)) {
        return true;
      }
    }
    return false;
  }
  
  public function get_changed_attributes() {
    $r = array();
    foreach($this->get_attributes() as $attribute) {
      if($this->attribute_changed($attribute)) {
        $ov = $this->get_attribute_was($attribute);
        $v  = $this->get_attribute($attribute);
        
        if(is_object($ov)) 
          $ov = (string) $ov;
          
        if(is_object($v)) 
          $v = (string) $v;

        $r[$attribute] = array('old' => $ov, 'new' => $v);
      }
    }
    return $r;
  }
  
  public static function quote_attribute($attribute, $value) {
    $def = static::get_attribute_definition($attribute);
    if($def) {
      switch($def['type']) {
        case "serial":
        case "int":
        case "integer":
          return quote_int($value);
        case "string":
          if(isset($def['length'])) {
            $value = $value ? substr($value, 0, $def['length']) : null;
          }
          return quote_str($value);
        case "datetime":
          if(is_string($value)) {
            return quote_str($value);
          } else {
            return quote_datetime($value);
          }
        case "time":
          return quote_time($value);
        case "date":
          $allow_null = isset($def['allow_null']) && $def['allow_null'] === true;
          if(is_string($value)) {
            return quote_str($value);
          } else {
            return quote_date($value, $allow_null);
          }
        case "serialized":
          return quote_str(serialize($value));
        case "boolean":
          return quote_bool($value);
        case "holding":
          if(is_string($value)) {
            return quote_str($value);
          } else if($value) {
            return quote_str($value->get_cph());
          } else {
            return quote_str(null);
          }
        default:
          return "";
      }
    } else {
      return "";
    }
	}
  
	public function quoted_attributes() {
	  $r = array();
	  foreach(static::get_attribute_definitions() as $attribute => $def) {
	    $f = $attribute;
	    if(isset($def['readonly']) && $def['readonly'])
	      continue;
	    if(isset($def['field'])) {
	      $f = $def['field'];
	    }
	    $v = $this->get_attribute($attribute);
	    if(isset($def['default']) && $v === null) {
	      $v = $def['default'];
      }
	    $r[$f] = $this->quote_attribute($attribute, $v);
	  }
	  return $r;
	}
	
	protected static function public_attributes() {
	  $public_attributes = static::get_class_var("public_attributes");
	  
    if(!$public_attributes) {
      $public_attributes = array();
      foreach(static::get_attribute_definitions() as $attribute => $def) {
        if(isset($def['public']) && $def['public']) {
          $public_attributes[] = $attribute;
        }
      }
      static::set_class_var("public_attributes", $public_attributes);
    }
    return  $public_attributes;
	}
	
	public static function get_attributes() {
	  $all_attributes = static::get_class_var("all_attributes");
	  
    if(!$all_attributes) {
      $all_attributes = array();
      foreach(static::get_attribute_definitions() as $attribute => $definition) {
        $all_attributes[] = $attribute;
      }
      static::set_class_var("all_attributes", $all_attributes);
    }
    return $all_attributes;
	}
	
	/** Returns map of field => attriute */
	protected static function attribute_map() {
	  $attribute_map = static::get_class_var("attribute_map");
	  
	  if(!$attribute_map) {
	    $attribute_map = array();
	    foreach(static::get_attribute_definitions() as $attribute => $def) {
	      if(isset($def['field'])) {
	        $attribute_map[$def['field']] = $attribute;
	      } else {
	        $attribute_map[$attribute] = $attribute;
	      }
	      static::set_class_var("attribute_map", $attribute_map);
	    }
	  }
	  return $attribute_map;
	}
	
	public function _get_attribute($attribute) {
	  return isset($this->_attribute_data[$attribute]) ? $this->_attribute_data[$attribute] : null;
	}
	
	public function get_attribute($attribute) {
		$method = "get_$attribute";
    if(method_exists($this, $method)) {
      return $this->$method();
  	} else if($this->is_attribute($attribute)) {
  	  if(isset($this->_attribute_data[$attribute]))
		    return $this->_attribute_data[$attribute];
	    else
  	    return null;
  	}
		return null;
	}
	
	public function _set_attribute($attribute, $value) {
	  $def = static::get_attribute_definition($attribute);
    if($def) {  	    
	    $cast_value = null;
  	  switch($def['type']) {
	      case "serial":
	      case "integer":
	      case "int":
	        $cast_value = is_numeric($value) ? (int) $value : null;
	        break;
	      case "string":
	        $cast_value = $value;
	        break;
	      case "date":
	        $cast_value = ScotEID_Utils::sanitize_date($value);
	        break;
	      case "datetime":
	        $cast_value = ScotEID_Utils::sanitize_datetime($value);
	        break;
	      case "time":
	        $cast_value = ScotEID_Utils::sanitize_time($value);
	        break;
	      case "boolean":
	        $cast_value = $value === null ? null : (bool) $value;
	        break;
	      case "serialized":
	        if(is_string($value)) {
	          $cast_value = unserialize($value);
	        } else {
	          $cast_value = $value;
	        }
	        break;
	      case "holding":
	        $cast_value = ScotEID_Utils::sanitize_location($value);
	        break;
	    }
	    $this->_attribute_data[$attribute] = $cast_value;
	    if(!array_key_exists($attribute, $this->_original_attribute_data))
	      $this->_original_attribute_data[$attribute] = $cast_value;
	  } 
	}
	
	public function set_attribute($attribute, $value) {
	  $method = "set_$attribute";
	  if(method_exists($this, $method)) {
	    $this->$method($value);
  	} else if($this->is_attribute($attribute)) {
      $this->_set_attribute($attribute, $value);
	  } else {
	  }
	}
	
	protected function after_save() {
	  parent::after_save();
	  $this->_original_attribute_data = $this->_attribute_data;
	}
	
	protected function after_save_on_update() {
	  parent::after_save_on_update();
	}
}
?>
<?php
abstract class ScotEID_Model extends ScotEID_SimpleModel
{ 
  private $is_saved = false;
  private $errors   = array();
  
  public function set_errors($errors) {
    $this->errors = $errors;
  }
  
  public function get_errors() {
    return $this->errors;
  }
  
  public function add_error($field, $message) {
    if(!array_key_exists($field, $this->errors)) {
      $this->errors[$field] = array();
    }
    $this->errors[$field][] = $message;
  }
  
  public function has_errors($field) {
    return array_key_exists($field, $this->errors);
  }
  
  public function validate(&$errors = array()) {
      $this->errors = array();
      $this->before_validation();
      $this->perform_validate();
      return count($this->errors) == 0;
  }
  
  public function is_saved() { return $this->is_saved; }
  protected function set_saved($bool) { $this->is_saved = $bool; }
  
  public function save() {
    if(!$this->validate()) {
      return false;
    }
    
    dbw_begin();
    
    try {
      $was_saved = $this->is_saved();
      $this->before_save();      
      if($was_saved) {
        $this->before_save_on_update();
        dbw_update($this->_table_name(), $this->quoted_attributes(), $this->get_primary_key());
				$this->after_save_on_update();
      } else {        
        $this->before_save_on_create();
        dbw_insert($this->_table_name(), $this->quoted_attributes());               
			  $this->after_save_on_create();			      
      }
      $this->after_save();
      dbw_commit();
      $this->is_saved            = true;
    } catch(Exception $ex) {
      dbw_rollback();
      throw $ex;
    }
    
    return true;
  }
  
  public function delete() {
    if(!$this->is_saved()) throw new Exception("Can't delete unsaved object");
    
    dbw_begin();
    
    try {
      dbw_delete($this->_table_name(), $this->get_primary_key());
      $this->after_delete();
      dbw_commit();
      
      $this->is_saved = false;
    } catch(Exception $e) {
      dbw_rollback();
      throw $e;
    }
  }
  
  public function extract_all_from($a) {
      $this->is_saved = true;
      parent::extract_all_from($a);
  }
     
  public static function find_by_sql($sql) {
    $class = get_called_class();
    $result = new $class;
        
    $row = dbw_row(dbw_query($sql));
    // FIXME - need to throw generic "not found" exception
    if(!$row) throw new LotNotFoundException("Object not found");
    $result->extract_all_from($row);
    
    return $result;
  }     

	public static function all() {
		return static::find(array('conditions' => null));
	}
	
	public static function first($options = array()) {
	  $options['limit'] = 1;
	  $result = static::find($options);
	  if(count($result) > 0) {
	    return $result[0];
	  } else {
	    return null;
	  }
	}

	public static function find($options) {
		$class = get_called_class();
		$sql   = static::sql_for_options($options);
		if(isset($options['order'])) {
		  $sql .= " ORDER BY " . static::sql_for_order($options['order']);
		}
		if(isset($options['limit'])) {
			$limit = quote_int($options['limit']);
			$sql  .= " LIMIT $limit";
		} 
                if(isset($options['offset'])) {
			$offset = quote_int($options['offset']);
			$sql  .= " OFFSET $offset";
		} 
                if(isset($options['page'])) {
			$limit  = quote_int($options['per_page']);
			$offset = quote_int($limit*($options['page']-1));
			$sql   .= " LIMIT $limit OFFSET $offset";
		}
		$r = dbw_stack_objects($sql, $class, array('method' => 'extract_all_from'));
		if(isset($options['page'])) {
			$count = static::count($options);
			$r = array(
				'pages' => ceil($count/$options['per_page']),
				'page'	=> (int) $options['page'], // FIXME: this isn't quite right
				'items' => $r				
			);
			return $r;
		} else {
			return $r;
		}
	}
	
	public static function count($options = array()) {
		$sql = static::sql_for_options($options);
		$sql = "SELECT COUNT(*) AS count FROM ($sql)a";
		$res = dbw_query($sql);
		$row = dbw_row($res);
		return (int) $row['count'];
	}
	
	public static function sql_for_options($options) {
		$class  			= get_called_class();
		$conditions		= isset($options['conditions']) ? $options['conditions'] : null;
		$joins        = isset($options['joins']) ? $options['joins'] : null;
		$group        = isset($options['group']) ? $options['group'] : null;
		$select 		  = isset($options['select']) ? $options['select'] : '*';
		
		$conditions   = self::sql_for_conditions($conditions);
		
		$sql = "SELECT $select FROM " . static::get_table_name();
				
		if($joins) $sql .= ' ' . $joins;	
				
		if($conditions) $sql .= " WHERE $conditions";		
		
		if($group) $sql .= ' GROUP BY ' . $group;
				
		$sql = preg_replace_callback('/#([a-zA-Z\-_]+)#/', function($match) use ($class) {
		  if(in_array($match[1], $class::get_dynamic_search_properties())) {
		    return $class::sql_for_dynamic_search_property($match[1]);
		  } else {
		    $column_name = $class::get_column_name($match[1]);
		    if($column_name) {
		      return $class::get_table_name() . "." . $column_name;
		    } else {
		      return null;
		    }
		  }
		}, $sql);
		
		return $sql;	
	}
	
	public static function sql_for_conditions($conditions) 
	{	  
	  if(is_array($conditions)) {
			$new_conditions = array();
			
			// is this an array of arrays?
			$or = count($conditions) >= 1 && isset($conditions[0]) && is_array($conditions[0]);

			if(!$or) {
			  return self::sql_for_flat_conditions($conditions);
			} else {
			  $new_conditions = array(); 
			  foreach($conditions as $group) {
			    $new_conditions[] = "(" . self::sql_for_flat_conditions($group) . ")";
			  }
			  return implode(" OR ", $new_conditions);
			}
		}
		return $conditions;
	}
	
	private static function sql_for_flat_conditions($conditions) 
	{
	  $new_conditions = array();
	  foreach($conditions as $field => $condition) {
			$where = "";
			$parts = explode(".", $field);
			$where .= "#" . $parts[0] . "#";
			if(count($parts) == 1) {
			  if($condition === null) {
			    $where .= " IS NULL";
			  } else {
  				$where .= " = ";
				}
			} else {
				switch($parts[1]) {
					case 'gt':
					  $where .= " > ";
						break;
					case 'lt':
					  $where .= " < ";
						break;
					case 'gte':
					case 'ge':
						$where .= " >= ";
						break;
					case 'lte':
					case 'le':
						$where .= " <= ";
						break;
					case 'eq':
						$where .= " = ";
						break;
					case 'ne':
					  $where .= " <> ";
					  break;
					case 'in':
						$where .= " IN ";
						break;
					case 'bw':
					case 'ew':
					case 'cn':	
					case 'like':
					  $where .= " LIKE ";
						break;
					default:
						if(is_array($condition)) {
							$where .= " IN ";
						} else {
						  if($condition === null) {
						    $where .= " IS NULL";
						  } else {
  							$where .= " = ";
							}
						}
				}
			}
			if(is_array($condition)) {
				$wheres = array();
				foreach($condition as $v) {
					$wheres[] = static::quote_attribute($parts[0], $v);
				}
				$where .= "(";
				$where .= implode(',', $wheres);
				$where .= ")";
			} else if($condition !== null) {
			  $t = isset($parts[1]) ? $parts[1] : null;
			  $d = false;
			  if(static::is_dynamic_search_property($parts[0])) {
			    $d = true;
			    $condition = quote_str($condition);
			  }
			    
			  switch($t) {
			    case 'bw':
			      $where .= quote_str($condition . '%');
			      break;
			    case 'ew':
			      $where .= quote_str('%' . $condition); 
		        break;
		      case 'cn':
			      $where .= quote_str('%' . $condition . '%');
			      break;
			    default:
			      if($d)
			        $where .= $condition;
			      else
			        $where .= static::quote_attribute($parts[0], $condition);
			  }
			}
			$new_conditions[] = $where;
		}
		$conditions = implode(" AND ", $new_conditions);
		return $conditions;
	}
	
	private static function sql_for_order($order) {
	  $class = get_called_class();
	  $result = null;
	  if(is_array($order)) {
	    if(is_array($order[0])) {
  	    $orders = array();
  	    foreach($order as $o) {
  	      $orders[] = static::sql_for_order($o);
  	      $result   = implode(',', $orders);  
  	    } 
  	  } else {
  	    $sql_for_order = $class::sql_for_order_by($order[0], $order[1]);
  	    if($sql_for_order) $result .= $sql_for_order;
			}
		} else {
		  $result = $order;
		}
		return $result;
	}
	
	protected static function sql_for_order_by($column, $direction) {	  
    $column = static::get_expression_for_order_by_column($column);    
    $result = "";
    if($column) {
      $result .= $column;
      if($direction == 'asc')  $result .= " ASC";
      if($direction == 'desc') $result .= " DESC";	
      return $result;
    } else {
      return null;
    }
	}
	
	protected static function get_expression_for_order_by_column($column) {
    if(static::is_dynamic_search_property($column)) {
      return static::sql_for_dynamic_search_property($column);
    } else {
	    return static::get_column_name($column);
    }
	}
    
	public static function get_column_name($property_name) {
		$column_lookup = array_flip(static::attribute_map());
		if($column_lookup[$property_name]) {
			return $column_lookup[$property_name];
		} else {
			if(in_array($property_name, static::get_attributes())) {
				return $property_name;
			} else {
				return null;
			}
		}
	}

	protected function _table_name() {
		return static::get_table_name();
	}

  //
  // Abstract methods etc 
  
  public static function is_dynamic_search_property($property) {
    return in_array($property, static::get_dynamic_search_properties());
  }
  
  public static function get_dynamic_search_properties() {
    return array();
  }
  
  public static function sql_for_dynamic_search_property($property) {
    return null;
  }
    
  static public function get_table_name() {}
  abstract public function get_primary_key();
  
  protected function perform_validate() {}
  protected function before_validation() {}
  protected function before_validation_on_create() {}
  
  protected function after_save() {}
  protected function after_save_on_create() {}
  protected function after_save_on_update() {}
    
  protected function before_save() {}
  protected function before_save_on_create() {}
  protected function before_save_on_update() {}
  
  protected function after_delete() {}
}
?>

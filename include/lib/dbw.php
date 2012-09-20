<?php
$GLOBALS['_SCOTEID_DB_LINK'] = false;
$GLOBALS['_SCOTEID_DB_LINK_TX_COUNT'] = 0;

class DBException extends Exception {}
class DuplicateKeyException extends DBException {}
class ForeignKeyException extends DBException {}

function dbw_connect() {
  $e = $GLOBALS['_SCOTEID_WEBSERVICES_ENV']['db'];
  if (!$GLOBALS['_SCOTEID_DB_LINK'] = mysqli_connect($e['hostname'], $e['username'], $e['password'])) {
      throw new DBException("Error connecting to database");
  }
  if (!mysqli_select_db($GLOBALS['_SCOTEID_DB_LINK'], $e['database'])) {
      throw new DBException("Error selecing database");
  }
  return $GLOBALS['_SCOTEID_DB_LINK'];
}

function dbw_connection() {
  if(!$GLOBALS['_SCOTEID_DB_LINK']) {
    $link = dbw_connect();
  } else {
    $link = $GLOBALS['_SCOTEID_DB_LINK'];
  }  
  return $link;
}

function dbw_query($sql, $args = null) {
  $link = dbw_connection();
  
	if(SCOTEID_WEBSERVICES_ENV == 'development') {
    // if(defined(FB)) FB::log($sql);
	}

  if(!$res = mysqli_query($link, $sql)) {
    $code = mysqli_errno($link);
    
    switch($code) {
      case 1022:
      case 1062:
      case 1169: throw new DuplicateKeyException(mysqli_error($link));
      
      case 1216:
      case 1217: throw new ForeignKeyException(mysqli_error($link));
      
      default:
      
        $msg = "Query Error\n";
        $msg .= "-----------\n\n";
        $msg .= "Database said: " . mysqli_error($link) . " (code $code)\n\n";
        $msg .= "Offending Query\n";
        $msg .= "---------------\n\n";
        $msg .= $sql;

        if(SCOTEID_WEBSERVICES_ENV == 'development') {
          // if(defined(FB)) FB::log($msg);
				}

        throw new DBException($msg);
    }
  }
  
  return $res;
}

function dbw_stack_objects($res, $class, $options = array()) {
  if (is_string($res)) $res = dbw_query($res);

  $method = isset($options['method']) ? $options['method'] : null;
  $out    = array();
  
  while($row = dbw_row($res)) {
    if($method) {
      $instance = new $class;
      $instance->$method($row);
    } else {
      $instance = new $class($row);
    }
    $out[] = $instance;
  }
  
  return $out;
}

function dbw_result($res, $row = 0, $offset = 0) {
}

function dbw_tx_count() {
	return $GLOBALS['_SCOTEID_DB_LINK_TX_COUNT'];
}

function dbw_increment_tx_count() {
	$GLOBALS['_SCOTEID_DB_LINK_TX_COUNT'] = dbw_tx_count() + 1;
}

function dbw_decrement_tx_count() {
	$r = dbw_tx_count() - 1;
	if($r <= 0) {
		$GLOBALS['_SCOTEID_DB_LINK_TX_COUNT'] = 0;
	} else {
  	$GLOBALS['_SCOTEID_DB_LINK_TX_COUNT'] = $r;
	}
	return dbw_tx_count();
}

function dbw_row($res) { return mysqli_fetch_assoc($res); }
function dbw_begin() { 
	if(dbw_tx_count() == 0) {
		dbw_query("START TRANSACTION");
	}
	dbw_increment_tx_count();
}
function dbw_commit() {
	if(dbw_decrement_tx_count() == 0) {
		dbw_query("COMMIT");
	} 
}
function dbw_rollback() { 
	$GLOBALS['_SCOTEID_DB_LINK_TX_COUNT'] = 0;
	dbw_query("ROLLBACK"); 
}
function dbw_insert_id($id = null) { return mysqli_insert_id(dbw_connection()); }
function dbw_affected_rows() { return mysqli_affected_rows(dbw_connection()); }

// 
// Simple SQL generation

function dbw_insert($table, $attribs) {
  dbw_query(sql_for_insert($table, $attribs));
}

function dbw_delete($table, $key) {
  dbw_query(sql_for_delete($table, $key));
}

function dbw_update($table, $attribs, $key) {
  dbw_query(sql_for_update($table, $attribs, $key));
}

function sql_for_insert($table, $attribs) {
  $fields = implode(',', array_keys($attribs));
  $values = implode(',', array_values($attribs));
  $sql    = "INSERT INTO $table ($fields) VALUES ($values)";
  return $sql;
}

function sql_for_update($table, $attribs, $key = array()) {
  $cond = array();
  foreach($key as $f => $v) {
    $cond[] = "$f = $v";
  }
  $cond_joined = implode(' AND ', $cond);
  $attrib = array();
  foreach($attribs as $f => $v) {
    $attrib[] = "$f = $v";
  }
  $attrib_joined = implode(',', $attrib);
  return "UPDATE $table SET $attrib_joined WHERE $cond_joined";
}

function sql_for_delete($table, $key = array()) {
  $cond = array();
  foreach($key as $f => $v) {
    $cond[] = "$f = $v";
  }
  $cond_joined = implode(' AND ', $cond);
  return "DELETE FROM $table WHERE $cond_joined";
}

//
// Data quoting

function quote_str($s) {
    if (is_null($s)) return 'NULL';
    return "'" . mysqli_real_escape_string(dbw_connection(), $s) . "'";
}

function quote_int($n) {
    if (is_null($n)) return 'NULL';
    return (int) $n;
}

function quote_float($n) {
    if (is_null($n)) return 'NULL';
    return (float) $n;
}

function quote_bool($b) {
    if (is_null($b)) return 'NULL';
    return $b ? 1 : 0;
}

function quote_datetime($n, $allow_null = true) {
  if(is_null($n) || empty($n)) return ($allow_null ? 'NULL' : '0000-00-00T00:00:00');
  return "'" . strftime('%Y-%m-%dT%H:%M:%S', $n) . "'";
}

function quote_time($n) {
  if(is_null($n) || empty($n)) return 'NULL';
  return "'" . strftime('%H:%M:%S', $n) . "'";
}

function quote_date($n, $allow_null = true) {
  if(is_null($n) || empty($n)) return ($allow_null ? 'NULL' : '0000-00-00');
  return "'" . strftime('%Y-%m-%d', $n) . "'";
}

?>
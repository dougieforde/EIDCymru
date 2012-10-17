<?php
class ScotEID_Utils
{
  public static function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
  {
      // Length of character list
      $chars_length = (strlen($chars) - 1);

      // Start our string
      $string = $chars{rand(0, $chars_length)};

      // Generate random string
      for ($i = 1; $i < $length; $i = strlen($string))
      {
          // Grab a random character from our list
          $r = $chars{rand(0, $chars_length)};

          // Make sure the same two characters don't appear next to each other
          if ($r != $string{$i - 1}) $string .=  $r;
      }

      // Return the string
      return $string;
  }
  
  public static function get_incomplete_sub_lot_number($lot_date, $lot_number, $uid) {
    $ok = false;
    
    $exploded = explode('/', $lot_number);
    
    $sub_lot_number = '';
    $lot_date       = quote_date($lot_date, false);
    $uid            = quote_int($uid);
    
    if(count($exploded) > 1) {
      $lot_number = implode("/", explode("/", $lot_number, -1));
      $char       = array_pop($exploded);
    } else {
      $char       = 'A';
    }
    
    $sub_lot_number = $lot_number . '/' . $char;
    while(strlen($sub_lot_number) < 10) {
      $quoted_sub_lot_number = quote_str($sub_lot_number);
      $row = dbw_row(dbw_query("SELECT SAMS_Movement_Reference FROM templots WHERE LotDate=$lot_date AND Lot_No=$quoted_sub_lot_number AND uid=$uid"));
      
      if(!$row) {
        $ok = true;
        break;
      }
      
      $char++;
      $sub_lot_number = $lot_number . '/' . $char;
    }
    
    return $ok ? $sub_lot_number : null;
  }
  
  public static function get_deleted_lot_number($lot_date, $read_location) 
  {
    $random = ScotEID_Utils::rand_str(10);

    $lot = ScotEID_CompletedLot::first(array('conditions' => array(
      'lot_date'      => $lot_date, 
      'read_location' => $read_location, 
      'lot_number'    => $random
    )));

    if(!$lot) {
      return $random;
    } else {
      return get_deleted_lot_number($lot_date, $read_location);
    }
  }
  
  public static function sanitize_date($date) {
    if(empty($date)) {
      return null;
    } else if(is_string($date)) {
      if($date == '0000-00-00') {
        return null;
      } else {
        return strtotime($date);
      }
    } else {
      return (int) $date;
    }  
  }
  
  public static function sanitize_datetime($datetime) {
    if(is_array($datetime) && isset($datetime['date']) && isset($datetime['time'])) {
      $datetime = $datetime['date'] . " " . $datetime['time'];
    }
    
    if(is_string($datetime))
      $datetime = trim($datetime);
    
    if(empty($datetime)) {
        return null;
    } else if(is_string($datetime)) {
        if($datetime == '0000-00-00 00:00:00' || $datetime == '00:00:00') {
          return null;
        }    
        $t = strtotime($datetime);
        if($t !== false) {
            return $t;
        } else {
            return null;
        }
    } else {
        return (int) $datetime;
    }
  }
  
  public static function sanitize_time($time) {
    return static::sanitize_datetime($time);
  }
  
  public static function sanitize_location($location) {
      if(empty($location)) {
          return null;
      } else if(is_string($location)) {
          return ScotEID_Holding::try_parse(trim($location));
      } else if($location instanceof ScotEID_Holding) {
          return $location;
      } else {
        return new ScotEID_Holding('');
      }
  }
}
?>
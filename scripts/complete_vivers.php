<?php
require 'include/prepend.php';
require 'include/script_helpers.php';

try {
  $lot_date = strtotime('0000-00-00');
  
  do {  
    $lots = ScotEID_Lot::find(array(
      'conditions' => array(
                        'uid'          => 1835, 
                        'lot_date.gte' => $lot_date
                      ), 
      'order'      => array('lot_date', 'asc'),
      'limit'      => 100  
    ));
    
    foreach($lots as $lot) {
      $lot->load_tag_readings();

      $lot->set_read_location("75/297/8000");
      $lot->set_departure_location("99/999/9999");
      $lot->set_destination_location("99/999/9999");
      $lot->set_movement_type_id(8);
      $lot->set_head_count(1);

      $lot_date       = $lot->get_lot_date();
      $formatted_date = date("d/m/Y", $lot->get_lot_date());

      try {
        $c = $lot->complete();
        if($c->is_saved()) {
          print_success("OK - completed lot {$lot->get_lot_number()} on $formatted_date");
        } else {
          print_error("FAILED {$lot->get_lot_number()} on $formatted_date");
        }
      } catch(DuplicateKeyException $dke) {
        print_error("DUPLICATE KEY {$lot->get_lot_number()} on $formatted_date");
      }
    }
  } while(count($lots) > 0);

} catch(Exception $ex) {
  print_error($ex);
}
?>
<?php
require './include/prepend.php';
require 'include/script_helpers.php';

$lot = new ScotEID_CompletedLot();
$lot->set_lot_number("FarmToFar2");
$lot->set_lot_date(strtotime("tomorrow"));
$lot->set_arrival_date(strtotime("tomorrow"));
$lot->set_movement_type_id(3);
$lot->set_species_id(3);
$lot->set_head_count(10);
$lot->set_doa_count(1);
$lot->set_fci_declaration(false);
$lot->set_uid(194);
$lot->set_departure_location("66/999/6666");
$lot->set_destination_location("33/444/5555");
$lot->set_description_id(1);
$lot->set_transport_details(array(
  'departure_name'             => 'Early Morning Farm Keeper Name',
  'departure_business'         => 'Early Morning Farm',
  'departure_address_3'        => 'Address3',
  'departure_address_4'        => 'Address4',
  'departure_assurance_number' => 103,
  'departure_postcode'         => 'HG4 5DB',
  'expected_duration'          => 2
));
$lot->set_batch_mark('SL6699');

try {
  if($lot->save()) {
    print_success("Movement from {$lot->get_departure_location()} to {$lot->get_destination_location()} saved");
  } else {
    print_error("Error saving lot {$lot->get_lot_number()}");
  }
} catch(DuplicateKeyException $ex) {
  print_error("Lot {$lot->get_lot_number()} already exists!");
}

$lot = new ScotEID_CompletedLot();
$lot->set_lot_number("FarmToAb1");
$lot->set_lot_date(strtotime("tomorrow"));
$lot->set_arrival_date(strtotime("tomorrow"));
$lot->set_movement_type_id(3);
$lot->set_species_id(3);
$lot->set_head_count(10);
$lot->set_doa_count(1);
$lot->set_fci_declaration(true);
$lot->set_uid(194);
$lot->set_departure_location("66/999/6666");
$lot->set_destination_location("99/888/7777");
$lot->set_description_id(1);
$lot->set_transport_details(array(
  'departure_name'             => 'Early Morning Farm Keeper Name',
  'departure_business'         => 'Early Morning Farm',
  'departure_address_3'        => 'Address3',
  'departure_address_4'        => 'Address4',
  'departure_assurance_number' => 103,
  'departure_postcode'         => 'HG4 5DB',
  'expected_duration'          => 2
));
$lot->set_batch_mark('SL6699');

try {
  if($lot->save()) {
    print_success("Movement from {$lot->get_departure_location()} to {$lot->get_destination_location()} saved");
  } else {
    print_error("Error saving lot {$lot->get_lot_number()}");
  }
} catch(DuplicateKeyException $ex) {
  print_error("Lot {$lot->get_lot_number()} already exists!");
}

?>
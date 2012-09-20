<?php
class ScotEID_TestHelpers
{
  public static function buildValidIncompleteLot() {
    $lot = new ScotEID_Lot();
    $lot->set_uid(1);
    $lot->set_lot_number("1");
    $lot->set_movement_type_id(4);
    
    $trs = array();
    for($i = 0; $i < 10; $i++) {
      $tr = new ScotEID_TagReading();
      $tr->set_timestamp(time());
      $tr->set_tag_id(new ScotEID_ISO24631TagID(sprintf("1 0 04 00 0 826 059999%06d", $i)));
      $trs[] = $tr;
    }
    $lot->set_tag_readings($trs);
    
    $fts = array();
    $ft = new ScotEID_FlockTag();
    $ft->set_flock_number(599999);
    $ft->set_tag_count(5);
    $fts[] = $ft;
    
    $ft = new ScotEID_FlockTag();
    $ft->set_flock_number(599998);
    $ft->set_tag_count(2);
    $fts[] = $ft;
    
    $lot->set_flock_tags($fts);
    
    return $lot;
  }
  
  public static function buildValidCompletedLot() {
    $lot = new ScotEID_CompletedLot();
    $lot->set_uid(1);
    $lot->set_lot_number("1");
    $lot->set_movement_type_id(4);
    $lot->set_read_location("11/222/3333");
    $lot->set_departure_location("11/111/3333");
    $lot->set_destination_location("33/444/5555");
    $lot->set_head_count(10);
    $lot->set_lot_date(time());
    
    $trs = array();
    for($i = 0; $i < 10; $i++) {
      $tr = new ScotEID_TagReading();
      $tr->set_timestamp(time());
      $tr->set_tag_id(new ScotEID_ISO24631TagID(sprintf("1 0 04 00 0 826 059999%06d", $i)));
      $trs[] = $tr;
    }
    $lot->set_tag_readings($trs);
    
    $fts = array();
    $ft = new ScotEID_FlockTag();
    $ft->set_flock_number(599999);
    $ft->set_tag_count(5);
    $fts[] = $ft;
    
    $ft = new ScotEID_FlockTag();
    $ft->set_flock_number(599998);
    $ft->set_tag_count(2);
    $fts[] = $ft;
    
    $lot->set_flock_tags($fts);
    
    return $lot;
  }
  
  public static function buildValidPigLot() {
    $lot = new ScotEID_CompletedLot();
    $lot->set_uid(1);
    $lot->set_lot_number("1");
    $lot->set_movement_type_id(3);
    $lot->set_species_id(3);
    $lot->set_batch_mark("AB123");
    $lot->set_departure_location("66/444/5555");
    $lot->set_destination_location("66/333/4444");
    $lot->set_head_count(1);
    $lot->set_lot_date(strtotime("today"));
    $tr = $lot->get_or_create_transport_details();
    $tr->set_departure_name("Joe Bloggs");
    $tr->set_departure_business("Bloggs Farm");
    $tr->set_departure_address_3("Glasgow");
    $tr->set_departure_address_4("Lanarkshire");
    $tr->set_departure_postcode("G3 8AB");
    # destination address and postcode now required
    $tr->set_destination_business("Test");
    $tr->set_destination_address_3("Glasgow");
    $tr->set_destination_address_4("Lanarkshire");
    $tr->set_destination_postcode("G3 8AB");
    return $lot;
  }
}
?>
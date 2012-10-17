<?php
require_once './test/test_helper.php';

class ScotEID_TagReadingTest extends ScotEID_BaseTest
{ 
  public function testPrevIDAndCountryCode() {        
    $lot = new ScotEID_CompletedLot();
    $lot->set_uid(0);
    $lot->set_lot_number("1");
    $lot->set_lot_date(strtotime("today"));
    $lot->set_head_count(1);
    $lot->set_movement_type_id(4);
    $lot->set_departure_location("11/222/3333");
    $lot->set_read_location("22/333/4444");
    $lot->set_destination_location("33/444/5555");
    
    $tr = new ScotEID_TagReading();
    $tr->set_timestamp(time());
    $tr->set_tag_id(new ScotEID_ISO24631TagID(sprintf("1 0 04 00 0 826 059999%06d", 100)));
    $tr->set_prev_id(1234);
    $tr->set_prev_country_code(826);
    
    $this->assertEquals(1234, $tr->get_prev_id());
    $this->assertEquals(826, $tr->get_prev_country_code());
    
    $lot->set_tag_readings(array($tr));
    $this->assertTrue($lot->save());
    
    $lot = ScotEID_CompletedLot::first(
      array(
        'conditions' => array('sams_movement_reference' => $lot->get_sams_movement_reference())
      )
    );
    
    $tr2s = $lot->get_tag_readings();
    $tr2 = $tr2s[0];
    $this->assertEquals(1234, $tr2->get_prev_id());
    $this->assertEquals(826, $tr2->get_prev_country_code());
  }
  
}
?>
<?php
require_once './test/test_helper.php';

class ScotEID_TagRegisterTest extends ScotEID_BaseTest
{ 
  /*
   * Things to test:
   *   1. test new tags are added to the register
   *   2. 
   */
  public function testNewTagsAreAddedToRegister() {    
    $r = dbw_query("SELECT count(*) AS count FROM tblindividualsheepeidregister");
    if($row = dbw_row($r)) {
      $this->assertEquals(0, $row['count']);
    }
    
    $lot = ScotEID_TestHelpers::buildValidCompletedLot();
    $this->assertTrue($lot->save());
        
    $r = dbw_query("SELECT count(*) AS count FROM tblindividualsheepeidregister");
    if($row = dbw_row($r)) {
      $this->assertEquals($lot->get_head_count(), (int) $row['count']);
    }
  }
  
  public function testFirstMoveUpdatesTagRegister() {
    
    $lot = ScotEID_TestHelpers::buildValidCompletedLot();
    $this->assertTrue($lot->save());
        
    $l = ScotEID_CompletedLot::first(array('conditions' => array('sams_movement_reference' => $lot->get_sams_movement_reference())));
    foreach($l->get_tag_readings() as $r) {
      $this->assertEquals($l->get_movement_type_id(),     $r->get_animal()->get_last_movement_type());
      $this->assertEquals($l->get_lot_date(),             $r->get_animal()->get_last_movement_date());
      $this->assertEquals($l->get_read_location(),        $r->get_animal()->get_last_read_location());
      $this->assertEquals($l->get_destination_location(), $r->get_animal()->get_last_destination_location());
    }    
  }
  
  public function testSubsequentMovesUpdateTagRegister() {
    
    $lot = ScotEID_TestHelpers::buildValidCompletedLot();
    $lot->set_read_location("55/555/5555");
    $lot->set_destination_location("66/666/6666");
    $this->assertTrue($lot->save());
    
    $lot2 = ScotEID_TestHelpers::buildValidCompletedLot();
    $lot2->set_departure_location("66/666/6666");
    $lot2->set_read_location("77/777/7777");
    $lot2->set_destination_location("88/888/8888");
    $lot2->set_tag_readings(array());
    $lot2->set_flock_tags(array());
    $lot2->set_head_count(5);
    
    $tr1 = $lot->get_tag_readings();
    for($i = 0; $i < 5; $i++) {
      $lot2->add_tag_reading($tr1[$i]);
    }
    
    $this->assertTrue($lot2->save());
    
    $lot3 = ScotEID_CompletedLot::first(array('conditions' => array('sams_movement_reference' => $lot->get_sams_movement_reference())));
    $lot4 = ScotEID_CompletedLot::first(array('conditions' => array('sams_movement_reference' => $lot2->get_sams_movement_reference())));
    
    $tr2 = $lot3->get_tag_readings();
    for($i = 0; $i < 5; $i++) {
      $r = $tr2[$i];
      $this->assertEquals($lot4->get_movement_type_id(),     $r->get_animal()->get_last_movement_type());
      $this->assertEquals($lot4->get_lot_date(),             $r->get_animal()->get_last_movement_date());
      $this->assertEquals($lot4->get_read_location(),        $r->get_animal()->get_last_read_location());
      $this->assertEquals($lot4->get_destination_location(), $r->get_animal()->get_last_destination_location());
    }
    
    for($i = 5; $i < 10; $i++) {
      $r = $tr2[$i];
      $this->assertEquals($lot3->get_movement_type_id(),     $r->get_animal()->get_last_movement_type());
      $this->assertEquals($lot3->get_lot_date(),             $r->get_animal()->get_last_movement_date());
      $this->assertEquals($lot3->get_read_location(),        $r->get_animal()->get_last_read_location());
      $this->assertEquals($lot3->get_destination_location(), $r->get_animal()->get_last_destination_location());
    }
  }
  
  public function testSubsequentMovesUpdateTagRegisterIncludingMovementType() {
    
    $lot = ScotEID_TestHelpers::buildValidCompletedLot();
    $lot->set_read_location("55/555/5555");
    $lot->set_destination_location("66/666/6666");
    $this->assertTrue($lot->save());
    
    $lot2 = ScotEID_TestHelpers::buildValidCompletedLot();
    $lot2->set_departure_location("66/666/6666");
    $lot2->set_read_location("77/777/7777");
    $lot2->set_destination_location("77/777/7777");
    $lot2->set_movement_type_id(ScotEID_MovementType::MOVEMENT_TYPE_ABATTOIR);
    $lot2->set_tag_readings(array());
    $lot2->set_flock_tags(array());
    $lot2->set_head_count(5);
    
    $tr1 = $lot->get_tag_readings();
    for($i = 0; $i < 5; $i++) {
      $lot2->add_tag_reading($tr1[$i]);
    }
    
    $this->assertTrue($lot2->save());
    
    $lot3 = ScotEID_CompletedLot::first(array('conditions' => array('sams_movement_reference' => $lot->get_sams_movement_reference())));
    $lot4 = ScotEID_CompletedLot::first(array('conditions' => array('sams_movement_reference' => $lot2->get_sams_movement_reference())));
    
    $tr2 = $lot3->get_tag_readings();
    for($i = 0; $i < 5; $i++) {
      $r = $tr2[$i];
      $this->assertEquals($lot4->get_movement_type_id(),     $r->get_animal()->get_last_movement_type());
      $this->assertEquals($lot4->get_lot_date(),             $r->get_animal()->get_last_movement_date());
      $this->assertEquals($lot4->get_read_location(),        $r->get_animal()->get_last_read_location());
      $this->assertEquals($lot4->get_destination_location(), $r->get_animal()->get_last_destination_location());
    }
    
    for($i = 5; $i < 10; $i++) {
      $r = $tr2[$i];
      $this->assertEquals($lot3->get_movement_type_id(),     $r->get_animal()->get_last_movement_type());
      $this->assertEquals($lot3->get_lot_date(),             $r->get_animal()->get_last_movement_date());
      $this->assertEquals($lot3->get_read_location(),        $r->get_animal()->get_last_read_location());
      $this->assertEquals($lot3->get_destination_location(), $r->get_animal()->get_last_destination_location());
    }
  }
  
  public function testDeletingLotRevertsToPreviousMovementDetails() {
    
    $lot1 = ScotEID_TestHelpers::buildValidCompletedLot();
    $lot1->set_read_location("55/555/5555");
    $lot1->set_destination_location("66/666/6666");
    $this->assertTrue($lot1->save());
    
    $lot2 = ScotEID_TestHelpers::buildValidCompletedLot();
    $lot2->set_read_location("66/666/6666");
    $lot2->set_destination_location("66/666/6666");
    $lot2->set_movement_type_id(ScotEID_MovementType::MOVEMENT_TYPE_DEATH);
    $lot2->set_tag_readings($lot1->get_tag_readings());
    $lot2->set_flock_tags(array());
    $lot2->set_head_count(5);
    
    $this->assertTrue($lot2->save());
    
    $lot2->delete();
    
    //
    // all details in the tag register should now have reverted back to those
    // of the first lot ($lot)
    $lot1 = ScotEID_CompletedLot::first(
      array('conditions' => 
        array('sams_movement_reference' => $lot1->get_sams_movement_reference())
      )
    );
    foreach($lot1->get_tag_readings() as $r) {
      $this->assertEquals($lot1->get_movement_type_id(),     $r->get_animal()->get_last_movement_type());
      $this->assertEquals($lot1->get_lot_date(),             $r->get_animal()->get_last_movement_date());
      $this->assertEquals($lot1->get_read_location(),        $r->get_animal()->get_last_read_location());
      $this->assertEquals($lot1->get_destination_location(), $r->get_animal()->get_last_destination_location());
    }
    
  }
  
  public function testDeletingLotRevertsToPreviousMovementDetails2() {
    
    $lot1 = ScotEID_TestHelpers::buildValidCompletedLot();
    $lot1->set_read_location("55/555/5555");
    $lot1->set_destination_location("66/666/6666");
    $this->assertTrue($lot1->save());
    
    $lot2 = ScotEID_TestHelpers::buildValidCompletedLot();
    $lot2->set_departure_location("66/666/6666");
    $lot2->set_read_location("77/777/7777");
    $lot2->set_destination_location("77/777/7777");
    $lot2->set_movement_type_id(ScotEID_MovementType::MOVEMENT_TYPE_ABATTOIR);
    $lot2->set_tag_readings($lot1->get_tag_readings());
    $lot2->set_flock_tags(array());
    $lot2->set_head_count(5);
    
    $this->assertTrue($lot2->save());
    
    $lot2->delete();
    
    //
    // all details in the tag register should now have reverted back to those
    // of the first lot ($lot)
    $lot1 = ScotEID_CompletedLot::first(
      array('conditions' => 
        array('sams_movement_reference' => $lot1->get_sams_movement_reference())
      )
    );
    foreach($lot1->get_tag_readings() as $r) {
      $this->assertEquals($lot1->get_movement_type_id(),     $r->get_animal()->get_last_movement_type());
      $this->assertEquals($lot1->get_lot_date(),             $r->get_animal()->get_last_movement_date());
      $this->assertEquals($lot1->get_read_location(),        $r->get_animal()->get_last_read_location());
      $this->assertEquals($lot1->get_destination_location(), $r->get_animal()->get_last_destination_location());
    }
    
  }
  
}
?>
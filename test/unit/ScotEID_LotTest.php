<?php
require_once './test/test_helper.php';

class ScotEID_LotTest extends ScotEID_BaseTest
{
  public function testLotsCleanedUp() {
    $this->assertEquals(0, ScotEID_Lot::count());
  }
  
  public function testCompletingLotSavesTags() {
    $lot = ScotEID_TestHelpers::buildValidIncompleteLot();
    $lot->save();
    $lot = ScotEID_Lot::first(array('conditions' => array('sams_movement_reference' => $lot->get_sams_movement_reference())));
    $lot->set_read_location("11/222/3333");
    $lot->set_departure_location("22/333/4444");
    $lot->set_destination_location("33/444/5555");
    $lot->set_head_count(10);
    $lot->set_lot_date(time());
    
    $completed_lot = $lot->complete();
     
    $this->assertTrue($completed_lot->is_saved(), var_export($completed_lot->get_errors(), true));
    
    $completed_lot = ScotEID_CompletedLot::first(array('conditions' => array('sams_movement_reference' => $completed_lot->get_sams_movement_reference())));
    
    $this->assertNotNull($completed_lot);
    $this->assertEquals(count($lot->get_tag_readings()), count($completed_lot->get_tag_readings()));
    $this->assertEquals(count($lot->get_flock_tags()),  count($completed_lot->get_flock_tags()));
  }
  
  public function testCreatingValidIncompleteLotSucceeds() {
    $lot = ScotEID_TestHelpers::buildValidIncompleteLot();
    $this->assertTrue($lot->save(), var_export($lot->get_errors(), true));
    $this->assertEquals(1, ScotEID_Lot::count());
    
    $lookup_lot = ScotEID_Lot::first(array('conditions' => array(
      'sams_movement_reference' => $lot->get_sams_movement_reference()
    )));
    
    $this->assertNotNull($lookup_lot);
  }
  
  public function testIncompleteLotRequiresUID() {
    $lot = ScotEID_TestHelpers::buildValidIncompleteLot();
    $lot->set_uid(null);
    $this->assertFalse($lot->save());
  }
  
  public function testIncompleteLotRequiresLotNumber() {
    $lot = ScotEID_TestHelpers::buildValidIncompleteLot();
    $lot->set_lot_number("");
    $this->assertFalse($lot->save());
  }
  
  public function testIncompleteLotRequiresMovementType() {
    $lot = ScotEID_TestHelpers::buildValidIncompleteLot();
    $lot->set_movement_type_id(null);
    $this->assertFalse($lot->save());
  }
  
  public function testIncompleteLotGetsDefaultSpecies() {
    $lot = ScotEID_TestHelpers::buildValidIncompleteLot();
    $lot->set_species_id(null);
    $this->assertTrue($lot->save());
    
    $lookup_lot = ScotEID_Lot::first(array('conditions' => array(
      'sams_movement_reference' => $lot->get_sams_movement_reference()
    )));
    
    $this->assertEquals(4, $lookup_lot->get_species_id());
  }
  
  public function testIncompleteLotDoesntOverrideSpecies() {
    $lot = ScotEID_TestHelpers::buildValidIncompleteLot();
    $lot->set_species_id(3);
    $lot->set_batch_mark("AB123");
    $this->assertTrue($lot->save());
    
    $lookup_lot = ScotEID_Lot::first(array('conditions' => array(
      'sams_movement_reference' => $lot->get_sams_movement_reference()
    )));
    
    $this->assertEquals(3, $lookup_lot->get_species_id());
  }
  
  public function testHeadCountDefaultsToNull() {
    $lot = ScotEID_TestHelpers::buildValidIncompleteLot();
    $lot->set_head_count(null);
    $lot->save();
    $lot = ScotEID_Lot::get($lot->get_sams_movement_reference());
    $this->assertNull($lot->get_head_count());
  }
  
  public function testIncompleteLotSplitsByTimeCorrectly() {
    $lot = ScotEID_TestHelpers::buildValidIncompleteLot();
    
    $t1 = strtotime("14:44");
    $t2 = strtotime("14:50");
    
    $trs = array();
    for($i = 0; $i < 10; $i++) {
      $tr = new ScotEID_TagReading();
      $tr->set_timestamp(($i >= 5 ? $t2 : $t1));
      $tr->set_tag_id(new ScotEID_ISO24631TagID(sprintf("1 0 04 00 0 826 059999%06d", $i)));
      $trs[] = $tr;
    }
    $tr = new ScotEID_TagReading();
    $tr->set_timestamp($t1);
    $tr->set_tag_reading_type(ScotEID_TagReading::TAGREADING_TYPE_MANUAL);
    $trs[] = $tr;
    
    $ftc = count($lot->get_flock_tags());
    
    $lot->set_tag_readings($trs);
    $this->assertTrue($lot->save());
    
    $new_lot = $lot->split_at($t2);
    
    $lot      = ScotEID_Lot::get($lot->get_sams_movement_reference());
    $new_lot  = ScotEID_Lot::get($new_lot->get_sams_movement_reference());
    
    $this->assertNotNull($lot);
    $this->assertNotNull($new_lot);
    
    $this->assertEquals(6, count($lot->get_tag_readings()));
    $this->assertEquals(5, count($new_lot->get_tag_readings()));
    $this->assertEquals($ftc, count($lot->get_flock_tags()));
    $this->assertEquals(0, count($new_lot->get_flock_tags()));
  }

  public function testIncompleteLotsMergeCorrectly() {
    $lot = ScotEID_TestHelpers::buildValidIncompleteLot();
    
    $t1 = strtotime("14:44");
    $t2 = strtotime("14:50");
    
    $trs = array();
    for($i = 0; $i < 10; $i++) {
      $tr = new ScotEID_TagReading();
      $tr->set_timestamp(($i >= 5 ? $t2 : $t1));
      $tr->set_tag_id(new ScotEID_ISO24631TagID(sprintf("1 0 04 00 0 826 059999%06d", $i)));
      $trs[] = $tr;
    }
    $tr = new ScotEID_TagReading();
    $tr->set_timestamp($t1);
    $tr->set_tag_reading_type(ScotEID_TagReading::TAGREADING_TYPE_MANUAL);
    $trs[] = $tr;
    
    $ftc = count($lot->get_flock_tags());
    
    $lot->set_tag_readings($trs);
    $this->assertTrue($lot->save());
    
    $new_lot = $lot->split_at($t2);
    
    $original_lot = ScotEID_Lot::get($lot->get_sams_movement_reference());
    $new_lot      = ScotEID_Lot::get($new_lot->get_sams_movement_reference());
    
    $original_lot->merge($new_lot);
    $merged_lot = ScotEID_Lot::get($original_lot->get_sams_movement_reference());
    
    $this->assertLotsEqual($lot, $merged_lot, array('tag_readings', 'flock_tags'));
    $this->assertEquals(11, count($merged_lot->get_tag_readings()));
    $this->assertEquals(2, count($merged_lot->get_flock_tags()));
  }
  
  public function testSplittingSpecificTagsWorks() {
    $lot = ScotEID_TestHelpers::buildValidIncompleteLot();
    
    $t1 = strtotime("14:44");
    $t2 = strtotime("14:50");
    
    $trs = array();
    for($i = 0; $i < 10; $i++) {
      $tr = new ScotEID_TagReading();
      $tr->set_timestamp(($i >= 5 ? $t2 : $t1));
      $tr->set_tag_id(new ScotEID_ISO24631TagID(sprintf("1 0 04 00 0 826 059999%06d", $i)));
      $trs[] = $tr;
    }
    $tr = new ScotEID_TagReading();
    $tr->set_timestamp($t1);
    $tr->set_tag_reading_type(ScotEID_TagReading::TAGREADING_TYPE_MANUAL);
    $trs[] = $tr;
    
    $ftc = count($lot->get_flock_tags());
    
    $lot->set_tag_readings($trs);
    $this->assertTrue($lot->save());
    
    $remove_tr = array_slice($trs, 0, 5);
    
    $new_lot = $lot->split_tag_readings($remove_tr);

    $lot      = ScotEID_Lot::get($lot->get_sams_movement_reference());
    $new_lot  = ScotEID_Lot::get($new_lot->get_sams_movement_reference());
    
    $this->assertNotNull($lot);
    $this->assertNotNull($new_lot);
    
    $this->assertEquals(6, count($lot->get_tag_readings()));
    $this->assertEquals(5, count($new_lot->get_tag_readings()));
    $this->assertEquals($ftc, count($lot->get_flock_tags()));
    $this->assertEquals(0, count($new_lot->get_flock_tags()));
  }
  
  public function testSplitingLotWithNullDateWorks() {
    $lot = new ScotEID_Lot();
    $lot->set_uid(0);
    $lot->set_species_id(4);
    $lot->set_movement_type_id(4);
    $lot->set_lot_number("0000");
    $lot->set_lot_date(null);
    
    $t1 = strtotime("14:44");
    $t2 = strtotime("14:50");
    
    $trs = array();
    for($i = 0; $i < 10; $i++) {
      $tr = new ScotEID_TagReading();
      $tr->set_timestamp(($i >= 5 ? $t2 : $t1));
      $tr->set_tag_id(new ScotEID_ISO24631TagID(sprintf("1 0 04 00 0 826 059999%06d", $i)));
      $trs[] = $tr;
    }
    $lot->set_tag_readings($trs);
    $this->assertTrue($lot->save(), var_export($lot->get_errors(), true));
    
    $remove_tr = array_slice($trs, 0, 2);
    $new_lot = $lot->split_tag_readings($remove_tr);
    
    $remove_tr = array_slice($trs, 2, 2);
    $new_lot_2 = $lot->split_tag_readings($remove_tr);
    
    $lot1 = ScotEID_Lot::first(array('conditions' => array('sams_movement_reference' => $lot->get_sams_movement_reference())));
    $lot2 = ScotEID_Lot::first(array('conditions' => array('sams_movement_reference' => $new_lot->get_sams_movement_reference())));
    $lot3 = ScotEID_Lot::first(array('conditions' => array('sams_movement_reference' => $new_lot_2->get_sams_movement_reference())));
        
    $this->assertEquals(6, count($lot1->get_tag_readings()));
    $this->assertEquals(2, count($lot2->get_tag_readings()));
    $this->assertEquals(2, count($lot3->get_tag_readings()));
  }
  
  public function testSplittingLotCreatesSensibleLotNumbers() {
    $lot = ScotEID_TestHelpers::buildValidIncompleteLot();
    $lot->set_lot_number("0001");
    $lot->save();
    $new = $lot->split_tag_readings(array_slice($lot->get_tag_readings(), 0, 1));
    $this->assertEquals("0001/A", $new->get_lot_number());  
    
    $lot = ScotEID_TestHelpers::buildValidIncompleteLot();
    $lot->set_lot_number("0002/Z");
    $lot->save();
    $new = $lot->split_tag_readings(array_slice($lot->get_tag_readings(), 0, 1));
    $this->assertEquals("0002/AA", $new->get_lot_number());
    
    $lot = ScotEID_TestHelpers::buildValidIncompleteLot();
    $lot->set_lot_number("0003/AZ");
    $lot->save();
    $new = $lot->split_tag_readings(array_slice($lot->get_tag_readings(), 0, 1));
    $this->assertEquals("0003/BA", $new->get_lot_number());
    
    $lot = ScotEID_TestHelpers::buildValidIncompleteLot();
    $lot->set_lot_number("0004/A/A");
    $lot->save();
    $new = $lot->split_tag_readings(array_slice($lot->get_tag_readings(), 0, 1));
    $this->assertEquals("0004/A/B", $new->get_lot_number());
  }
  
  public function testVisuallyRecordBooleanSaves() 
  {  
    $lot = ScotEID_TestHelpers::buildValidIncompleteLot();
    $lot->set_visually_read(true);
    $this->assertTrue($lot->save());
    
    $lot2 = ScotEID_Lot::find_by_sams_movement_reference($lot->get_sams_movement_reference());
    $this->assertTrue($lot2->get_visually_read());
    $lot2->set_visually_read(false);
    $lot2->save();
    
    $lot3 = ScotEID_Lot::find_by_sams_movement_reference($lot2->get_sams_movement_reference());
    $this->assertFalse($lot3->get_visually_read());
  }
  
  public function testUpdateRetainsTagReadings() {
    $lot = ScotEID_TestHelpers::buildValidIncompleteLot();
    
    $c = count($lot->get_tag_readings());
    
    $this->assertTrue($lot->save());
    $lot2 = new ScotEID_Lot();
    $lot2->set_lot_number("AB123");
    
    $lot = ScotEID_Lot::first(array('sams_movement_reference' => $lot->get_sams_movement_reference()));
    
    $lot->update($lot2, array('tag_readings', 'flock_tags'));
    $this->assertTrue($lot->save());
  
    $lot = ScotEID_Lot::first(array('sams_movement_reference' => $lot->get_sams_movement_reference()));
  
    $this->assertEquals($c, count($lot->get_tag_readings()));
    $this->AssertEquals("AB123", $lot->get_lot_number());
  }
  
  public function testChangingLotNumberRetainsTagReadings() {
    $lot = ScotEID_TestHelpers::buildValidIncompleteLot();
		$c = count($lot->get_tag_readings());
    $this->assertTrue($lot->save());
		
    $lot = ScotEID_Lot::first(array('sams_movement_reference' => $lot->get_sams_movement_reference()));
    $lot->set_lot_number("XXXX");
    $lot->save();
    $this->assertEquals($c, count($lot->get_tag_readings()));
    
    $lot = ScotEID_Lot::first(array('sams_movement_reference' => $lot->get_sams_movement_reference()));
    $this->assertEquals($c, count($lot->get_tag_readings()));
  }
  
  public function testChangingLotNumberAndCompletingRetainsTagReadings() {
    $lot = ScotEID_TestHelpers::buildValidIncompleteLot();
		$lot2 = ScotEID_TestHelpers::buildValidIncompleteLot();
    
    $c = count($lot->get_tag_readings());
		
    $lot->set_lot_number("0001");
    $lot->set_read_location("99/999/9999");
    $lot->set_destination_location("99/999/9999");
    $lot->set_departure_location("99/999/9999");
    $lot->set_head_count($c);
    $lot->set_lot_date(time());

		$this->assertTrue($lot->save());
		
		$lot2->set_lot_number("0002");
    $lot2->set_read_location("99/999/9999");
    $lot2->set_destination_location("99/999/9999");
    $lot2->set_departure_location("99/999/9999");
    $lot2->set_head_count($c);
    $lot2->set_lot_date(time());
    
    $this->assertTrue($lot2->save());
		
		// reload the two lots
    $lot = ScotEID_Lot::first(array('sams_movement_reference' => $lot->get_sams_movement_reference()));
    $lot2 = ScotEID_Lot::first(array('sams_movement_reference' => $lot2->get_sams_movement_reference()));
    
    // make sure tags are still intact		

    // try and give them botht he same lot number
		$lot->set_lot_number("AB124");
    $lot->save();

		$lot2->set_lot_number("AB124");
		$lot2->save();
		
		// now complete these lots
    $this->assertEquals($c, count($lot->get_tag_readings()));
//    $this->assertEquals($c, count($clot1->get_tag_readings()));
		$clot1 = $lot->complete();
    $this->assertTrue($clot1->is_saved(), var_export($clot1->get_errors(), true));

		try {
		  $clot2 = $lot2->complete();
		  $this->assertTrue(false); // should never hit this
		} catch(DuplicateKeyException $ex) {
		}
				
		// now reload them both and make sure their reads are intact
    $lot = ScotEID_CompletedLot::first(array('sams_movement_reference' => $clot1->get_sams_movement_reference()));
    $this->assertEquals($c, count($lot->get_tag_readings()));
    
    $lot2 = ScotEID_Lot::first(array('sams_movement_reference' => $lot->get_sams_movement_reference()));
    $this->assertEquals($c, count($lot2->get_tag_readings()));
  }

}
?>
<?php
require_once './test/test_helper.php';
class ScotEID_CompletedLotTest extends ScotEID_BaseTest
{
  private function buildValidCompleteLot() {
    $lot = new ScotEID_CompletedLot();
    $lot->set_uid(0);
    $lot->set_lot_number("1");
    $lot->set_lot_date(strtotime("today"));
    $lot->set_head_count(1);
    $lot->set_movement_type_id(4);
    $lot->set_departure_location("11/222/3333");
    $lot->set_read_location("22/333/4444");
    $lot->set_destination_location("33/444/5555");
    
    $trs = array();
    for($i = 0; $i < 10; $i++) {
      $tr = new ScotEID_TagReading();
      $tr->set_timestamp(time());
      $tr->set_tag_id(new ScotEID_ISO24631TagID(sprintf("1 0 04 00 0 826 059999%06d", $i)));
      $trs[] = $tr;
    }
    
    $lot->set_tag_readings($trs);
    
    return $lot;
  }
  
  public function testValidCompleteLotSavesSuccessfully() {
    $lot = $this->buildValidCompleteLot();
    $this->assertTrue($lot->save(), var_export($lot->get_errors(), true));
  }
  
  public function testCompleteLotRequiresUID() {
    $lot = $this->buildValidCompleteLot();
    $lot->set_uid(null);
    $this->assertFalse($lot->save());
  }
  
  public function testCompleteLotRequiresLotNumber() {
    $lot = $this->buildValidCompleteLot();
    $lot->set_lot_number("");
    $this->assertFalse($lot->save());
  }
  
  public function testCompleteLotRequiresLotDate() {
    $lot = $this->buildValidCompleteLot();
    $lot->set_lot_date(null);
    $this->assertFalse($lot->save());
  }
  
  public function testCompleteLotRequiresHeadCount() {
    $lot = $this->buildValidCompleteLot();
    $lot->set_head_count(null);
    $this->assertFalse($lot->save());
  }
  
  public function testCompleteLotRequiresMovementType() {
    $lot = $this->buildValidCompleteLot();
    $lot->set_movement_type_id(null);
    $this->assertFalse($lot->save());
  }
  
  public function testFullyPopulatedLotSavesAll() {
  
    $tr_d = array(
      'vehicle_id'                   => 'AB123 HB',
      'departure_assurance_number'   => 1234,
      'departure_name'               => 'Joe Bloggs',
      'departure_business'           => 'Bloggs Farm',
      'departure_address_2'          => 'Foo',
      'departure_address_3'          => 'Somewhere',
      'departure_address_4'          => 'Some country',
      'departure_postcode'           => 'AB1 23F',
      'departure_tel'                => '0182 31923',
      'departure_email'              => 'joe@bloggs.com',
      'departure_slap_mark'          => 'AB123',
      'destination_assurance_number' => 12,
      'destination_name'             => 'Frank Bloggs',
      'destination_business'         => 'Blogs Abattoir',
      'destination_address_2'        => 'qwerty',
      'destination_address_3'        => 'yuiop',
      'destination_address_4'        => 'shsdfhwq',
      'destination_postcode'         => 'AB123 DE',
      'destination_tel'              => '012 32323',
      'destination_email'            => 'frank@bloggs.com',
      'destination_slap_mark'        => 'AB233',
      'haulier_permit_number'        => 123,
      'haulier_name'                 => 'Generic Haulier',
      'expected_duration'            => 2,
      'loading_datetime'             => time(),
      'unloading_datetime'           => time() + 3600,
      // 'departure_time'               => '12:30',
      'individual_ids'               => implode(',', array('AB001', 'AB002', 'AB003'))
    );
    
    $tr = new ScotEID_TransportDetails();
    $tr->extract_all_from($tr_d);
    
    $d = array(
      'lot_number'         => 'AB123',
      'lot_date'           => date('Y-m-d', time()),
      'external_reference' => '1234',
      'species_id'         => 3,
      'batch_mark'         => 'AB1234',
      'movement_type_id'   => 1,
      'lot_description_id' => 2,
      'arrival_date'       => date('Y-m-d', time()),
      'departure_date'     => date('Y-m-d', time()),
      'head_count'         => 10,
      'doa_count'          => 1,
      'all_eid'            => false,
      'visually_read'      => false,
      'departure_location'    => '66/444/5555',
      'destination_location'  => '66/333/4444',
      'read_location'         => '66/444/5555',
      'buyer_invoice_number'  => 123,
      'seller_payment_number' => 234,
    );
    
    $lot = $this->buildValidCompleteLot();
    $lot->set_transport_details($tr);
    
    foreach($d as $k => $v) {
      $lot->set_attribute($k, $v);
    }
    
    $this->assertTrue($lot->save(), var_export($lot->get_errors(), true));
    
    $saved_lot = ScotEID_CompletedLot::get($lot->get_sams_movement_reference());
    
    $this->assertLotsEqual($lot, $saved_lot);
  }
  
  public function testChangingLotDateSucceeds() {
    $lot = $this->buildValidCompleteLot();
    $lot->set_lot_date(strtotime("today"));
    $this->assertTrue($lot->save());
    
    $lot = ScotEID_CompletedLot::get($lot->get_sams_movement_reference());
    $tr = count($lot->get_tag_readings());
    
    $tomorrow = strtotime("tomorrow");
    $lot->set_lot_date($tomorrow);
    
    $lot->save();
    
    $lot = ScotEID_CompletedLot::get($lot->get_sams_movement_reference());
    
    $this->assertEquals($lot->get_lot_date(), $tomorrow);
    $this->assertEquals($tr, count($lot->get_tag_readings()));
  }
  
  public function testChangingReadLocationSucceeds() {
    $lot = $this->buildValidCompleteLot();
    $lot->save();
        
    $lot->set_read_location("11/111/1112");
    $lot->save();
        
    $saved_lot = ScotEID_CompletedLot::get($lot->get_sams_movement_reference());
    $this->assertLotsEqual($lot, $saved_lot);       
  }
  
  public function testUpdatingLotCreatesAuditItem() {
    $lot = $this->buildValidCompleteLot();
    $this->assertTrue($lot->save());
        
    $lot = ScotEID_CompletedLot::get($lot->get_sams_movement_reference());
    
    $this->assertEquals(0, ScotEID_AuditItem::count());
    $lot->set_lot_number("1A");    
  
    $lot->save();
    $this->assertEquals(1, ScotEID_AuditItem::count());
    
    $audit_item = ScotEID_AuditItem::first();
    $d = $audit_item->get_object();
    $this->assertEquals($d['sams_movement_reference'], $lot->get_sams_movement_reference());
    $this->assertEquals($d['lot_number'], array('old' => '1', 'new' => '1A'));
    $this->assertEquals($audit_item->get_sams_movement_reference(), $lot->get_sams_movement_reference());
  }
  
  public function testSheepLotDoesntValidateTransportDetails() {
    $lot1 = ScotEID_TestHelpers::buildValidIncompleteLot();
  }
  
  public function testPigLotNeedsBatchMarkOrIdentifiers() {
    $lot1 = ScotEID_TestHelpers::buildValidPigLot();
    $lot1->set_lot_number("1");
    $lot1->set_pig_identities(array());
    $lot1->set_batch_mark("");
    
    $lot2 = ScotEID_TestHelpers::buildValidPigLot();
    $lot2->set_lot_number("2");
    $lot2->set_pig_identities(array());
    $lot2->set_batch_mark("");
    
    $this->assertFalse($lot1->save());
    $lot1->set_batch_mark("AB123");
    $this->assertTrue($lot1->save(), var_export($lot1->get_errors(), true));
    
    $this->assertFalse($lot2->save());
    $lot2->set_pig_identities(array("AB123", "AB124"));
    $this->assertTrue($lot2->save(), var_export($lot1->get_errors(), true));
  }
  
  public function testSentToBpexDefaultsToFalse() {
    $lot1 = ScotEID_TestHelpers::buildValidPigLot();
    $lot1->set_sent_to_bpex(null);
    $this->assertTrue($lot1->save());
    $lot1 = ScotEID_CompletedLot::get($lot1->get_sams_movement_reference());
    $this->assertEquals(ScotEID_Lot::BPEX_UNSENT, $lot1->get_sent_to_bpex());
  }
  
  public function testSentToBpexResetWhenLotChanged() {
    $lot1 = ScotEID_TestHelpers::buildValidPigLot();
    $this->assertTrue($lot1->save());
  
    $lot1 = ScotEID_CompletedLot::get($lot1->get_sams_movement_reference());
    $lot1->set_sent_to_bpex(ScotEID_Lot::BPEX_SENT);
    $this->assertTrue($lot1->save());
    
    $lot1 = ScotEID_CompletedLot::get($lot1->get_sams_movement_reference());
  
    $this->assertEquals(ScotEID_Lot::BPEX_SENT, $lot1->get_sent_to_bpex());
    $lot1->set_head_count($lot1->get_head_count() + 1);
    $this->assertTrue($lot1->is_dirty());
    $this->assertTrue(!$lot1->sent_to_bpex_changed());
    $this->assertTrue($lot1->save());
    
    $lot1 = ScotEID_CompletedLot::get($lot1->get_sams_movement_reference());
    $this->assertEquals(ScotEID_Lot::BPEX_UNSENT, $lot1->get_sent_to_bpex());    
  }
  
  public function testCompletedLotRequiresLotDate() {
    $lot = new ScotEID_CompletedLot();
    $lot->set_lot_date(null);
    $this->assertFalse($lot->save());
  }
  
  public function testCompletedLotRequresHeadCount() {
    $lot = $this->buildValidCompleteLot();
    $lot->set_lot_number("1");
    $lot->set_head_count(null);
    $this->assertFalse($lot->save());
    
    $lot = $this->buildValidCompleteLot();
    $lot->set_lot_number("2");
    $lot->set_head_count('');
    $this->assertFalse($lot->save());
    
    $lot = $this->buildValidCompleteLot();
    $lot->set_lot_number("3");
    $lot->set_head_count('a');
    $this->assertFalse($lot->save());
  }
  
  public function testCompletedPigLotRequresDOACount() {
    $lot = ScotEID_TestHelpers::buildValidPigLot();
    $lot->set_lot_number("1");
    $lot->set_arrival_date(time());
    $lot->set_doa_count(null);
    $this->assertFalse($lot->save());
    
    $lot = ScotEID_TestHelpers::buildValidPigLot();
    $lot->set_lot_number("2");
    $lot->set_doa_count('');
    $lot->set_arrival_date(time());
    $this->assertFalse($lot->save());
    
    $lot = ScotEID_TestHelpers::buildValidPigLot();
    $lot->set_lot_number("3");
    $lot->set_doa_count('a');
    $lot->set_arrival_date(time());
    $this->assertFalse($lot->save());
    
    $lot->set_doa_count(0);
    $this->assertTrue($lot->save());
  }
  
  public function testCompletingLotWithFlockTagsWorks() {
    $lot = new ScotEID_Lot();
    $lot->set_uid(1);
    $lot->set_lot_number("1");
    $lot->set_movement_type_id(4);
    
    $fts = array();
    $ft = new ScotEID_FlockTag();
    $ft->set_flock_number(599999);
    $ft->set_tag_count(5);
    $fts[] = $ft;
    
    $lot->set_flock_tags($fts);
    $this->assertTrue($lot->save());
    
    $loaded_lot = ScotEID_Lot::first(array('conditions' => array('sams_movement_reference' => $lot->get_sams_movement_reference())));
    $this->assertNotNull($loaded_lot);
    
    $loaded_lot->set_departure_location("99/999/9999");
    $loaded_lot->set_read_location("99/999/9999");
    $loaded_lot->set_destination_location("99/999/9999");
    $loaded_lot->set_head_count(5);
    $loaded_lot->set_lot_date(time());
    
    $completed_lot = $loaded_lot->complete();
    
    $this->assertTrue($completed_lot->is_saved(), var_export($completed_lot->get_errors(), true));
    
    $loaded_complete_lot = ScotEID_CompletedLot::first(array('conditions' => array('sams_movement_reference' => $completed_lot->get_sams_movement_reference())));
    $this->assertNotNull($loaded_complete_lot);
    $this->assertEquals(1, count($loaded_complete_lot->get_flock_tags()));    
  }
  
  public function testPigLotRequiresTwoAddressLinesForDepartureKeeper() {
    $lot = ScotEID_TestHelpers::buildValidPigLot();
    $this->assertTrue($lot->save());
    
    $k = new ScotEID_Keeper();
    $k->set_name('Departure keeper');
    $k->set_address_1('Business');
    $k->set_address_2('Address2');
    $k->set_postcode('G3 8RW');
    $lot->set_departure_keeper($k);
          
    $this->assertTrue($lot->save(), var_export($lot->get_errors(), true));
    
    $k->set_address_2('');
    $lot->set_departure_keeper($k);
    
    $this->assertFalse($lot->save());
    
    $k->set_address_3('Address3');
    $lot->set_departure_keeper($k);
    $this->assertTrue($lot->save());
  }
  
  public function testPigLotLeavingScotlandRequiresBPEXMovementType() {
    $lot = ScotEID_TestHelpers::buildValidPigLot();
    $this->assertTrue($lot->save());
    
    $lot->set_destination_location("11/222/3333");
    $this->assertFalse($lot->save());
    
    $lot->set_bpex_movement_type_id(1);
    $this->assertTrue($lot->save());
  }
  
  public function testPigLotLeavingEnglandRequiresBPEXMovementType() {
    $lot = ScotEID_TestHelpers::buildValidPigLot();
    $this->assertTrue($lot->save());
    
    $lot->set_departure_location("11/222/3333");
    $this->assertFalse($lot->save());
    
    $lot->set_bpex_movement_type_id(1);
    $this->assertTrue($lot->save());
  }
  
  public function testPigLotCannotBeMovedToCertainLocationsOnTempMark() {
    $lot = ScotEID_TestHelpers::buildValidPigLot();
    $lot->get_transport_details()->set_id_type(ScotEID_TransportDetails::ID_TYPE_TEMP);
    $this->assertTrue($lot->save());
    
    $h = new ScotEID_Holding();
    $h->set_cph("66/222/4001");
    $h->set_location_type(ScotEID_Holding::LOCATION_TYPE_FARM);
    $h->save();
    
    $lot->set_destination_location($h);
    $this->assertTrue($lot->save(), var_export($lot->get_errors(), true));
    
    $illegal = array(ScotEID_Holding::LOCATION_TYPE_MART, 
                               ScotEID_Holding::LOCATION_TYPE_ABATTOIR,
                               ScotEID_Holding::LOCATION_TYPE_SHOW,
                               ScotEID_Holding::LOCATION_TYPE_EU_IMPORT_EXPORT);
                               
    foreach($illegal as $i) {
      $h->set_location_type($i);
      $h->save();
  
      $lot->set_destination_location($h);
      $this->assertFalse($lot->save());
    }
  }
  
  public function testCrossBorder() {
    $lot = ScotEID_TestHelpers::buildValidPigLot();
    
    $lot->set_departure_location("80/123/1234");
    $lot->set_destination_location("80/123/1235");
    $this->assertFalse($lot->is_cross_border());
    
    $lot->set_departure_location("40/123/1234");
    $lot->set_destination_location("80/123/1235");
    $this->assertTrue($lot->is_cross_border());
    
    $lot->set_departure_location("80/123/1234");
    $lot->set_destination_location("40/123/1235");
    $this->assertTrue($lot->is_cross_border());
    
    $lot->set_departure_location("40/123/1234");
    $lot->set_destination_location("40/123/1235");
    $this->assertTrue($lot->is_cross_border());
  }
  
  public function tewtNewPigLotsSetUnsent() {
    $lot = ScotEID_TestHelpers::buildValidPigLot();
    $this->assertTrue($lot->save());
    $this->assertEquals(ScotEID_Lot::BPEX_UNSENT, $lot->get_sent_to_bpex());
  }
  
  public function testNewPigLotsFromBpexSetSent() {
    $lot = ScotEID_TestHelpers::buildValidPigLot();
    $bpex = ScotEID_User::first(array('conditions' => array('username' => 'bpex')));
    $lot->set_uid($bpex->get_uid());
    $this->assertTrue($lot->save());
    $this->assertEquals(ScotEID_Lot::BPEX_SENT, $lot->get_sent_to_bpex());
  }
  
  public function testBpexUpdatingOwnPigLotsDoesntChangeStatus() {
    $lot = ScotEID_TestHelpers::buildValidPigLot();
    
    $bpex = ScotEID_User::first(array('conditions' => array('username' => 'bpex')));
    $fieldsman = ScotEID_User::first(array('conditions' => array('username' => 'fieldsman1')));
    
    $lot->set_uid($bpex->get_uid());
    $this->assertEquals($bpex->get_uid(), $lot->get_uid());
   
    $this->assertTrue($lot->save());
    $this->assertEquals(ScotEID_Lot::BPEX_SENT, $lot->get_sent_to_bpex());
        
    $lot2 = ScotEID_CompletedLot::first(array('conditions' => array('sams_movement_reference' => $lot->get_sams_movement_reference())));
    $lot2->set_editing_uid($bpex->get_uid());
    $lot2->set_doa_count(10);
    
    $this->assertEquals($bpex->get_uid(), $lot2->get_uid());
    $this->assertEquals($bpex->get_uid(), $lot2->get_editing_uid());
    $this->assertTrue(ScotEID_User::user_has_role($lot2->get_editing_uid(), ScotEID_User::ROLE_BPEX));
    $this->assertTrue(ScotEID_User::user_has_role($lot2->get_uid(), ScotEID_User::ROLE_BPEX));
        
    $this->assertTrue($lot2->save());
    $this->assertEquals(ScotEID_Lot::BPEX_SENT, $lot2->get_sent_to_bpex());
    
    $lot3 = ScotEID_CompletedLot::first(array('conditions' => array('sams_movement_reference' => $lot->get_sams_movement_reference())));
    $lot3->set_editing_uid($fieldsman->get_uid());
    $lot3->set_doa_count(5);
    $this->assertTrue($lot3->save());
    $this->assertEquals(ScotEID_Lot::BPEX_UNSENT, $lot3->get_sent_to_bpex());
    
    $lot4 = ScotEID_CompletedLot::first(array('conditions' => array('sams_movement_reference' => $lot->get_sams_movement_reference())));
    $lot4->set_editing_uid($fieldsman->get_uid());
    $lot4->set_doa_count(10);
    $this->assertTrue($lot4->save());
    $this->assertEquals(ScotEID_Lot::BPEX_UNSENT, $lot3->get_sent_to_bpex());
  }
  
  public function testOtherUpdatingBPEXLotDoesChangeStatus() {
    $lot = ScotEID_TestHelpers::buildValidPigLot();
    
    $bpex = ScotEID_User::first(array('conditions' => array('username' => 'bpex')));
    $fieldsman = ScotEID_User::first(array('conditions' => array('username' => 'fieldsman1')));
    
    $lot->set_uid($bpex->get_uid());
    $this->assertEquals($bpex->get_uid(), $lot->get_uid());
   
    $this->assertTrue($lot->save());
    
    $lot2 = ScotEID_CompletedLot::first(array('conditions' => array('sams_movement_reference' => $lot->get_sams_movement_reference())));
    $lot2->set_editing_uid($fieldsman->get_uid());
    $lot2->set_doa_count(10);
    
    $this->assertTrue($lot2->save());
    $this->assertEquals(ScotEID_Lot::BPEX_UNSENT, $lot2->get_sent_to_bpex());
  }
  
  public function testBPEXHoldFlagSetForCrossBorderMoves() {
    $lot = ScotEID_TestHelpers::buildValidPigLot();
    
    $lot->set_departure_location("80/123/1234");
    $lot->set_destination_location("12/123/1235");
    $lot->set_bpex_movement_type_id(1);
    
    $lot->get_transport_details()->set_id_type(ScotEID_TransportDetails::ID_TYPE_INDIVIDUAL_IDS);
    $lot->get_transport_details()->set_individual_ids("123\n456");
  
    $this->assertTrue($lot->save());
    $this->assertEquals(ScotEID_Lot::BPEX_HOLD, $lot->get_sent_to_bpex());
  }
  
  public function testBPEXHoldFlagSetForCrossBorderMovesUnlessFieldsman() {
    $lot = ScotEID_TestHelpers::buildValidPigLot();
    
    $fieldsman = ScotEID_User::first(array('conditions' => array('username' => 'fieldsman1')));
    
    $lot->set_departure_location("80/123/1234");
    $lot->set_destination_location("12/123/1235");
    $lot->set_bpex_movement_type_id(1);
    $lot->set_editing_uid($fieldsman->get_uid());
    
    $lot->get_transport_details()->set_id_type(ScotEID_TransportDetails::ID_TYPE_INDIVIDUAL_IDS);
    $lot->get_transport_details()->set_individual_ids("123\n456");
    
    $this->assertTrue($lot->save());
    $this->assertEquals(ScotEID_Lot::BPEX_UNSENT, $lot->get_sent_to_bpex());
  }
  
  public function testBPEXHistoricDataIsHidden() {
    $lot = ScotEID_TestHelpers::buildValidPigLot();
    
    $bpex = ScotEID_User::first(array('conditions' => array('username' => 'bpex')));
    
    $lot->set_uid($bpex->get_uid());
    $lot->set_lot_date(strtotime("2012-03-01"));
    $lot->set_movement_type_id(ScotEID_MovementType::MOVEMENT_TYPE_MART);
    $this->assertTrue($lot->save(), var_export($lot->get_errors(), true));
    
    $lot = ScotEID_CompletedLot::first(
      array('conditions' => array(
        'sams_movement_reference' => $lot->get_sams_movement_reference())
      )
    );
    $this->assertEquals(ScotEID_MovementType::MOVEMENT_TYPE_HIDDEN, $lot->get_movement_type_id());
  }
  
  public function testBPEXNonHistoricDataIsntHidden() {
    $lot = ScotEID_TestHelpers::buildValidPigLot();
    
    $bpex = ScotEID_User::first(array('conditions' => array('username' => 'bpex')));
    
    $lot->set_uid($bpex->get_uid());
    $lot->set_lot_date(strtotime("2012-04-01"));
    $lot->set_movement_type_id(ScotEID_MovementType::MOVEMENT_TYPE_MART);
    $this->assertTrue($lot->save(), var_export($lot->get_errors(), true));
    
    $lot = ScotEID_CompletedLot::first(
      array('conditions' => array(
        'sams_movement_reference' => $lot->get_sams_movement_reference())
      )
    );
    $this->assertEquals(ScotEID_MovementType::MOVEMENT_TYPE_MART, $lot->get_movement_type_id());
  }
  
  public function testONLYBPEXHistoricDataIsHidden() {
    $lot = ScotEID_TestHelpers::buildValidPigLot();
    
    $bpex = ScotEID_User::first(array('conditions' => array('username' => 'bpex')));
    
    //$lot->set_uid($bpex->get_uid());
    $lot->set_lot_date(strtotime("2012-03-01"));
    $lot->set_movement_type_id(ScotEID_MovementType::MOVEMENT_TYPE_MART);
    $this->assertTrue($lot->save(), var_export($lot->get_errors(), true));
    
    $lot = ScotEID_CompletedLot::first(
      array('conditions' => array(
        'sams_movement_reference' => $lot->get_sams_movement_reference())
      )
    );
    $this->assertEquals(ScotEID_MovementType::MOVEMENT_TYPE_MART, $lot->get_movement_type_id());
  }
  
  public function testBPEXUpdatableProperties()
  {
    $lot = ScotEID_TestHelpers::buildValidPigLot();
    $lot->save();
    
    $bpex = ScotEID_User::first(array('conditions' => array('username' => 'bpex')));
    
    $updates = new ScotEID_Lot();
    $updates->set_arrival_date(time());
    $updates->set_doa_count(10);
    $updates->set_departure_location('67/777/7778');
    
    $lot->set_editing_uid($bpex->get_uid());
    
    $lot->update($updates);
    
    $this->assertEquals($updates->get_arrival_date(), $lot->get_arrival_date());
    $this->assertEquals($updates->get_doa_count(), $lot->get_doa_count());
    $this->assertNotEquals($updates->get_departure_location(), $lot->get_departure_location());
  }
  
  public function testCancellingLot() {
    $lot = ScotEID_TestHelpers::buildValidCompletedLot();
    $lot->save();
    
    $lot->cancel();
    
    $lot2 = ScotEID_CompletedLot::first(array('conditions' => array('sams_movement_reference' => $lot->get_sams_movement_reference())));
    
    $this->assertEquals(10, strlen($lot2->get_lot_number()));
    $this->assertEquals(ScotEID_MovementType::MOVEMENT_TYPE_HIDDEN, $lot2->get_movement_type_id());

  }
}
?>
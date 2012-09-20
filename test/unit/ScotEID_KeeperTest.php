<?php
require_once './test/test_helper.php';
class ScotEID_KeeperTest extends ScotEID_BaseTest
{
  private function buildValidKeeper() {
    $k = new ScotEID_Keeper();
    $k->set_uid(0);
    $k->set_name('Test keeper');
    $k->set_address_1('Address 1');
    $k->set_address_4('Address 4');
    $k->set_postcode('G3 8RW');
    $k->set_landline_tel('0141 225 0650');
    return $k;
  }
  
  public function testKeeperRequiresTwoLinesOfAddress() {
    $k = $this->buildValidKeeper();
    $this->assertTrue($k->save(), var_export($k->get_errors(), true));
    
    $k->set_address_4('  ');
    $this->assertFalse($k->save());
  
    $k->set_address_2('Address2');
    $this->assertTrue($k->save());
  
    $k->set_address_1('');
    $this->assertFalse($k->save());
  }
  
  public function testNewPigKeeperHasPigRoleAdded() {
    $u = new ScotEID_User();
    $u->set_username('test4');
    $u->set_password('foobar');
    $this->assertTrue($u->save());
    
    $k = new ScotEID_Keeper();
    $k->set_uid($u->get_uid());
    $k->set_name("Test Keeper");
    $k->set_postcode("G3 8RW");
    $k->set_landline_tel("12345");
    $k->set_address_1("Address1");
    $k->set_address_2("Address2");
    
    $h = new ScotEID_KeeperHolding();
    $h->set_cph("66/001/9999");
    $h->set_pigs(10);
    $h->set_pig_herd_number("AB1234");
    
    $k->set_keeper_holdings(array($h));
    
    $this->assertTrue($k->save(), var_export($h->get_errors(), true));
    $this->assertTrue(ScotEID_User::user_has_role($u->get_uid(), ScotEID_User::ROLE_PIG_FARM));
    $this->assertFalse(ScotEID_User::user_has_role($u->get_uid(), ScotEID_User::ROLE_PARTNER_FARM));
  }
  
  public function testNewSheepKeeperHasSheepRoleAdded() {
    $u = new ScotEID_User();
    $u->set_username('test4');
    $u->set_password('foobar');
    $this->assertTrue($u->save());
    
    $k = new ScotEID_Keeper();
    $k->set_uid($u->get_uid());
    $k->set_name("Test Keeper");
    $k->set_postcode("G3 8RW");
    $k->set_landline_tel("12345");
    $k->set_address_1("Address1");
    $k->set_address_2("Address2");
    
    $h = new ScotEID_KeeperHolding();
    $h->set_cph("66/001/9999");
    $h->set_sheep(100);
    $h->set_sheep_flock_number(543210);
    
    $k->set_keeper_holdings(array($h));
    
    $this->assertTrue($k->save(), var_export($h->get_errors(), true));
    $this->assertTrue(ScotEID_User::user_has_role($u->get_uid(), ScotEID_User::ROLE_PARTNER_FARM));
    $this->assertFalse(ScotEID_User::user_has_role($u->get_uid(), ScotEID_User::ROLE_PIG_FARM));
  }
  
  public function testNewKeeperHasMultipleRolesAdded() {
    $u = new ScotEID_User();
    $u->set_username('test4');
    $u->set_password('foobar');
    $this->assertTrue($u->save());
    
    $k = new ScotEID_Keeper();
    $k->set_uid($u->get_uid());
    $k->set_name("Test Keeper");
    $k->set_postcode("G3 8RW");
    $k->set_landline_tel("12345");
    $k->set_address_1("Address1");
    $k->set_address_2("Address2");
    
    $h = new ScotEID_KeeperHolding();
    $h->set_cph("66/001/9999");
    $h->set_sheep(100);
    $h->set_sheep_flock_number(543210);
    
    $h2 = new ScotEID_KeeperHolding();
    $h2->set_cph("66/001/9998");
    $h2->set_pigs(10);
    $h2->set_pig_herd_number("AB3456");
    
    $k->set_keeper_holdings(array($h, $h2));
    
    $this->assertTrue($k->save(), var_export($h->get_errors(), true));
    $this->assertTrue(ScotEID_User::user_has_role($u->get_uid(), ScotEID_User::ROLE_PARTNER_FARM));
    $this->assertTrue(ScotEID_User::user_has_role($u->get_uid(), ScotEID_User::ROLE_PIG_FARM));
  }
  
  public function testOldHoldingsAreDeleted() {
    $u = new ScotEID_User();
    $u->set_username('test5');
    $u->set_password('foobar');
    $this->assertTrue($u->save());
    
    $k = new ScotEID_Keeper();
    $k->set_uid($u->get_uid());
    $k->set_name("Test Keeper");
    $k->set_postcode("G3 8RW");
    $k->set_landline_tel("12345");
    $k->set_address_1("Address1");
    $k->set_address_2("Address2");
    
    $h = new ScotEID_KeeperHolding();
    $h->set_cph("66/001/9999");
    $h->set_sheep(100);
    $h->set_sheep_flock_number(543210);
    
    $h2 = new ScotEID_KeeperHolding();
    $h2->set_cph("66/001/9998");
    $h2->set_pigs(10);
    $h2->set_pig_herd_number("AB3456");
    
    $k->set_keeper_holdings(array($h, $h2));
    
    $this->assertTrue($k->save(), var_export($k->get_errors(), true));
    
    // reload the keeper, assert they have 2 holdings, remove one and save
    $k = ScotEID_Keeper::first(array('conditions' => array('uid' => $k->get_uid())));
    $this->assertEquals(2, count($k->get_keeper_holdings()));
    $k->set_keeper_holdings(array($h));
    $this->assertTrue($k->save(), var_export($k->get_errors(), true));
    
    // reload and ensure we're down to 1 holding
    $k = ScotEID_Keeper::first(array('conditions' => array('uid' => $k->get_uid())));
    $this->assertEquals(1, count($k->get_keeper_holdings()));
    $this->assertTrue($k->save(), var_export($k->get_errors(), true));
    
    // reload and save
    $k = ScotEID_Keeper::first(array('conditions' => array('uid' => $k->get_uid())));
    $this->assertTrue($k->save(), var_export($k->get_errors(), true));
    
    // reload and ensure we stil have 1 holding
    $k = ScotEID_Keeper::first(array('conditions' => array('uid' => $k->get_uid())));
    $this->assertEquals(1, count($k->get_keeper_holdings()));
    $this->assertTrue($k->save(), var_export($k->get_errors(), true));
  }
  
  public function testOldHoldingsAreDeletedUsingArrays() {
    $u = new ScotEID_User();
    $u->set_username('test6');
    $u->set_password('foobar');
    $this->assertTrue($u->save());
    
    $k = new ScotEID_Keeper();
    $k->set_uid($u->get_uid());
    $k->set_name("Test Keeper");
    $k->set_postcode("G3 8RW");
    $k->set_landline_tel("12345");
    $k->set_address_1("Address1");
    $k->set_address_2("Address2");
    
    $h  = array('cph' => '66/001/9999', 'sheep' => 100, 'sheep_flock_number' => 543210);
    $h2 = array('cph' => '66/001/9998', 'pigs' => 10, 'pig_herd_number' => "AB3456");
        
    $k->set_keeper_holdings(array($h, $h2));
    
    $this->assertTrue($k->save(), var_export($k->get_errors(), true));
    
    // reload the keeper, assert they have 2 holdings, remove one and save
    $k = ScotEID_Keeper::first(array('conditions' => array('uid' => $k->get_uid())));
    $this->assertEquals(2, count($k->get_keeper_holdings()));
    $k->set_keeper_holdings(array($h));
    $this->assertTrue($k->save(), var_export($k->get_errors(), true));
    
    // reload and ensure we're down to 1 holding
    $k = ScotEID_Keeper::first(array('conditions' => array('uid' => $k->get_uid())));
    $this->assertEquals(1, count($k->get_keeper_holdings()));
    $this->assertTrue($k->save(), var_export($k->get_errors(), true));
    
    // reload and save
    $k = ScotEID_Keeper::first(array('conditions' => array('uid' => $k->get_uid())));
    $this->assertTrue($k->save(), var_export($k->get_errors(), true));
    
    // reload and ensure we stil have 1 holding
    $k = ScotEID_Keeper::first(array('conditions' => array('uid' => $k->get_uid())));
    $this->assertEquals(1, count($k->get_keeper_holdings()));
    $this->assertTrue($k->save(), var_export($k->get_errors(), true));
  }
  
  public function testOldHoldingsAreDeletedUsingArrayExtractPublic() {
    $u = new ScotEID_User();
    $u->set_username('test7');
    $u->set_password('foobar');
    $this->assertTrue($u->save());
    
    $k = new ScotEID_Keeper();
    $k->set_uid($u->get_uid());
    $k->set_name("Test Keeper");
    $k->set_postcode("G3 8RW");
    $k->set_landline_tel("12345");
    $k->set_address_1("Address1");
    $k->set_address_2("Address2");
    
    $h  = array('cph' => '66/001/9999', 'sheep' => 100, 'sheep_flock_number' => 543210);
    $h2 = array('cph' => '66/001/9998', 'pigs' => 10, 'pig_herd_number' => "AB3456");
        
    $k->extract_public_from(array('keeper_holdings' => array($h, $h2)));
    
    $this->assertTrue($k->save(), var_export($k->get_errors(), true));
    
    // reload the keeper, assert they have 2 holdings, remove one and save
    $k = ScotEID_Keeper::first(array('conditions' => array('uid' => $k->get_uid())));
    $this->assertEquals(2, count($k->get_keeper_holdings()));
    $k->extract_public_from(array('keeper_holdings' => array($h)));
    $this->assertTrue($k->save(), var_export($k->get_errors(), true));
    
    // reload and ensure we're down to 1 holding
    $k = ScotEID_Keeper::first(array('conditions' => array('uid' => $k->get_uid())));
    $this->assertEquals(1, count($k->get_keeper_holdings()));
    $this->assertTrue($k->save(), var_export($k->get_errors(), true));
    
    // reload and save
    $k = ScotEID_Keeper::first(array('conditions' => array('uid' => $k->get_uid())));
    $this->assertTrue($k->save(), var_export($k->get_errors(), true));
    
    // reload and ensure we stil have 1 holding
    $k = ScotEID_Keeper::first(array('conditions' => array('uid' => $k->get_uid())));
    $this->assertEquals(1, count($k->get_keeper_holdings()));
    $this->assertTrue($k->save(), var_export($k->get_errors(), true));
  }
}
?>
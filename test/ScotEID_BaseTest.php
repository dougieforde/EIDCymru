<?php
class ScotEID_BaseTest extends PHPUnit_Framework_TestCase
{
  protected static $_TEST_USERS = array(
    array('test1', 'password1'),
    array('test2', 'password2'),
    array('test3', 'password3'),
    array('fieldsman1', 'password1'),
    array('bpex', 'password1')
  );
  
  protected function setUp() {    
    //
    // tear down data in templots, tempitemread, tblsslots, tblsheepitemread, keepers, keeper_holdings etc
    
    $teardown = array(
      'tempitemread', 
      'templots', 
      'tblsheepitemread', 
      'tblsslots', 
      'keepers', 
      'keeper_holdings', 
      'tblaudititems',
      'tblindividualsheepeidregister',
      ScotEID_User::get_table_name(), 
      ScotEID_User::get_database_name() . '.users_roles', 
      'tblaudititems',
      'tblsamholdings',
      'ccppp_names',
      'transport_details'
    );
    
    foreach($teardown as $table) {
      dbw_query("TRUNCATE $table");
    }
    
    //
    // create some test users

    foreach(static::$_TEST_USERS as $user) {
      $u = new ScotEID_User();
      $u->set_username($user[0]);
      $u->set_password($user[1]);
      $u->save();
    }
    
    $d = ScotEID_User::get_database_name();
    
    dbw_query("INSERT $d.users_roles(uid, rid) SELECT uid, (SELECT rid FROM $d.role WHERE name = '" . ScotEID_User::ROLE_FIELDSMAN . "') FROM $d.users WHERE name = 'fieldsman1';");
    dbw_query("INSERT $d.users_roles(uid, rid) SELECT uid, (SELECT rid FROM $d.role WHERE name = '" . ScotEID_User::ROLE_BPEX . "') FROM $d.users WHERE name = 'bpex';");
    
    //
    // insert some valid county/parish combinations
    
    dbw_query("INSERT ccppp_names(CC,PPP) VALUES (98,863);");
    dbw_query("INSERT ccppp_names(CC,PPP) VALUES (66,001);");
    
  }
  
  protected function assertLotsEqual($lot1, $lot2, $skip = array()) {
    $this->assertModelsEqual($lot1, $lot2, array("read_count"));

    if(!in_array('tag_readings', $skip))
      $this->assertEquals(count($lot1->get_tag_readings()), count($lot2->get_tag_readings()));
      
    if(!in_array('flock_tags', $skip))
      $this->assertEquals(count($lot1->get_flock_tags()), count($lot2->get_flock_tags()));
    
    if(!in_array('transport_details', $skip))
      $this->assertModelsEqual($lot1->get_transport_details(), $lot2->get_transport_details());
  }

  protected function assertModelsEqual($m1, $m2, $skip = array()) {
    if(($m1 && !$m2) || (!$m1 && $m2)) {
      $this->assertTrue(false, "models not equal");
    } else if($m1 && $m2) {
      foreach($m1->get_attributes() as $attribute) {
        if(in_array($attribute, $skip)) continue;
        $this->assertEquals($m1->get_attribute($attribute), $m2->get_attribute($attribute), "$attribute not equal");
      }
    }
  }
}
?>
<?php
require_once './test/test_helper.php';

class ScotEID_TagIDTest extends ScotEID_BaseTest
{ 
  public function testCountryCode() {        
    for($i = 0; $i <= 999; $i++) {
      $cc = sprintf("%03d", $i);
      $tag = new ScotEID_ISO24631TagID("1 0 00 00 0 $cc 000000000000");
      $this->assertEquals($i, $tag->get_country_code());
    }
  }
}
?>
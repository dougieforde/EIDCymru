<?php
require_once './test/test_helper.php';

class ScotEID_HoldingTest extends ScotEID_BaseTest
{ 
  public function testLandlessKeeper() {
    $h = new ScotEID_Holding("12"); // invalid
    $this->assertFalse($h->is_landless_keeper());
    
    $h = new ScotEID_Holding("12/345/6789");
    $this->assertFalse($h->is_landless_keeper());
    
    $h = new ScotEID_Holding("12/345/7234");
    $this->assertTrue($h->is_landless_keeper());
  }
}
?>
<?php
require_once './test/test_helper.php';
class ScotEID_KeeperHoldingTest extends ScotEID_BaseTest
{
  private function buildValidKeeperHolding() {
    $kh = new ScotEID_KeeperHolding();
    $kh->set_cph("98/863/3333");
    $kh->set_sheep_flock_number(543210);
    return $kh;
  }
  
  public function testBuildValidKeeper() {
    $kh = $this->buildValidKeeperHolding();
    $this->assertTrue($kh->validate(), var_export($kh->get_errors(), true));
  }
  
  public function testHerdMarkFormatIsValidated() {
    $kh = $this->buildValidKeeperHolding();
    
    $kh->set_sheep_flock_number(null);
    $this->assertFalse($kh->validate());
    
    $kh->set_pig_herd_number("123");
    $this->assertFalse($kh->validate());
    
    $kh->set_pig_herd_number("A123");
    $this->assertFalse($kh->validate());
    
    $kh->set_pig_herd_number("A1234");
    $this->assertTrue($kh->validate());
    
    $kh->set_pig_herd_number("AB12345");
    $this->assertFalse($kh->validate());
    
    $kh->set_pig_herd_number("AB1234");
    $this->assertTrue($kh->validate());
    
    $kh->set_pig_herd_number("ABC1234567");
    $this->assertFalse($kh->validate());
  }
}
?>
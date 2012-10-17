<?php
require_once './test/test_helper.php';

class ScotEID_TransportDetailsTest extends ScotEID_BaseTest
{ 
  public function testGetIndividualIdsArraySanitizes() {
    $t = new ScotEID_TransportDetails();
    
    $t->set_individual_ids("a,b ,c");
    $this->assertEquals(array("a","b","c"), $t->get_individual_ids_array());
    
    $t->set_individual_ids("a\nb  \n");
    $this->assertEquals(array("a","b"), $t->get_individual_ids_array());
  }
}
?>
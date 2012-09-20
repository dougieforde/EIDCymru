<?php
require_once './test/test_helper.php';

class ScotEID_CreateIncompleteLotTest extends ScotEID_BaseTest
{
  private $request;
  
  private function setRequestHeaders($request) {
    $request->ApplicationName     = 'Internal Test Application';
    $request->ApplicationVersion  = '1.0';
    $request->Timestamp           = time();
    $request->SchemaVersion       = '1.4';
  }
  
  private function buildSampleTagReadings() {
    $trs = array();
    for($i = 0; $i < 10; $i++) {
      $tr = new ScotEID_TagReading();
      $tr->set_timestamp(time());
      $tr->set_tag_id(new ScotEID_ISO24631TagID(sprintf("1 0 04 00 0 826 059999%06d", $i)));
      $trs[] = $tr;
    }
    return $trs;
  }
  
  private function buildValidLotData() {
    return array(
      'LotNumber'     => '0001',
      'LotDate'       => '2011-01-01',
      'MovementType'  => 4,
      'HeadCount'     => 0,
      'AllEID'        => true,
      'ReadLocation'  => '99/999/9999',
      'TagReadings'   => $this->buildSampleTagReadings()
    );
  }
  
  protected function setUp() {
    parent::setUp();
    $this->request = new ScotEID_CreateIncompleteLotRequest();
    $this->setRequestHeaders($this->request);
    $_SERVER['PHP_AUTH_USER'] = ScotEID_BaseTest::$_TEST_USERS[0][0];
    $_SERVER['PHP_AUTH_PW']   = ScotEID_BaseTest::$_TEST_USERS[0][1];
  }
  
  /**
   * @expectedException ScotEID_AuthenticationError
   */
  public function testAuthenticationRequired() {
    $_SERVER['PHP_AUTH_USER'] = 'abcdef';
    $_SERVER['PHP_AUTH_PW']   = ScotEID_BaseTest::$_TEST_USERS[0][1];
    
    $d = $this->buildValidLotData();
    
    $this->request->Lot = $d;

    $this->request->before();
    $response = $this->request->handle();
    $this->assertTrue($response instanceof ScotEID_CreateIncompleteLotResponse);
  }
  
  public function testCreatingIncompleteLotSucceeds() {
    $d = $this->buildValidLotData();
    
    $this->request->Lot = $d;

    $this->request->before();
    $response = $this->request->handle();
    $this->assertTrue($response instanceof ScotEID_CreateIncompleteLotResponse);
    $this->assertEquals(1, ScotEID_Lot::count());

    // TODO: do a complete check of one lot vs the other
  }
  
  /**
   * @expectedException ScotEID_LotValidationFault
   */
  public function testIncompleteLotRequiresLotNumber() {
    $d = $this->buildValidLotData();
    unset($d['LotNumber']);
    $this->request->Lot = $d;
    
    $this->request->before();
    $response = $this->request->handle();
  }
  
  /**
   * @expectedException ScotEID_LotValidationFault
   */
  public function testIncompleteLotRequiresMovementType() {
    $d = $this->buildValidLotData();
    unset($d['MovementType']);
    $this->request->Lot = $d;
    
    $this->request->before();
    $response = $this->request->handle();
  }
}
?>
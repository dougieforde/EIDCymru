<?php
class ScotEID_GetIncompleteLotRequest extends ScotEID_AbstractRequest
{
  public $LotNumber;
  public $LotDate;
  
  public function before() {
    parent::before();
    $this->LotDate = empty($this->LotDate) ? null : strtotime($this->LotDate);
  }
  
  public function handle() {
    if(empty($this->LotNumber) || empty($this->LotDate)) {
      throw new ScotEID_LotNotFoundFault();
    }
    $lot = ScotEID_Lot::first(array('conditions' => array(
      'lot_number' => $this->LotNumber,
      'lot_date'   => $this->LotDate,
      'uid'        => $this->get_uid()
    )));
    if($lot == null) {
      throw new ScotEID_LotNotFoundFault();
    }
    $lot->load_tag_readings();
    $lot->load_flock_tags();
    $response = new ScotEID_GetIncompleteLotResponse();
    $response->Lot = $this->prepare_lot($lot);
    
    // fix for no tag readings - make sure that an empty element
    // is included in the response
    // only doing this here because the problem is specific to TGL
    // and the only use this method for downloading lots
    
    if($response->Lot['TagReadings'] === null) {
      $response->Lot['TagReadings'] = array();
    }

    return $response;
  }
}
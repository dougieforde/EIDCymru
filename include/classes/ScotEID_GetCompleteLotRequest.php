<?php
class ScotEID_GetCompleteLotRequest extends ScotEID_AbstractRequest
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
    $lot = ScotEID_CompletedLot::first(array('conditions' => array(
      'lot_number'          => $this->LotNumber,
      'lot_date'            => $this->LotDate,
      'uid'                 => $this->get_uid(),
      'movement_type_id.ne' => ScotEID_MovementType::MOVEMENT_TYPE_HIDDEN
    )));
    
    if(!$lot) {
      throw new ScotEID_LotNotFoundFault();
    }
    
    $response = new ScotEID_GetCompleteLotResponse();
    $response->Lot = $this->prepare_lot($lot);
    return $response;
  }
}
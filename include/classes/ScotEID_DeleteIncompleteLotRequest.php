<?php
class ScotEID_DeleteIncompleteLotRequest extends ScotEID_AbstractRequest
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
   if($lot === null) {
     throw new ScotEID_LotNotFoundFault();
   }
    $lot->delete();
    $response = new ScotEID_DeleteIncompleteLotResponse();
    return $response;
  }
}
?>
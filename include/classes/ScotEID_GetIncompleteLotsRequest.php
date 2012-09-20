<?php
class ScotEID_GetIncompleteLotsRequest extends ScotEID_AbstractRequest
{
  public $LotDate   = null;
  
  public function before() {
    parent::before();
    $this->LotDate = strtotime($this->LotDate);
  }
  
  public function handle() {
    $response = new ScotEID_FindIncompleteLotsResponse();
    $response->Lots = ScotEID_Lot::find(array('conditions' => array(
      'uid'      => $this->get_uid(),
      'lot_date' => $this->LotDate
    )));
    $response->Lots = $this->prepare_lots($response->Lots);
    return $response;
  }
}
?>
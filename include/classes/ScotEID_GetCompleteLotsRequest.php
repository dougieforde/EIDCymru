<?php
class ScotEID_GetCompleteLotsRequest extends ScotEID_AbstractRequest
{
  public $LotDate   = null;
  
  public function before() {
    parent::before();
    $this->LotDate = strtotime($this->LotDate);
  }
  
  public function handle() {
    $response = new ScotEID_GetCompleteLotsResponse();
    $response->Lots = ScotEID_CompletedLot::find(array('conditions' => array(
      'uid'                 => $this->get_uid(),
      'lot_date'            => $this->LotDate,
      'movement_type_id.ne' => ScotEID_MovementType::MOVEMENT_TYPE_HIDDEN
    )));
    // OPTIMIZE
    foreach($response->Lots as $lot) {
      $lot->load_tag_readings();
      $lot->load_flock_tags();
    }
    $response->Lots = $this->prepare_lots($response->Lots);
    return $response;
  }
}
?>
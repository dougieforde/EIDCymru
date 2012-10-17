<?php
class ScotEID_FindCompleteLotsRequest extends ScotEID_AbstractRequest
{
  public $LotDate   = null;
  
  public function before() {
    parent::before();
    $this->LotDate = strtotime($this->LotDate);
  }
  
  public function handle() {
    $response = new ScotEID_FindCompleteLotsResponse();
    $response->Lots = ScotEID_CompletedLot::find(array('conditions' => array(
      'uid'                   => $this->get_uid(),
      'lot_date'              => $this->LotDate,
      'movement_type_id.ne'   => ScotEID_MovementType::MOVEMENT_TYPE_HIDDEN
    )));
    $response->Lots = $this->prepare_lots($response->Lots, array('tag_readings' => false, 'flock_tags' => false));
    return $response;
  }
}
?>
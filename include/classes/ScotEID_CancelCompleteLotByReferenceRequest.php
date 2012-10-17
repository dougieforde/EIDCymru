<?php
class ScotEID_CancelCompleteLotByReferenceRequest extends ScotEID_AbstractRequest
{
  public $LotReference;
  
  public function before() {
    parent::before();
  }
  
  public function handle() {
    if(empty($this->LotReference)) {
      throw new ScotEID_LotNotFoundFault();
    }
    
    $lot = ScotEID_CompletedLot::first(array('conditions' => array(
      'sams_movement_reference' => $this->LotReference,
      'movement_type_id.ne'     => ScotEID_MovementType::MOVEMENT_TYPE_HIDDEN
    )));
    
    if(!$lot) {
      throw new ScotEID_LotNotFoundFault();
    }
    
    if($lot->is_editable_by($this->get_uid())) {
      $lot->cancel();
      return new ScotEID_CancelCompleteLotByReferenceResponse();
    } else {
      throw new ScotEID_SecurityFault();
    }
  }
}
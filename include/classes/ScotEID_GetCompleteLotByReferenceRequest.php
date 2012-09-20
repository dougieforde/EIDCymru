<?php
class ScotEID_GetCompleteLotByReferenceRequest extends ScotEID_AbstractRequest
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
    
    # the role check here is a hack to make sure that privileged users can't download
    # the entire database using webservices
    if($lot->is_downloadable_by($this->get_uid()) && 
       (
         ScotEID_User::user_has_role($this->get_uid(), array('SuperUser')) || 
         !ScotEID_User::user_has_role($this->get_uid(), array('SuperUser', 'Fieldsman', 'RPID_user', 'Local_Auth'))
       )
      ) {
      $lot->load_tag_readings();
      $lot->load_flock_tags();
      $response      = new ScotEID_GetCompleteLotByReferenceResponse();
      $response->Lot = $this->prepare_lot($lot);
      return $response;  
    } else {
      throw new ScotEID_SecurityFault();
    }
  }
}
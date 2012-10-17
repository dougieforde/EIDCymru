<?php
class ScotEID_GetAnimalsOnHoldingRequest extends ScotEID_AbstractRequest
{
  public $HoldingNumber = null;
  
  public function before() {
    parent::before();
    
    $holding = ScotEID_Holding::try_parse($this->HoldingNumber);
    
    if(!$holding->valid() || !ScotEID_User::is_registered_holding($this->get_uid(), $holding)) {
      throw new ScotEID_SecurityFault("Not a registered holding");
    }
  }
  
  public function handle() {
    $response = new ScotEID_GetAnimalsOnHoldingResponse();
    $response->Animals = ScotEID_Animal::find(array('conditions' => array(
      'last_destination_location' => $this->HoldingNumber,
      'last_movement_type.ne'     => 5
    )));
    return $response;
  }
}
?>
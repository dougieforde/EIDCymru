<?php
class ScotEID_CreateCompleteLotRequest extends ScotEID_AbstractRequest
{
  public $Lot = null;

  public function before() {
    parent::before();
    $this->Lot = $this->extract_lot($this->Lot);
    $this->Lot->set_uid($this->get_uid());
  }

  public function handle() {
    if(empty($this->Lot) || !($this->Lot instanceof ScotEID_Lot)) {
      throw new Exception("Lot missing");
    }
    
    try {
      $completed_lot = $this->Lot->complete();
        
      if($completed_lot->is_saved()) {
        $response = new ScotEID_CreateCompleteLotResponse();
        if(!$this->is_schema_version_gte('1.4')) {
          $response->Lot = $this->prepare_lot($completed_lot, array('tag_readings' => false, 'flock_tags' => false));
        } else {
          $response->Lot = $this->prepare_lot($completed_lot);
        }
        return $response;
      } else {
        throw new ScotEID_LotValidationFault($completed_lot);
      }    
    } catch(DuplicateKeyException $ex) {
      throw new ScotEID_LotAlreadyExistsFault();
    }
  }
  
  public function handleTest() {
    return new ScotEID_CreateCompleteLotResponse();
  }
}
?>
<?php
class ScotEID_CreateIncompleteLotRequest extends ScotEID_AbstractRequest
{
  public $Lot = null;

  public function before() {
    parent::before();
    $this->Lot = $this->extract_lot($this->Lot);
  }

  public function handle() {
    
    // Have to manually check that a duplicate lot doesn't already exist
    // but it might be better if filename wasn't part of the primary key
    // for an incomplete lot; then we could rely on a DuplicateKeyException
    // from the db wrapper
      
    $existing_lot = null;
    try {
            
      $existing_lot = ScotEID_Lot::first(array('conditions' => array(
        'lot_number' => $this->Lot->get_lot_number(),
        'lot_date'   => $this->Lot->get_lot_date(),
        'uid'        => $this->get_uid()
      )));
      
    } catch(Exception $ex) {}
    
    if($existing_lot !== null) {
      throw new ScotEID_LotAlreadyExistsFault();
    }
    
    if(empty($this->Lot) || !($this->Lot instanceof ScotEID_Lot)) {
      throw new Exception("Lot missing");
    }
    
    $this->Lot->set_uid($this->get_uid());
    
    if($this->Lot->save()) {
      try {
        $response = new ScotEID_CreateIncompleteLotResponse();
        if($this->is_schema_version_gte('1.2.3')) {
            $this->Lot->set_tag_readings(null);
            $this->Lot->set_flock_tags(null);
            $response->Lot = $this->prepare_lot($this->Lot, array('tag_readings' => false, 'flock_tags' => false));
        }
        return $response;
      } catch(DuplicateKeyException $ex) {
        throw new ScotEID_LotAlreadyExistsFault();
      }
    } else {
      throw new ScotEID_LotValidationFault($this->Lot);
    }
  }
  
  public function handleTest() {
    return new ScotEID_CreateIncompleteLotResponse();
  }
}
?>
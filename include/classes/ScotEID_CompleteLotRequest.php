<?php
class ScotEID_CompleteLotRequest extends ScotEID_AbstractRequest
{
  public $LotNumber;
  public $LotDate;
  public $Lot;
  
  public function before() {
    parent::before();
    $this->LotDate = empty($this->LotDate) ? null : strtotime($this->LotDate);
    $this->Lot     = $this->extract_lot($this->Lot);
  }
  
  public function handle() {
    //
    // Find the lot and update it with any elements supplied in this request      
    $lot = ScotEID_Lot::first(array('conditions' => array(
      'lot_number' => $this->LotNumber,
      'lot_date'   => $this->LotDate,
      'uid'        => $this->get_uid()
    )));
        
    if(!$lot) {
      throw new ScotEID_LotNotFoundFault();
    }
    
    if(!empty($this->Lot)) {
      //
      // When completing a lot, tags can't be updated
      $tr = $this->Lot->get_tag_readings();        
      if(!empty($tr)) {    
        $lot->add_error('tag_readings', 'cannot be updated');      
        $f = new ScotEID_LotValidationFault($lot);
        throw $f;
      }       
       
      $lot->update($this->Lot, array('tag_readings', 'flock_tags'));
    }
    
    //
    // Attempt to complete the lot, throwing an already exists fault if a duplicate
    // key exception is raised by the db wrapper
    try {
      $completed_lot = $lot->complete();
    } catch(DuplicateKeyException $ex) {
      throw new ScotEID_LotAlreadyExistsFault();
    }     
             
    if($completed_lot->is_saved()) {
      $response      = new ScotEID_CompleteLotResponse();
      $response->Lot = $this->prepare_lot($completed_lot);
      return $response;
    } else {
      throw new ScotEID_LotValidationFault($completed_lot);
    }
  }
}
?>
<?php
class ScotEID_MergeIncompleteLotsRequest extends ScotEID_AbstractRequest
{
  public $LotDate    = null;
  public $LotNumber  = null;
  
  public function before() {
    parent::before();
    $this->LotDate = strtotime($this->LotDate);
  }
  
  public function handle() {    
    if(count($this->LotNumber) != 2) {
      throw new ScotEID_UnmergeableLotsFault("2 lots are required");
    }
    
    $lots = array();
    foreach($this->LotNumber as $lot_number) {
      $lot = ScotEID_Lot::first(array('conditions' => array(
        'lot_number' => $lot_number,
        'lot_date'   => $this->LotDate,
        'uid'        => $this->get_uid()
      )));
      if(!$lot) {
        throw new ScotEID_LotNotFoundFault();
      }
      $lots[] = $lot;
    }
    
    if($lots[0]->get_sams_movement_reference() == $lots[1]->get_sams_movement_reference()) {
      throw new ScotEID_UnmergeableLotsFault("Lots are the same");
    }
    
    if(!$lots[0]->merge($lots[1])) {
      throw new ScotEID_UnmergeableLotsFault("");
    } else {
      $response = new ScotEID_MergeIncompleteLotsResponse();
      $response->Lot = $this->prepare_lot($lots[0]);
      return $response;
    }
  }
}
?>
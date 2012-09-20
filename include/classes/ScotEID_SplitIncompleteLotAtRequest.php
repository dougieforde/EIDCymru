<?php
class ScotEID_SplitIncompleteLotAtRequest extends ScotEID_AbstractRequest
{
  public $LotNumber;
  public $LotDate;
  public $SplitAt;
  
  public function before() {
    parent::before();
    $this->LotDate = empty($this->LotDate) ? null : strtotime($this->LotDate);
    $this->SplitAt = empty($this->SplitAt) ? null : strtotime($this->SplitAt);
  }
  
  public function handle() {
    if(empty($this->LotNumber) || empty($this->LotDate)) {
      throw new ScotEID_LotNotFoundFault();
    }
    
    $lot = ScotEID_Lot::first(array('conditions' => array(
      'lot_number' => $this->LotNumber,
      'lot_date'   => $this->LotDate,
      'uid'        => $this->get_uid()
    )));
    
    if(!$lot) {
      throw new ScotEID_LotNotFoundFault();
    }

    $response = new ScotEID_SplitIncompleteLotAtResponse();

    if(!empty($this->SplitAt)) {
      $new_lot = $lot->split_at($this->SplitAt);
      $response->Lots = $this->prepare_lots(array($lot, $new_lot));
    } else {
      $response->Lots = $this->prepare_lots(array($lot));
    }
    return $response;
  }  
}
?>
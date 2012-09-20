<?php
class ScotEID_UpdateIncompleteLotRequest extends ScotEID_AbstractUpdateIncompleteLotRequest
{
  public $LotNumber;
  public $LotDate;
  public $Lot;
  
  public function before() {
    parent::before();
    $this->Lot        = $this->extract_lot($this->Lot);
    $this->LotDate    = empty($this->LotDate) ? null : strtotime($this->LotDate);
    $this->update_lot = ScotEID_Lot::first(array('conditions' => array(
      'lot_number' => $this->LotNumber,
      'lot_date'   => $this->LotDate,
      'uid'        => $this->get_uid()
    )));
    if($this->update_lot == null) {
      throw new ScotEID_LotNotFoundFault();
    }
  }

}
?>
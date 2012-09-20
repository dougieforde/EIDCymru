<?php
class ScotEID_UpdateIncompleteLotByReferenceRequest extends ScotEID_AbstractUpdateIncompleteLotRequest
{
    public $LotReference;
    public $Lot;
    
    public function before() {
        parent::before();
        $this->Lot = $this->extract_lot($this->Lot);
        $this->update_lot = ScotEID_Lot::first(array('conditions' => array('sams_movement_reference' => $this->LotReference)));
        if($this->update_lot == null) {
          throw new ScotEID_LotNotFoundFault();
        }
        if($this->update_lot->get_uid() != $this->get_uid()) {
          throw new ScotEID_SecurityFault();
        }
    }
}
?>
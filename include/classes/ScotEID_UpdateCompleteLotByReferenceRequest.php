<?php
class ScotEID_UpdateCompleteLotByReferenceRequest extends ScotEID_AbstractRequest
{
    public $LotReference;
    public $Lot;
    
    private $update_lot;
    
    public function before() {
        parent::before();
        $this->Lot = $this->extract_lot($this->Lot);
        $this->update_lot = ScotEID_CompletedLot::first(array('conditions' => array(
          'sams_movement_reference' => $this->LotReference,
          'movement_type_id.ne'     => ScotEID_MovementType::MOVEMENT_TYPE_HIDDEN,
        )));
        if($this->update_lot == null) {
          throw new ScotEID_LotNotFoundFault();
        }
        if(!($this->update_lot->is_editable_by($this->get_uid()))) {
          throw new ScotEID_SecurityFault();
        }
    }
    
    
    public function handle() {
      try {         
        //
        // Find the lot and update it with any elements supplied in this request
        $lot = $this->update_lot;
        $lot->set_editing_uid($this->get_uid());
      
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

        $existing_lot = null;
        try {
          $existing_lot = ScotEID_CompletedLot::first(array(
            'conditions' => array(
              'lot_number'    => $lot->get_lot_number(),
              'lot_date'      => $lot->get_lot_date(),
              'read_location' => $lot->get_read_location()
            )
          ));
        } catch(Exception $ex) {}

        if($existing_lot !== null && $lot->get_sams_movement_reference() != $existing_lot->get_sams_movement_reference()) {
          throw new ScotEID_LotAlreadyExistsFault();
        }

        //
        // Attempt to save the lot, throwing an already exists fault if a duplicate
        // key exception is raised by the db wrapper
        try {
          if($lot->save()) {
            $response = new ScotEID_UpdateCompleteLotResponse();
            $response->Lot = $this->prepare_lot($lot);
            return $response;
          } else {
            throw new ScotEID_LotValidationFault($lot);
          }
        } catch(DuplicateKeyException $ex) {
          throw new ScotEID_LotAlreadyExistsFault();
        }

      } catch(LotNotFoundException $ex) {
        throw new ScotEID_LotNotFoundFault();
      }
    }
}
?>
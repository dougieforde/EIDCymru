<?php
class ScotEID_AbstractUpdateIncompleteLotRequest extends ScotEID_AbstractRequest
{
    public    $Lot;
    protected $update_lot;

    public function handle() {
      try {         
        //
        // Find the lot and update it with any elements supplied in this request
        $lot = $this->update_lot;
        
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
          $existing_lot = ScotEID_Lot::first(array(
            'conditions' => array(
              'lot_number' => $lot->get_lot_number(),
              'lot_date'   => $lot->get_lot_date(),
              'uid'        => $lot->get_uid()
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
            $response = new ScotEID_UpdateIncompleteLotResponse();
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
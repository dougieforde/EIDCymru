<?php
class ScotEID_FindLotsByDestinationLocationRequest extends ScotEID_AbstractRequest
{
  public $DestinationLocation;
  public $FromDate;
  public $ToDate;
  public $Species;
  
  public function before() {
    parent::before();
    
    $this->ToDate   = strtotime($this->ToDate);
    $this->FromDate = strtotime($this->FromDate);
    
    $holding = ScotEID_Holding::try_parse($this->DestinationLocation);

    if(!$this->Species) {
      $this->Species = ScotEID_Species::SPECIES_SHEEP;
    }
    
    $samu = ScotEID_User::user_has_role($this->get_uid(), ScotEID_User::ROLE_SAMU_WS);
    
    if($samu && $this->Species != ScotEID_Species::SPECIES_PIGS) {
      // SAMU has access to all pig data, nothing else
      throw new ScotEID_SecurityFault("Permission denied");
    } else if(!$samu) {
      // everybody else has access to lots recorded on or off their registered holding
      if(!$holding->valid() || !ScotEID_User::is_registered_holding($this->get_uid(), $holding)) {
        throw new ScotEID_SecurityFault("Not a registered holding");
      }      
    }
  }
  
  public function handle() 
  {
    $response = new ScotEID_FindLotsByDestinationLocationResponse();
    $response->Lots = $this->get_lots(false);

    return $response;
  }
  
  protected function get_lots($with_tags) {
    if($this->ToDate > time() || $this->FromDate > time()) {
      $lots = array();
    } else {
      $lots = ScotEID_CompletedLot::find(array('conditions' => array(
        'destination_location' => $this->DestinationLocation,
        'lot_date.gte'         => $this->FromDate,
        'lot_date.lte'         => $this->ToDate,
        'species_id'           => $this->Species,
        'movement_type_id.ne'  => ScotEID_MovementType::MOVEMENT_TYPE_HIDDEN
      )));
    }
    
    if(ScotEID_User::user_has_role($this->get_uid(), ScotEID_User::ROLE_SAMU_WS)) {
      return $this->prepare_samu_lots($lots);
    } else {
      return $this->prepare_lots($lots, array('tag_readings' => $with_tags, 'flock_tags' => $with_tags));
    }
  }
}
?>
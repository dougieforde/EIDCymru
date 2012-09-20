<?php
class ScotEID_GetSummaryByLotRequest extends ScotEID_AbstractRequest
{
	public $LotDate;
	public $LotNumber;
	public $ReadLocation;
	
	public function before()
  {    
		parent::before();		
		if(!ScotEID_User::user_has_role($this->get_uid(), ScotEID_User::ROLE_SAMU_WS)) {
			throw new ScotEID_SecurityFault();
		}
		$this->ReadLocation = ScotEID_Holding::try_parse($this->ReadLocation);
		$this->LotDate      = strtotime($this->LotDate);
  }

	public function handle() 
	{
	  if(empty($this->LotDate) ||
	     empty($this->LotNumber) ||
	     !$this->ReadLocation->valid()) {
	       throw new ScotEID_LotNotFoundFault();
	  }
		  
		// TODO: replace this with new style finder
		// $lot = ScotEID_CompletedLot::find_by_read_location_and_lot_number_and_date(
		// 	$this->ReadLocation,
		// 	$this->LotNumber,
		// 	$this->LotDate
		//);

		$lot = ScotEID_CompletedLot::first(array('conditions' => array(
			'read_location' 		=> $this->ReadLocation,
			'lot_number'			=> $this->LotNumber,
			'lot_date'	    		=> $this->LotDate,
			'movement_type_id.ne'	=> ScotEID_MovementType::MOVEMENT_TYPE_HIDDEN
		)));
	
		if($lot == null) {
			throw new ScotEID_LotNotFoundFault();
		} else {
			$response = new ScotEID_GetSummaryByLotResponse();
			$response->set_flock_tags($lot->get_flock_tag_summary());
			return $response;	
		}
	}
}
?>
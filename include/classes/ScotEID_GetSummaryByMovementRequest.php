<?php
class ScotEID_GetSummaryByMovementRequest extends ScotEID_AbstractRequest
{
	public $DepartureLocation 		= null;
	public $DestinationLocation		= null;
	public $LotDate								= null;
	public $WithinDays						= 3;
	
	public function before()
  {    
		parent::before();		
		
		if(!ScotEID_User::user_has_role($this->get_uid(), ScotEID_User::ROLE_SAMU_WS)) {
			throw new ScotEID_SecurityFault();
		}
		
		$this->DepartureLocation    = ScotEID_Holding::try_parse($this->DepartureLocation);
		$this->DestinationLocation  = ScotEID_Holding::try_parse($this->DestinationLocation);
		$this->LotDate              = strtotime($this->LotDate);
  }

	public function handle() 
	{
    
		$response = new ScotEID_GetSummaryByMovementResponse();
		$s = ScotEID_Lot::get_movement_summary(
			$this->LotDate, $this->DepartureLocation, $this->DestinationLocation, $this->WithinDays);
		
		$dates = array();
				
		foreach($s as $date => $flock_tags) {
			$summary_date = new ScotEID_SummaryDate();
			$summary_date->date = date('Y-m-d', strtotime($date));
			$summary_date->FlockTags = $flock_tags;
			$dates[] = $summary_date;
		}
				
		$response->SummaryDates = $dates;
		return $response;	
	}
}
?>
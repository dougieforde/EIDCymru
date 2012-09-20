<?php
class ScotEID_GetSummaryByLotResponse extends ScotEID_AbstractResponse
{
	public $FlockTags = array();
	
	public function set_flock_tags($flock_tags) {
		$this->FlockTags = $flock_tags;
	}
}
?>
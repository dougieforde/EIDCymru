<?php
class ScotEID_FCI extends ScotEID_SimpleModel
{  
  protected function property_map() {
    return array(
      'ConsignmentMedicines' => 'consignment_medicines',
      'IndividualMedicines'  => 'individual_medicines',
      'WithdrawalPeriodsMet' => 'withdrawal_periods_met',
      'InjuredPigs'          => 'injured_pigs',
      'TailBitePigs'         => 'tail_bite_pigs',
      'PoorDoerPigs'         => 'poor_doer_pigs',
      'HerniaPigs'           => 'hernia_pigs',
      'ZNCPScore'            => 'zncp_score'
    );
  }
  
  // TODO: this is from ScotEID_ExtendedModel
  public function get_attribute($attribute) {
		$method = "get_$attribute";
		return $this->$method();
	}
  
  public function get_primary_key() {}
    
  private $consignment_medicines;
  private $individual_medicines;
  private $withdrawal_periods_met = false;
  private $injured_pigs;
  private $tail_bite_pigs;
  private $poor_doer_pigs;
  private $hernia_pigs;
  private $zncp_score;
  
  public function get_consignment_medicines()   { return $this->consignment_medicines; }
  public function get_individual_medicines()    { return $this->individual_medicines; }
  public function get_withdrawal_periods_met()   { return $this->withdrawal_periods_met; }
  public function get_injured_pigs()            { return $this->injured_pigs; }
  public function get_tail_bite_pigs()          { return $this->tail_bite_pigs; }
  public function get_poor_doer_pigs()          { return $this->poor_doer_pigs; }
  public function get_hernia_pigs()             { return $this->hernia_pigs; }
  public function get_zncp_score()              { return $this->zncp_score; }
  
  public function set_consignment_medicines($v) { $this->consignment_medicines = $v; }
  public function set_individual_medicines($v)  { $this->individual_medicines  = $v; }
  public function set_withdrawal_periods_met($v) { 
    $this->withdrawal_periods_met = $v === null ? null : (int) $v;
  }
  public function set_injured_pigs($v)          { $this->injured_pigs = $v; }
  public function set_tail_bite_pigs($v)        { $this->tail_bite_pigs = $v; }
  public function set_poor_doer_pigs($v)        { $this->poor_doer_pigs = $v; }
  public function set_hernia_pigs($v)           { $this->hernia_pigs = $v; }
  public function set_zncp_score($v)            { $this->zncp_score = $v; }
}
?>
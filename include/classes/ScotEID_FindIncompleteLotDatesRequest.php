<?php
class ScotEID_FindIncompleteLotDatesRequest extends ScotEID_AbstractRequest
{
  public function handle() {
    $response = new ScotEID_FindIncompleteLotDatesResponse();
    $response->LotDate = ScotEID_Lot::find_dates($this->get_uid());
    return $response;
  }
}
?>
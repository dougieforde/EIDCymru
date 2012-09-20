<?php
class ScotEID_GetLotsByDepartureLocationRequest extends ScotEID_FindLotsByDepartureLocationRequest
{
  public function handle() {
    $response = new ScotEID_GetLotsByDepartureLocationResponse();
    $response->Lots = $this->get_lots(true);
    return $response;
  }
}
?>
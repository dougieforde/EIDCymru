<?php
class ScotEID_GetLotsByDestinationLocationRequest extends ScotEID_FindLotsByDestinationLocationRequest
{
  public function handle() {
    $response = new ScotEID_GetLotsByDestinationLocationResponse();
    $response->Lots = $this->get_lots(true);
    return $response;
  }
}
?>
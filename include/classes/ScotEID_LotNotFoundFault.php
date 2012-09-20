<?php
class ScotEID_LotNotFoundFault extends SoapFault
{
  public $ErrorDescription = "The specified lot could not be found";

  public function __construct() {
    parent::__construct("Server", "The lot could not be found", null, $this, "LotNotFoundFault");
  }
}
?>
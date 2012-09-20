<?php
class ScotEID_LotAlreadyExistsFault extends SoapFault
{
  public $ErrorDescription = "The specified lot already exists";
  
  public function __construct() {
    parent::__construct("Server", "", null, $this, "LotAlreadyExistsFault");
  }
}
?>
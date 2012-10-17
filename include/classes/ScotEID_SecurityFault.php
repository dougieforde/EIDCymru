<?php
class ScotEID_SecurityFault extends SoapFault
{
  public $ErrorDescription = "Security fault";
  
  public function __construct($message) {
    parent::__construct("Client", $message, null, $this, "SecurityFault");
  }
}
?>
<?php
class ScotEID_UnmergeableLotsFault extends SoapFault
{
  public $ErrorDescription = "The specified lot cannot be merged";
  
  public function __construct($message) {
    parent::__construct("Client", $message, null, $this, "UnmergeableLotsFault");
  }
}
?>
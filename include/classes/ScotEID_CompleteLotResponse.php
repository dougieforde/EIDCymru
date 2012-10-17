<?php
class ScotEID_CompleteLotResponse extends ScotEID_AbstractResponse
{
  public $Lot;
  
  public function __construct($Lot) {
    $this->Lot = $Lot;
  }
}
?>
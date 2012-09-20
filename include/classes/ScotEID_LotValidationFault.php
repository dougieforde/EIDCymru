<?php
class ScotEID_LotValidationFault extends SoapFault
{
  public $ErrorDescription = "The lot contains errors";
  
  public $Errors;

  public function __construct($lot) {
    $errors       = $lot->get_errors();
    $this->Errors = array();
    
    foreach($errors as $field => $messages) {
      foreach($messages as $message) {
        $this->Errors[] = array(
          'Property' => $field,
          'Message'  => $message
        );
      }
    }

    parent::__construct("Client", "The lot could not be validated", null, $this, "LotValidationFault");
  }
  
  /*
  public static function with_errors($errors) {
    $r = parent::__construct("Client", "The lot could not be validated", null, null, "LotValidationFault");
    $r->Errors = $errors;
    return $r;
  }*/
}
?>
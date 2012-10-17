<?php
class ScotEID_ISO24631TagID extends ScotEID_TagID
{
  public function __construct($iso24631) {
    $iso24631 = str_replace(" ", "", $iso24631);
    
    if(strlen($iso24631) != 22) {
      // TODO: maybe we should throw an exception here, but for now just store
      // something that won't be valid resulting in the tag reading getting
      // rejected
      parent::__construct("0");
    } else {
      
      $animalFlag   = substr($iso24631, 0, 1);
      $retagCounter = (int)substr($iso24631, 1, 1);
      $speciesCode  = (int)substr($iso24631, 2, 2);
      $reservedData = (int)substr($iso24631, 4, 2);
      $dataBlock    = (int)substr($iso24631, 6, 1);
      $countryCode  = (int)substr($iso24631, 7, 3);
      $animalId     = substr($iso24631, 10, 12);
      
      $binary = "";
      
      if($animalFlag == "1" || $animalFlag == "A") {
        $binary .= "1";
      } else {
        $binary .= "0";
      }
 
      $binary .= str_pad(decbin($retagCounter), 3, "0", STR_PAD_LEFT);
      $binary .= str_pad(decbin($speciesCode), 5, "0", STR_PAD_LEFT);
      $binary .= str_pad(decbin($reservedData), 6, "0", STR_PAD_LEFT);
      $binary .= $dataBlock == 1 ? "1" : "0";
      $binary .= str_pad(decbin($countryCode), 10, "0", STR_PAD_LEFT);
      $binary .= str_pad(gmp_strval(gmp_init($animalId,10),2), 38, "0", STR_PAD_LEFT);
           
      $gmp = gmp_init($binary, 2);
      
      parent::__construct(gmp_strval($gmp,16)); 
    }
  }
  
  public static function from_xml($xml) {
   return new ScotEID_ISO24631TagID(strip_tags($xml));
  }
}
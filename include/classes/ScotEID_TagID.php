<?php
class ScotEID_TagID
{
  public $Hex;
  
  const RETAG_MASK        = "7000000000000000";
  const SPECIES_MASK      = "0F80000000000000";
  const RESERVED_MASK     = "007E000000000000";
  const COUNTRY_CODE_MASK = "0000FFC000000000";
  const ANIMAL_ID_MASK    = "0000003FFFFFFFFF";
  
  public function __construct($hex) {
    $this->Hex = str_replace(" ", "", strtoupper($hex));
  }
  
  public function __toString() {
    return $this->Hex;
  }
  
  public function get_animal_id() {
    $str_hex = substr($this->Hex,-10);
    $str_bin = "";
    for ($i=0; $i < strlen($str_hex); $i++) {
      $str_bin .= sprintf("%04s", decbin(hexdec($str_hex[$i])));
    }    
    return bindec(substr($str_bin,-38));
  }
  
  public function get_country_code() {
    $gmp  = gmp_init($this->Hex, 16);
    $mask = gmp_init(ScotEID_TagID::COUNTRY_CODE_MASK, 16);
    
    return gmp_intval(gmp_div(gmp_and($gmp, $mask), gmp_pow(2, 38)));
  }
  
  public function get_flock_number() {
		if($this->get_country_code() == 826) {
			// FIXME - lookup country codes
			//return ($this->get_country_code() == 826 ? "UK" : "??") . substr($this->get_animal_id(), 0, 6);
			return substr($this->get_animal_id(), 0, 6);
		} else {
			return "UNKNOWN";
		}
  }

  public function to_hex_string() {
    return $this->Hex;
  }
  
  public function equals($tag_id) {
    return $this->to_hex_string() == $tag_id->to_hex_string();
  }
  
  public function is_valid() {
    return !! preg_match('/^[0-9A-F]{16}$/', $this->Hex);
  }
  
  public static function instance_to_xml($tag_id) {
   return $tag_id->to_xml();
  }
  
  public static function from_xml($xml) {
   return new ScotEID_TagID(strip_tags($xml));
  }
  
  public function to_xml() {
   return '<TagID>' . $this->Hex . '</TagID>';
  }
}
?>
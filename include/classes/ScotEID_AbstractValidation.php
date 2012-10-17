<?php
class ScotEID_AbstractValidation
{
  protected $attribute;
  protected $error;
  protected $if;
  
  public function __construct($attribute, $options = array()) 
  {
    $this->attribute = $attribute;
    if(isset($options['error'])) {
      $this->error = $options['error'];
    }
    if(isset($options['if'])) {
      $this->if = $options['if'];
    }  
  }
  
  public function validate($model)
  {
    if($this->if) {
      $f = $this->if;
      return $f($model);
    }
    return true;
  }
  
  protected function get_error($default) {
    if($this->error) {
      return $this->error;
    } else {
      return $default;
    }
  }
}
?>
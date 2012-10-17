<?php
class ScotEID_PresenceValidation extends ScotEID_AbstractValidation
{
  public function __construct($attribute, $options = array()) {
    parent::__construct($attribute, $options);
  }
  
  public function validate($model)
  {
    $r = parent::validate($model);    
    if(!$r) return;
    
    $v = $model->get_attribute($this->attribute);
    if($this->is_blank($v)) {
      $model->add_error($this->attribute, $this->get_error("cannot be blank"));
    }
  }
  
  private function is_blank($value) {
    return empty($value) && !is_numeric($value);
  }
}
?>
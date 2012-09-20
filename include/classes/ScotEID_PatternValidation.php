<?php
class ScotEID_PatternValidation extends ScotEID_AbstractValidation
{
  private $pattern;
  
  public function __construct($attribute, $pattern, $options = array()) {
    parent::__construct($attribute, $options);
    $this->pattern = $pattern;
  }
  
  public function validate($model)
  {
    $r = parent::validate($model);    
    if(!$r) return;
        
    $v = $model->get_attribute($this->attribute);
    
    if(!preg_match($this->pattern, $v)) {
      $model->add_error($this->attribute, $this->get_error("doesn't match the pattern"));
    }
  }
}
?>
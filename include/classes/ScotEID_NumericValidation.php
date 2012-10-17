<?php
class ScotEID_NumericValidation extends ScotEID_AbstractValidation
{
  private $min;
  private $max;
  
  public function __construct($attribute, $options = array()) {
    parent::__construct($attribute, $options);
    if(isset($options['min']))
      $this->min = $options['min'];
    if(isset($options['max']))
      $this->max = $options['max'];
  }
  
  public function validate($model) {
    $r = parent::validate($model);
    
    if(!$r) return;
    
    $v = $model->get_attribute($this->attribute);
    
    $errors = false;
    
    if(!is_numeric($v)) {
      $errors = true;
    } else if(is_numeric($this->min) && $v < $this->min) {
      $errors = true;
    } else if(is_numeric($this->max) && $v > $this->max) {
      $errors = true;
    }

    if($errors) {
      if(is_numeric($this->min) && is_numeric($this->max)) {
        $model->add_error($this->attribute, $this->get_error("must be between {$this->min} and {$this->max}"));
      } else if(is_numeric($this->min)) {
        $model->add_error($this->attribute, $this->get_error("must be greater than or equal to {$this->min}"));
      } else if(is_numeric($this->max)) {
        $model->add_error($this->attribute, $this->get_error("must be less than or equal to {$this->max}"));
      } else {
        $model->add_error($this->attribute, $this->get_error("must be a number"));
      }
    }
  }
}
?>
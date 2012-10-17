<?php 
class ScotEID_LengthOfValidation extends ScotEID_AbstractValidation
{
  private $min;
  private $max;
  
  public function __construct($attribute, $options = array()) {
    parent::__construct($attribute, $options);
    if(isset($options['min'])) {
      $this->min = $options['min'];
    }
    if(isset($options['max'])) {
      $this->max = $options['max'];
    }
  }
  
  public function validate($model)
  {
    $r = parent::validate($model);    
    if(!$r) return;    

    $v   = $model->get_attribute($this->attribute);
    $len = empty($v) ? 0 : strlen($v); 
       
    $error = false;
    
    if($this->min && $len < $this->min) {
      $error = true;
    }
  
    if($this->max && $len > $this->max) {
      $error = true;
    }
    
    if($error) {
      $message = null;
      if($this->min && $this->max) {
        $message = "must be between {$this->min} and {$this->max} characters long";
      } else if($this->min) {
        $message = "must be at least {$this->min} characters long";
      } else if($this->max) {
        $message = "must be at most {$this->max} characters long";
      }
            
      $model->add_error($this->attribute, $this->get_error($message));
    }
  }
}
?>
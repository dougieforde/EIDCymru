<?php
class ScotEID_CustomValidation extends ScotEID_AbstractValidation
{
  private $callback;
  
  public function __construct($callback, $options = array()) {
    parent::__construct(null, $options);
    $this->callback = $callback;
  }
  
  public function validate($model) {
    $r = parent::validate($model);    
    if(!$r) return;
        
    $m = $this->callback;
    $model->$m();
  }
}
?>
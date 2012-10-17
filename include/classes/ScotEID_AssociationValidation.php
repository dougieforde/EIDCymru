<?php
class ScotEID_AssociationValidation extends ScotEID_AbstractValidation
{
  public function __construct($attribute, $options = array()) {
    parent::__construct($attribute, $options);
  }
  
  public function validate($model)
  {
    $r = parent::validate($model);    
    if(!$r) return;    
   
    $errors = false;
    
    $v = $model->get_attribute($this->attribute);
    
    if($v && is_array($v)) {
      foreach($v as $a) {
        if(!$a->validate()) {
          $errors = true;
        }
      }
    }
    
    if($errors) {
      $model->add_error($this->attribute, "are not valid");
    }
  }
}
?>
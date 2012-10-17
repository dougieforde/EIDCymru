<?php
require 'include/prepend.php';

$items = ScotEID_AuditItem::all();

foreach($items as $item) {
  if(is_array($item->get_object())) {
    $data = $item->get_object();
    if(isset($data['sams_movement_reference'])) {
      $item->set_sams_movement_reference($data['sams_movement_reference']);
      $item->save();
    }
  }
}

?>
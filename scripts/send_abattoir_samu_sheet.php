<?php

require 'include/prepend.php';

$lot_date = $argv[1];
$uid      = $argv[2];

if(empty($uid) || empty($lot_date)) {
  echo "Usage: send_abattoir_samu_sheet <lot_date> <uid>\n";
} else {
  
  $lot_date = strtotime($lot_date);

  if(ScotEID_Mailer::deliver_samu_spreadsheet($lot_date, $uid)) {
    echo "Sent successfully.\n";
  } else {
    echo "Send failed.\n";
  }
}

?>
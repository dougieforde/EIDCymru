<?php
require_once './include/prepend.php';

$filename = @$argv[1];
$uid      = @$argv[2];

if(empty($filename) || empty($uid)) {
    print "Usage: import_vion <filename> <uid>\n";
} else {
  $processor = new ScotEID_VionXMLProcessor($filename, (int) $uid);
  $processor->process();
}

?>